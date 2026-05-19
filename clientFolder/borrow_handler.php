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
        // 1. Fetch Book and User Details
        $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ? AND is_deleted = 0");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT credit_score FROM users WHERE id = ?");
        $stmt->execute([$user_db_id]);
        $user = $stmt->fetch();

        if (!$book) {
            echo json_encode(['status' => 'error', 'message' => 'Book not found.']);
            exit();
        }

        // 2. Check Credit Score for Exclusive Books
        if ($book['is_exclusive'] && $user['credit_score'] <= 5) {
            echo json_encode(['status' => 'error', 'message' => 'Exclusive books require a Credit Score higher than 5.']);
            exit();
        }

        // 3. Check current availability
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status = 'borrowed'");
        $stmt->execute([$book_id]);
        $isCurrentlyBorrowed = $stmt->fetchColumn() > 0;

        if ($action === 'borrow' && $isCurrentlyBorrowed) {
            echo json_encode(['status' => 'error', 'message' => 'This book is currently out on loan.']);
            exit();
        }

        if ($action === 'borrow') {
            // 4. Process Borrowing
            $borrow_date = date('Y-m-d H:i:s');
            $due_date = date('Y-m-d H:i:s', strtotime('+7 days')); // Default 7 days

            $insert = $pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $insert->execute([$book_id, $user_db_id, $borrow_date, $due_date]);

            echo json_encode(['status' => 'success', 'message' => 'Book borrowed successfully! Due date: ' . date('M d, Y', strtotime($due_date))]);
        }

        if ($action === 'reserve') {
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

            foreach ($_SESSION['borrow_cart'] as $id) {
                // Re-verify availability per book
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status = 'borrowed'");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() == 0) {
                    $insert = $pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
                    $insert->execute([$id, $user_db_id, $borrow_date, $due_date]);
                    $success_count++;
                }
            }

            $_SESSION['borrow_cart'] = []; // Clear cart
            echo json_encode(['status' => 'success', 'message' => "Successfully rented $success_count books!"]);
        }

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>