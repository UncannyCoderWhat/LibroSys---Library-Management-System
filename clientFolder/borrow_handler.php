<?php
session_start();
require_once '../dbForLogin/db.php';

if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_db_id = $_SESSION['user_id'];
    $book_id = $_POST['book_id'] ?? null;
    $action = $_POST['action'];

    try {
        // Fetch User Details (Required for most actions to check credit score)
        $stmt = $pdo->prepare("SELECT credit_score FROM users WHERE id = ?");
        $stmt->execute([$user_db_id]);
        $user = $stmt->fetch();

        // Single-book actions validation
        if (in_array($action, ['borrow', 'reserve', 'add_to_cart'])) {
            $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_deleted = 0");
            $stmt->execute([$book_id]);
            $book = $stmt->fetch();

            if (!$book) {
                echo json_encode(['status' => 'error', 'message' => 'Book not found.']);
                exit();
            }

            if ($book['is_exclusive'] && $user['credit_score'] <= 5) {
                echo json_encode(['status' => 'error', 'message' => 'Exclusive books require a Credit Score higher than 5.']);
                exit();
            }
        }

        if ($action === 'borrow') {
            // Check current availability for single borrow
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status IN ('borrowed', 'reserved') AND user_id != ?");
            $stmt->execute([$book_id, $user_db_id]);
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['status' => 'error', 'message' => 'This book is currently out on loan or held for another user.']);
                exit();
            }

            // 4. Process Borrowing
            $borrow_date = date('Y-m-d H:i:s');
            $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Default 7 days

            $insert = $pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $insert->execute([$book_id, $user_db_id, $borrow_date, $due_date]);

            // NEW: Fulfill any active reservation for this book by this user
            $fulfillRes = $pdo->prepare("DELETE FROM borrows WHERE book_id = ? AND user_id = ? AND status = 'reserved'");
            $fulfillRes->execute([$book_id, $user_db_id]);

            echo json_encode(['status' => 'success', 'message' => 'Book borrowed successfully! Due date: ' . date('M d, Y', strtotime($due_date))]);
        }

        if ($action === 'reserve') {
            // Validation: Book must be currently borrowed AND not already reserved
            $stmt = $pdo->prepare("SELECT status FROM borrows WHERE book_id = ? AND status IN ('borrowed', 'reserved')");
            $stmt->execute([$book_id]);
            $activeStatuses = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!in_array('borrowed', $activeStatuses)) {
                echo json_encode(['status' => 'error', 'message' => 'This book is currently available for rent.']);
                exit();
            }

            if (in_array('reserved', $activeStatuses)) {
                echo json_encode(['status' => 'error', 'message' => 'This book is already on hold for another user.']);
                exit();
            }

            // 5. Process Reservation
            $insert = $pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, status) VALUES (?, ?, ?, 'reserved')");
            $insert->execute([$book_id, $user_db_id, date('Y-m-d H:i:s')]);

            echo json_encode(['status' => 'success', 'message' => 'Book reserved successfully! You will be notified when it is returned.']);
        }
        
        if ($action === 'add_to_cart') {
            if (!isset($_SESSION['borrow_cart'])) {
                $_SESSION['borrow_cart'] = [];
            }
            
            if (in_array($book_id, $_SESSION['borrow_cart'])) {
                echo json_encode(['status' => 'info', 'message' => 'Book is already in your queue.']);
            } else {
                $_SESSION['borrow_cart'][] = $book_id;
                echo json_encode(['status' => 'success', 'message' => 'Added to your borrow queue!', 'cart_count' => count($_SESSION['borrow_cart'])]);
            }
        }

        if ($action === 'remove_from_cart') {
            if (($key = array_search($book_id, $_SESSION['borrow_cart'])) !== false) {
                unset($_SESSION['borrow_cart'][$key]);
                echo json_encode(['status' => 'success', 'message' => 'Removed from cart']);
            }
        }

        if ($action === 'checkout') {
            if (empty($_SESSION['borrow_cart'])) {
                echo json_encode(['status' => 'error', 'message' => 'Cart is empty.']);
                exit();
            }

            $borrow_date = date('Y-m-d H:i:s');
            $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));
            $success_count = 0;

            foreach ($_SESSION['borrow_cart'] as $key => $id) {
                // Re-verify availability and credit score eligibility per book in cart
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status IN ('borrowed', 'reserved') AND user_id != ?");
                $stmt->execute([$id, $user_db_id]);
                $isBorrowed = $stmt->fetchColumn() > 0;

                $bookStmt = $pdo->prepare("SELECT is_exclusive FROM books WHERE id = ?");
                $bookStmt->execute([$id]);
                $b = $bookStmt->fetch();

                if (!$isBorrowed) {
                    // Skip if user no longer has enough credit score for an exclusive book
                    if ($b['is_exclusive'] && $user['credit_score'] <= 5) continue;

                    $insert = $pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
                    $insert->execute([$id, $user_db_id, $borrow_date, $due_date]);
                    $success_count++;
                    unset($_SESSION['borrow_cart'][$key]);
                }
            }

            $_SESSION['borrow_cart'] = array_values($_SESSION['borrow_cart']); // Re-index remaining items
            echo json_encode(['status' => 'success', 'message' => "Successfully rented $success_count books!"]);
        }

        if ($action === 'pay_fines') {
            // 1. Fetch all currently borrowed books to process their automatic return
            $stmt = $pdo->prepare("SELECT br.*, b.title FROM borrows br JOIN books b ON br.book_id = b.id WHERE br.user_id = ? AND br.status = 'borrowed'");
            $stmt->execute([$user_db_id]);
            $activeBorrows = $stmt->fetchAll();

            $totalScoreChange = 0;
            $now = date('Y-m-d H:i:s');

            foreach ($activeBorrows as $borrow) {
                // Determine credit score impact (Matching return_handler.php logic)
                $isLate = strtotime($now) > strtotime($borrow['due_date']);
                $totalScoreChange += ($isLate ? -2 : 1);

                // Notify users who reserved these specific books
                $resStmt = $pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
                $resStmt->execute([$borrow['book_id']]);
                $reservation = $resStmt->fetch();

                if ($reservation) {
                    $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $msg = "The book '" . $borrow['title'] . "' you reserved is now available!";
                    $notifStmt->execute([$reservation['user_id'], $msg]);
                }

                // Mark as returned and clear fine immediately
                $upd = $pdo->prepare("UPDATE borrows SET status = 'returned', return_date = ?, fine_amount = 0 WHERE id = ?");
                $upd->execute([$now, $borrow['id']]);
            }

            // 2. Clear fines for any books already returned that still have a balance
            $clearStmt = $pdo->prepare("UPDATE borrows SET fine_amount = 0 WHERE user_id = ? AND status = 'returned'");
            $clearStmt->execute([$user_db_id]);

            // 3. Apply the aggregated Credit Score changes
            if ($totalScoreChange !== 0) {
                $updateUser = $pdo->prepare("UPDATE users SET credit_score = GREATEST(0, LEAST(10, credit_score + ?)) WHERE id = ?");
                $updateUser->execute([$totalScoreChange, $user_db_id]);
            }

            echo json_encode(['status' => 'success', 'message' => 'Payment successful! All books have been returned and fines are cleared.']);
        }

        if ($action === 'cancel_reservation') {
            $res_id = $_POST['borrow_id'] ?? null;
            // Verify ownership and status before deleting
            $stmt = $pdo->prepare("DELETE FROM borrows WHERE id = ? AND user_id = ? AND status = 'reserved'");
            $stmt->execute([$res_id, $user_db_id]);
            echo json_encode(['status' => 'success', 'message' => 'Reservation cancelled successfully!']);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>