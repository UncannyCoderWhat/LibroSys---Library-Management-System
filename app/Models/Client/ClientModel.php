<?php
// app/Models/Client/ClientModel.php
class ClientModel
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getUserById(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function getBrowsePageData(int $userId): array
    {
        $userStmt = $this->pdo->prepare("SELECT credit_score FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $userData = $userStmt->fetch(PDO::FETCH_ASSOC);

        $totalFines = 0;
        $stmtFines = $this->pdo->prepare("SELECT fine_amount, due_date, status FROM borrows WHERE user_id = ? AND is_fine_paid = FALSE");
        $stmtFines->execute([$userId]);
        $borrowsForFines = $stmtFines->fetchAll(PDO::FETCH_ASSOC);

        foreach ($borrowsForFines as $b) {
            $f = $b['fine_amount'] ?? 0;

            if (($b['status'] ?? null) === 'borrowed' && !empty($b['due_date'])) {
                $now = time();
                $dueDate = strtotime($b['due_date']);
                if ($now > $dueDate) {
                    $daysLate = (int)ceil(($now - $dueDate) / (60 * 60 * 24));
                    if ($daysLate <= 3) $f = $daysLate * 50;
                    elseif ($daysLate <= 10) $f = $daysLate * 100;
                    else $f = $daysLate * 150;
                } else {
                    $f = 0;
                }
            }

            $totalFines += $f;
        }

        $exclusive_books = $this->pdo->query("
            SELECT b.*, 0 as is_borrowed
            FROM books b
            WHERE is_exclusive = 1 AND is_deleted = 0
            AND b.id NOT IN (SELECT book_id FROM borrows WHERE status IN ('borrowed', 'reserved'))
        ")->fetchAll(PDO::FETCH_ASSOC);

        $regular_books = $this->pdo->query("
            SELECT b.*, 0 as is_borrowed
            FROM books b
            WHERE is_exclusive = 0 AND is_deleted = 0
            AND b.id NOT IN (SELECT book_id FROM borrows WHERE status IN ('borrowed', 'reserved'))
        ")->fetchAll(PDO::FETCH_ASSOC);

        $borrowed_others_stmt = $this->pdo->prepare("
            SELECT b.*, 1 as is_borrowed
            FROM books b
            JOIN borrows br ON b.id = br.book_id
            WHERE br.status IN ('borrowed', 'reserved')
            AND b.is_deleted = 0
            AND b.id NOT IN (
                SELECT book_id
                FROM borrows
                WHERE user_id = ? AND status IN ('borrowed', 'reserved')
            )
            GROUP BY b.id
        ");
        $borrowed_others_stmt->execute([$userId]);
        $borrowed_books = $borrowed_others_stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'current_score' => $userData['credit_score'] ?? 0,
            'totalFines' => $totalFines,
            'exclusive_books' => $exclusive_books,
            'regular_books' => $regular_books,
            'borrowed_books' => $borrowed_books,
        ];
    }

    public function handleLogin(string $loginInput, string $password): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ? OR email = ?");
        $stmt->execute([$loginInput, $loginInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return [
                'success' => true,
                'user' => $user,
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid ID/Email or Password.',
        ];
    }

    public function handleSignup(string $name, string $email, string $password, string $confirmPassword): array
    {
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match!'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long!'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $userId = 'USR-' . uniqid();

        $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE user_id = ?");
        $checkStmt->execute([$userId]);

        if ($checkStmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Failed to generate a unique User ID. Please try again.'];
        }

        $stmt = $this->pdo->prepare("INSERT INTO users (user_id, name, email, password, credit_score) VALUES (?, ?, ?, ?, 10)");
        if ($stmt->execute([$userId, $name, $email, $hashedPassword])) {
            return [
                'success' => true,
                'user_id' => $userId,
                'insert_id' => (int)$this->pdo->lastInsertId(),
                'name' => $name,
            ];
        }

        return ['success' => false, 'message' => 'Unable to create the account. Please try again.'];
    }

    public function handleBorrowAction(int $userId, ?int $bookId, string $action, array &$session): array
    {
        if ($action === 'add_to_cart') {
            if (!isset($session['borrow_cart'])) {
                $session['borrow_cart'] = [];
            }

            if (in_array($bookId, $session['borrow_cart'], true)) {
                return ['status' => 'info', 'message' => 'Book is already in your queue.'];
            }

            $session['borrow_cart'][] = $bookId;
            return [
                'status' => 'success',
                'message' => 'Added to your borrow queue!',
                'cart_count' => count($session['borrow_cart']),
            ];
        }

        if ($action === 'remove_from_cart') {
            if (!isset($session['borrow_cart']) || !is_array($session['borrow_cart'])) {
                return ['status' => 'success', 'message' => 'Cart already empty.'];
            }

            $key = array_search($bookId, $session['borrow_cart'], true);
            if ($key !== false) {
                unset($session['borrow_cart'][$key]);
                $session['borrow_cart'] = array_values($session['borrow_cart']);
                return ['status' => 'success', 'message' => 'Removed from cart'];
            }

            return ['status' => 'info', 'message' => 'Book was not in your cart.'];
        }

        $stmt = $this->pdo->prepare("SELECT credit_score FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (in_array($action, ['borrow', 'reserve', 'add_to_cart'], true)) {
            $stmt = $this->pdo->prepare("SELECT * FROM books WHERE id = ? AND is_deleted = 0");
            $stmt->execute([$bookId]);
            $book = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$book) {
                return ['status' => 'error', 'message' => 'Book not found.'];
            }

            if (!empty($book['is_exclusive']) && (int)($user['credit_score'] ?? 0) <= 5) {
                return ['status' => 'error', 'message' => 'Exclusive books require a Credit Score higher than 5.'];
            }
        }

        if ($action === 'borrow') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status = 'borrowed'");
            $stmt->execute([$bookId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return ['status' => 'error', 'message' => 'This book is currently out on loan.'];
            }

            $stmt = $this->pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
            $stmt->execute([$bookId]);
            $firstInLine = $stmt->fetchColumn();

            if ($firstInLine && (int)$firstInLine !== $userId) {
                return ['status' => 'error', 'message' => 'This book is currently held for another user who reserved it first.'];
            }

            $borrowDate = date('Y-m-d H:i:s');
            $dueDate = date('Y-m-d H:i:s', strtotime('+7 days'));

            $insert = $this->pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $insert->execute([$bookId, $userId, $borrowDate, $dueDate]);

            $fulfillRes = $this->pdo->prepare("DELETE FROM borrows WHERE book_id = ? AND user_id = ? AND status = 'reserved'");
            $fulfillRes->execute([$bookId, $userId]);

            return ['status' => 'success', 'message' => 'Book borrowed successfully! Due date: ' . date('M d, Y', strtotime($dueDate))];
        }

        if ($action === 'reserve') {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND user_id = ? AND status IN ('borrowed', 'reserved')");
            $stmt->execute([$bookId, $userId]);
            if ((int)$stmt->fetchColumn() > 0) {
                return ['status' => 'error', 'message' => 'You already have an active request for this book.'];
            }

            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status IN ('borrowed', 'reserved')");
            $stmt->execute([$bookId]);
            if ((int)$stmt->fetchColumn() === 0) {
                return ['status' => 'error', 'message' => 'This book is available on the shelves. You should rent it instead!'];
            }

            $insert = $this->pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, status) VALUES (?, ?, ?, 'reserved')");
            $insert->execute([$bookId, $userId, date('Y-m-d H:i:s')]);

            return ['status' => 'success', 'message' => 'Book reserved successfully! You will be notified when it is returned.'];
        }

        if ($action === 'checkout') {
            if (empty($session['borrow_cart'])) {
                return ['status' => 'error', 'message' => 'Cart is empty.'];
            }

            $borrowDate = date('Y-m-d H:i:s');
            $dueDate = date('Y-m-d H:i:s', strtotime('+7 days'));
            $successCount = 0;

            foreach ($session['borrow_cart'] as $key => $id) {
                $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE book_id = ? AND status = 'borrowed'");
                $stmt->execute([$id]);
                $isBorrowed = (int)$stmt->fetchColumn() > 0;

                $qStmt = $this->pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
                $qStmt->execute([$id]);
                $firstInLine = $qStmt->fetchColumn();
                $isHeldForOthers = ($firstInLine && (int)$firstInLine !== $userId);

                $bookStmt = $this->pdo->prepare("SELECT is_exclusive FROM books WHERE id = ?");
                $bookStmt->execute([$id]);
                $b = $bookStmt->fetch(PDO::FETCH_ASSOC);

                if (!$isBorrowed && !$isHeldForOthers) {
                    if (!empty($b['is_exclusive']) && (int)($user['credit_score'] ?? 0) <= 5) {
                        continue;
                    }

                    $insert = $this->pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
                    $insert->execute([$id, $userId, $borrowDate, $dueDate]);
                    $successCount++;
                    unset($session['borrow_cart'][$key]);
                }
            }

            $session['borrow_cart'] = array_values($session['borrow_cart']);
            return ['status' => 'success', 'message' => "Successfully rented $successCount books!"];
        }

        if ($action === 'pay_fines') {
            $now = date('Y-m-d H:i:s');
            $stmt = $this->pdo->prepare("SELECT br.*, b.title FROM borrows br JOIN books b ON br.book_id = b.id WHERE br.user_id = ? AND br.status = 'borrowed' AND br.due_date < ?");
            $stmt->execute([$userId, $now]);
            $activeBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalScoreChange = 0;

            foreach ($activeBorrows as $borrow) {
                $isLate = strtotime($now) > strtotime($borrow['due_date']);
                $fine = 0;
                if ($isLate) {
                    $diff = strtotime($now) - strtotime($borrow['due_date']);
                    $daysLate = (int)ceil($diff / (60 * 60 * 24));
                    if ($daysLate <= 3) $fine = $daysLate * 50;
                    elseif ($daysLate <= 10) $fine = $daysLate * 100;
                    else $fine = $daysLate * 150;
                }

                $totalScoreChange += ($isLate ? -2 : 1);

                $resStmt = $this->pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
                $resStmt->execute([$borrow['book_id']]);
                $reservation = $resStmt->fetch(PDO::FETCH_ASSOC);

                if ($reservation) {
                    $notifStmt = $this->pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                    $msg = "The book '" . $borrow['title'] . "' you reserved is now available!";
                    $notifStmt->execute([$reservation['user_id'], $msg]);
                }

                $upd = $this->pdo->prepare("UPDATE borrows SET status = 'returned', return_date = ?, fine_amount = ?, is_fine_paid = TRUE WHERE id = ?");
                $upd->execute([$now, $fine, $borrow['id']]);
            }

            $clearStmt = $this->pdo->prepare("UPDATE borrows SET is_fine_paid = TRUE WHERE user_id = ? AND status = 'returned' AND fine_amount > 0");
            $clearStmt->execute([$userId]);

            if ($totalScoreChange !== 0) {
                $updateUser = $this->pdo->prepare("UPDATE users SET credit_score = GREATEST(0, LEAST(10, credit_score + ?)) WHERE id = ?");
                $updateUser->execute([$totalScoreChange, $userId]);
            }

            return ['status' => 'success', 'message' => 'Payment successful! All books have been returned and fines are cleared.'];
        }

        if ($action === 'cancel_reservation') {
            $resId = $bookId;
            $stmt = $this->pdo->prepare("DELETE FROM borrows WHERE id = ? AND user_id = ? AND status = 'reserved'");
            $stmt->execute([$resId, $userId]);
            return ['status' => 'success', 'message' => 'Reservation cancelled successfully!'];
        }

        return ['status' => 'error', 'message' => 'Unsupported action.'];
    }

    public function handleReturnAction(int $userId, int $borrowId): array
    {
        $stmt = $this->pdo->prepare("SELECT br.*, b.title FROM borrows br JOIN books b ON br.book_id = b.id WHERE br.id = ? AND br.user_id = ? AND br.status = 'borrowed'");
        $stmt->execute([$borrowId, $userId]);
        $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$borrow) {
            return ['status' => 'error', 'message' => 'Active borrow record not found.'];
        }

        $now = date('Y-m-d H:i:s');
        $dueDate = $borrow['due_date'];
        $isLate = strtotime($now) > strtotime($dueDate);
        $fine = 0;

        if ($isLate) {
            $diff = strtotime($now) - strtotime($dueDate);
            $daysLate = (int)ceil($diff / (60 * 60 * 24));
            if ($daysLate <= 3) $fine = $daysLate * 50;
            elseif ($daysLate <= 10) $fine = $daysLate * 100;
            else $fine = $daysLate * 150;
        }

        $update = $this->pdo->prepare("UPDATE borrows SET status = 'returned', return_date = ?, fine_amount = ?, is_fine_paid = FALSE WHERE id = ?");
        $update->execute([$now, $fine, $borrowId]);

        $resStmt = $this->pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
        $resStmt->execute([$borrow['book_id']]);
        $reservation = $resStmt->fetch(PDO::FETCH_ASSOC);

        if ($reservation) {
            $notifStmt = $this->pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, ?)");
            $msg = "The book '" . $borrow['title'] . "' you reserved is now available!";
            $notifStmt->execute([$reservation['user_id'], $msg, $now]);
        }

        $scoreChange = $isLate ? -2 : 1;
        $updateUser = $this->pdo->prepare("UPDATE users SET credit_score = GREATEST(0, LEAST(10, credit_score + ?)) WHERE id = ?");
        $updateUser->execute([$scoreChange, $userId]);

        return [
            'status' => 'success',
            'message' => $isLate ? "Book returned late! Fine: ₱" . number_format($fine, 2) . ". Credit score penalized." : "Book returned on time! Credit score improved.",
        ];
    }

    public function markNotificationRead(int $userId, int $notifId): array
    {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notifId, $userId]);
        return ['status' => 'success'];
    }

    public function getCartPageData(array $cartIds): array
    {
        if (empty($cartIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($cartIds), '?'));
        $stmt = $this->pdo->prepare("
            SELECT b.*,
                (
                    SELECT COUNT(*)
                    FROM borrows
                    WHERE book_id = b.id AND status = 'borrowed'
                ) as is_borrowed
            FROM books b
            WHERE b.id IN ($placeholders)
        ");
        $stmt->execute($cartIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProfileMetrics(int $userId): array
    {
        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ?");
        $stmtTotal->execute([$userId]);
        $totalBorrowed = (int)$stmtTotal->fetchColumn();

        $stmtReturned = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'returned'");
        $stmtReturned->execute([$userId]);
        $totalReturned = (int)$stmtReturned->fetchColumn();

        $stmtPending = $this->pdo->prepare("SELECT COUNT(*) FROM borrows WHERE user_id = ? AND status = 'borrowed'");
        $stmtPending->execute([$userId]);
        $totalPending = (int)$stmtPending->fetchColumn();

        return [
            'totalBorrowed' => $totalBorrowed,
            'totalReturned' => $totalReturned,
            'totalPending' => $totalPending,
        ];
    }

    public function getBorrowHistory(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT br.id as borrow_id, b.title, b.author, br.borrow_date, br.due_date, br.return_date,
                   br.status, br.fine_amount, br.is_fine_paid
            FROM borrows br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = ? AND br.status != 'reserved'
            ORDER BY br.borrow_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReservations(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT br.id as res_id, b.id as book_id, b.title, b.author, br.borrow_date as reservation_date,
                   (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as is_currently_borrowed,
                   (SELECT id FROM borrows WHERE book_id = b.id AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1) as next_in_line_res_id
            FROM borrows br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = ? AND br.status = 'reserved'
            ORDER BY br.borrow_date DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotifications(int $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOutstandingFines(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT br.id, b.title, br.due_date, br.status, br.fine_amount
            FROM borrows br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = ? AND br.is_fine_paid = FALSE
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateAccountDetails(int $userId, string $name, string $email): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        return $stmt->execute([$name, $email, $userId]);
    }

    public function updatePassword(int $userId, string $passwordHash): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$passwordHash, $userId]);
    }

    public function deleteAccount(int $userId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?")->execute([$userId]);
            $this->pdo->prepare("DELETE FROM borrows WHERE user_id = ?")->execute([$userId]);
            $this->pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
