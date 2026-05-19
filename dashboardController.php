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
    public function getRecentActivities($limit = null) {
        try {
            $sql = "
                SELECT 
                    b.title as book_title, b.author, b.cover_path, b.is_exclusive, br.fine_amount,
                    u.name as user_name,
                    u.id as user_id, br.fine_amount,
                    br.borrow_date, br.due_date, br.return_date, br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id 
                WHERE b.is_deleted = 0
                ORDER BY br.borrow_date DESC";
            
            if ($limit) {
                $sql .= " LIMIT :limit";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            } else {
                $stmt = $this->pdo->prepare($sql);
            }

            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as &$row) {
                $daysLate = 0;
                $fine = $row['fine_amount'] ?? 0;

                $endDate = $row['return_date'] ? strtotime($row['return_date']) : time();
                $dueDate = strtotime($row['due_date']);

                if ($endDate > $dueDate && $row['status'] !== 'reserved') {
                    $diff = $endDate - $dueDate;
                    $daysLate = ceil($diff / (60 * 60 * 24));
                    
                    // Calculate live fine if not returned yet
                    if ($row['status'] === 'borrowed') {
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    }
                }
                $row['days_late'] = $daysLate;
                $row['total_fine'] = "₱" . number_format($fine, 2);
            }
            return $results;
        } catch (PDOException $e) {
            error_log("Recent activities error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get currently active borrows (status = 'borrowed')
     */
    public function getActiveBorrows() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.title, b.author, b.cover_path, b.is_exclusive,
                    u.user_id as borrower_id, u.name as borrower_name,
                    br.borrow_date, br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id
                WHERE br.status = 'borrowed' 
                AND b.is_deleted = 0
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Active borrows error: " . $e->getMessage());
            return [];
        }
    }
}