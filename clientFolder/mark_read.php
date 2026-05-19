<?php
session_start();
require_once '../dbForLogin/db.php';

if (!isset($_SESSION['user_logged_in'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notif_id = $_POST['notification_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$notif_id, $user_id]);
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error']);
    }
}
?>