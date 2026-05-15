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

            // Available Books (not borrowed or reserved)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total 
                FROM books b
                LEFT JOIN borrows br ON b.id = br.book_id 
                    AND br.status IN ('borrowed', 'reserved')
                WHERE b.is_deleted = 0 
                AND br.id IS NULL
            ");
            $stmt->execute();
            $availableBooks = $stmt->fetchColumn();

            // Borrowed Books (currently borrowed)
            $stmt = $this->pdo->prepare("
                SELECT COUNT(DISTINCT book_id) as total 
                FROM borrows 
                WHERE status = 'borrowed' 
                AND return_date IS NULL
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

// Usage in dashboard.php
try {
    require_once 'dbForLogin/db.php';

    // Create controller instance
    $dashboardController = new DashboardController($pdo);
    
    // Get metrics data
    $metrics = $dashboardController->getDashboardMetrics();
    $totalBooks = $metrics['totalBooks'];
    $availableBooks = $metrics['availableBooks'];
    $borrowedBooks = $metrics['borrowedBooks'];
    $exclusiveBooks = $metrics['exclusiveBooks'];
    
    // Get recent activities
    $activities = $dashboardController->getRecentActivities(10);
    
} catch (Exception $e) {
    error_log("Dashboard controller error: " . $e->getMessage());
    // Set default values on error
    $totalBooks = $availableBooks = $borrowedBooks = $exclusiveBooks = 0;
    $activities = [];
}
?>