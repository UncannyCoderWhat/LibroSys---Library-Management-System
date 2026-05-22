<?php
class UserManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT u.*, (SELECT COUNT(*) FROM borrows WHERE user_id = u.id AND status = 'borrowed') as active_borrows FROM users u");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUserWithHistory($userId) {
        // Logic moved from users.php to follow Encapsulation principles
        $delNotifs = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $delNotifs->execute([$userId]);
        $delHistory = $this->pdo->prepare("DELETE FROM borrows WHERE user_id = ?");
        $delHistory->execute([$userId]);
        $delUser = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $delUser->execute([$userId]);
    }
}
?>