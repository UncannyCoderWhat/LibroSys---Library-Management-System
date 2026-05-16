<?php
// dashboardController.php
require_once 'dbForLogin/db.php';

class DashboardController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get dashboard metrics data
     */
    public function getDashboardMetrics() {
        try {
            // Total Books
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total 
                FROM books 
                WHERE is_deleted = 0
            ");
            $stmt->execute();
            $totalBooks = $stmt->fetchColumn();

            // Available Books (Books that are not currently out)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total 
                FROM books b
                WHERE b.is_deleted = 0 AND b.id NOT IN (
                    SELECT book_id FROM borrows WHERE status = 'borrowed' AND return_date IS NULL
                )
            ");
            $stmt->execute();
            $availableBooks = $stmt->fetchColumn();

            // Borrowed Books (currently borrowed)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total FROM borrows WHERE status = 'borrowed' AND return_date IS NULL
            ");
            $stmt->execute();
            $borrowedBooks = $stmt->fetchColumn();

            // Exclusive Books (special collection)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total 
                FROM books 
                WHERE is_exclusive = 1 
                AND is_deleted = 0
            ");
            $stmt->execute();
            $exclusiveBooks = $stmt->fetchColumn();

            return [
                'totalBooks' => $totalBooks,
                'availableBooks' => $availableBooks,
                'borrowedBooks' => $borrowedBooks,
                'exclusiveBooks' => $exclusiveBooks
            ];

        } catch (PDOException $e) {
            error_log("Dashboard metrics error: " . $e->getMessage());
            return [
                'totalBooks' => 0,
                'availableBooks' => 0,
                'borrowedBooks' => 0,
                'exclusiveBooks' => 0
            ];
        }
    }

    /**
     * Get recent borrow activities
     */
    public function getRecentActivities($limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.title as book_title,
                    u.name as user_name,
                    br.borrow_date,
                    br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id
                WHERE b.is_deleted = 0
                ORDER BY br.borrow_date DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }
}