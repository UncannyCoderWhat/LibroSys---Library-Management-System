<?php
// dashboardController.php
require_once '../dbForLogin/db.php';
date_default_timezone_set('Asia/Manila');

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
                    SELECT book_id FROM borrows WHERE status IN ('borrowed', 'reserved')
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
                AND br.status != 'reserved'
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

                // Only calculate fines/late days if it's not a reservation and has a due date
                if ($row['status'] !== 'reserved' && !empty($row['due_date'])) {
                    $endDate = $row['return_date'] ? strtotime($row['return_date']) : time();
                    $dueDate = strtotime($row['due_date']);
                    
                    if ($endDate > $dueDate) {
                    $diff = $endDate - $dueDate;
                    $daysLate = ceil($diff / (60 * 60 * 24));
                    
                    // Calculate live fine if not returned yet
                    if ($row['status'] === 'borrowed') {
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    }
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
     * Get all active reservation activities
     */
    public function getReservationActivities() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    b.title as book_title, b.author, br.id as res_id,
                    u.name as user_name,
                    br.borrow_date as reservation_date, br.status,
                    (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as is_currently_borrowed,
                    (SELECT id FROM borrows WHERE book_id = b.id AND status = 'reserved' ORDER BY borrow_date ASC LIMIT 1) as next_in_line_res_id
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id 
                WHERE br.status = 'reserved' AND b.is_deleted = 0
                ORDER BY br.borrow_date DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Reservation activities error: " . $e->getMessage());
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

    /**
     * Get total fines owed by a specific user, including live calculation for overdue borrowed books.
     * @param int $userId The ID of the user.
     * @return float The total fines owed.
     */
    public function getUserTotalFines($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT 
                    br.due_date, br.return_date, br.status, br.fine_amount
                FROM borrows br
                WHERE br.user_id = ? AND br.is_fine_paid = FALSE
            ");
            $stmt->execute([$userId]);
            $userBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalFines = 0;
            foreach ($userBorrows as $borrow) {
                $fine = $borrow['fine_amount'] ?? 0; // Stored fine for returned books

                // Calculate live fine for currently borrowed and overdue books
                if ($borrow['status'] === 'borrowed' && !empty($borrow['due_date'])) {
                    $now = time();
                    $dueDate = strtotime($borrow['due_date']);
                    if ($now > $dueDate) {
                        $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    } else { $fine = 0; } // Not overdue yet
                }
                $totalFines += $fine;
            }
            return $totalFines;
        } catch (PDOException $e) {
            error_log("Error calculating total fines for user " . $userId . ": " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get detailed fine history for a specific user
     */
    public function getUserFineDetails($userId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.title, br.due_date, br.return_date, br.status, br.fine_amount, br.is_fine_paid
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                WHERE br.user_id = ? AND (br.fine_amount > 0 OR (br.status = 'borrowed' AND br.due_date < NOW()))
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute([$userId]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($records as &$row) {
                $fine = $row['fine_amount'] ?? 0;
                $isOverdue = false;

                if ($row['status'] === 'borrowed' && !empty($row['due_date'])) {
                    $now = time();
                    $dueDate = strtotime($row['due_date']);
                    if ($now > $dueDate) {
                        $isOverdue = true;
                        $daysLate = ceil(($now - $dueDate) / (60 * 60 * 24));
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    }
                }
                // Note: fine_amount in DB will now store the incurred fine, not necessarily the outstanding one.
                $row['calculated_fine'] = $fine;
                $row['is_live_overdue'] = $isOverdue;
            }

            return $records;
        } catch (PDOException $e) {
            error_log("Error fetching fine details: " . $e->getMessage());
            return [];
        }
    }
}