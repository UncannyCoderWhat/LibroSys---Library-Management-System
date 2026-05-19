<?php
session_start();
require_once '../dbForLogin/db.php';

if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_id'])) {
    $borrow_id = $_POST['borrow_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // 1. Verify the record exists and belongs to this user
        $stmt = $pdo->prepare("
            SELECT br.*, b.title 
            FROM borrows br 
            JOIN books b ON br.book_id = b.id 
            WHERE br.id = ? AND br.user_id = ? AND br.status = 'borrowed'");
        $stmt->execute([$borrow_id, $user_id]);
        $borrow = $stmt->fetch();

        if (!$borrow) {
            echo json_encode(['status' => 'error', 'message' => 'Active borrow record not found.']);
            exit();
        }

        $now = date('Y-m-d H:i:s');
        $due_date = $borrow['due_date'];

        // 3. Credit Score Logic: Reward for on-time (+1), Penalty for late (-2)
        $isLate = strtotime($now) > strtotime($due_date);
        $fine = 0;

        if ($isLate) {
            $diff = strtotime($now) - strtotime($due_date);
            $daysLate = ceil($diff / (60 * 60 * 24));
            
            if ($daysLate <= 3) $fine = $daysLate * 50;
            elseif ($daysLate <= 10) $fine = $daysLate * 100;
            else $fine = $daysLate * 150;
        }

        // 2. Update status, return date, and fine
        $update = $pdo->prepare("UPDATE borrows SET status = 'returned', return_date = ?, fine_amount = ? WHERE id = ?");
        $update->execute([$now, $fine, $borrow_id]);

        // 4. Notification Logic: Check if anyone reserved this book
        $resStmt = $pdo->prepare("SELECT user_id FROM borrows WHERE book_id = ? AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1");
        $resStmt->execute([$borrow['book_id']]);
        $reservation = $resStmt->fetch();

        if ($reservation) {
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, ?)");
            $msg = "The book '" . $borrow['title'] . "' you reserved is now available!";
            $notifStmt->execute([$reservation['user_id'], $msg, $now]);
        }

        $scoreChange = $isLate ? -2 : 1;

        // Use SQL GREATEST/LEAST to keep score between 0 and 10
        $updateUser = $pdo->prepare("UPDATE users SET credit_score = GREATEST(0, LEAST(10, credit_score + ?)) WHERE id = ?");
        $updateUser->execute([$scoreChange, $user_id]);

        echo json_encode([
            'status' => 'success', 
            'message' => $isLate ? "Book returned late! Fine: ₱" . number_format($fine, 2) . ". Credit score penalized." : "Book returned on time! Credit score improved."
        ]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>