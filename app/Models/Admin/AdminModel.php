<?php
// app/Models/Admin/AdminModel.php
// Consolidated admin model — all business logic for admin operations

class AdminModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ==================== ACTIVITY LOG ====================

    public function logActivity(string $adminId, string $action, string $details = ''): void
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, details) VALUES (?, ?, ?)");
            $stmt->execute([$adminId, $action, $details]);
        } catch (PDOException $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }

    public function getActivityLogs(int $limit = 50): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id, admin_id, action, details, created_at
                FROM admin_activity_log
                ORDER BY created_at DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get activity logs error: " . $e->getMessage());
            return [];
        }
    }

    // ==================== AUTHENTICATION ====================

    public function authenticateAdmin(string $adminId, string $password): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
        $stmt->execute([$adminId]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return null;
    }

    public function registerAdmin(string $adminId, string $password, string $confirmPassword): array
    {
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match!'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long!'];
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $checkStmt = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_id = ?");
            $checkStmt->execute([$adminId]);

            if ($checkStmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Admin ID already exists. Please choose another.'];
            }

            $stmt = $this->pdo->prepare("INSERT INTO admins (admin_id, password) VALUES (?, ?)");
            $stmt->execute([$adminId, $hashed_password]);
            return ['success' => true, 'message' => 'Registration successful! You can now login.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database Error: ' . $e->getMessage()];
        }
    }

    // ==================== DASHBOARD METRICS ====================

    public function getDashboardMetrics(): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM books WHERE is_deleted = 0");
            $stmt->execute();
            $totalBooks = (int)$stmt->fetchColumn();

            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total FROM books b
                WHERE b.is_deleted = 0 AND b.id NOT IN (
                    SELECT book_id FROM borrows WHERE status IN ('borrowed', 'reserved')
                )
            ");
            $stmt->execute();
            $availableBooks = (int)$stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM borrows WHERE status = 'borrowed' AND return_date IS NULL");
            $stmt->execute();
            $borrowedBooks = (int)$stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM books WHERE is_exclusive = 1 AND is_deleted = 0");
            $stmt->execute();
            $exclusiveBooks = (int)$stmt->fetchColumn();

            return [
                'totalBooks' => $totalBooks,
                'availableBooks' => $availableBooks,
                'borrowedBooks' => $borrowedBooks,
                'exclusiveBooks' => $exclusiveBooks
            ];
        } catch (PDOException $e) {
            error_log("Dashboard metrics error: " . $e->getMessage());
            return ['totalBooks' => 0, 'availableBooks' => 0, 'borrowedBooks' => 0, 'exclusiveBooks' => 0];
        }
    }

    public function getRecentActivities(?int $limit = null): array
    {
        try {
            $sql = "
                SELECT b.title as book_title, b.author, b.cover_path, b.is_exclusive, br.fine_amount,
                    u.name as user_name, u.id as user_id,
                    br.borrow_date, br.due_date, br.return_date, br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id
                WHERE b.is_deleted = 0 AND br.status != 'reserved'
                ORDER BY br.borrow_date DESC
            ";

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
                $fine = (float)($row['fine_amount'] ?? 0);

                if (($row['status'] ?? null) !== 'reserved' && !empty($row['due_date'])) {
                    $endDate = $row['return_date'] ? strtotime($row['return_date']) : time();
                    $dueDate = strtotime($row['due_date']);

                    if ($endDate > $dueDate) {
                        $diff = $endDate - $dueDate;
                        $daysLate = (int)ceil($diff / (60 * 60 * 24));

                        if (($row['status'] ?? null) === 'borrowed') {
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

    public function getReservationActivities(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.title as book_title, b.author, br.id as res_id,
                    u.name as user_name, br.borrow_date as reservation_date, br.status,
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

    public function getActiveBorrows(): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.title, b.author, b.cover_path, b.is_exclusive,
                    u.user_id as borrower_id, u.name as borrower_name,
                    br.borrow_date, br.status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                JOIN users u ON br.user_id = u.id
                WHERE br.status = 'borrowed' AND b.is_deleted = 0
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Active borrows error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserTotalFines(int $userId): float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT br.due_date, br.return_date, br.status, br.fine_amount
                FROM borrows br WHERE br.user_id = ? AND br.is_fine_paid = FALSE
            ");
            $stmt->execute([$userId]);
            $userBorrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalFines = 0;
            foreach ($userBorrows as $borrow) {
                $fine = (float)($borrow['fine_amount'] ?? 0);
                if ($borrow['status'] === 'borrowed' && !empty($borrow['due_date'])) {
                    $now = time();
                    $dueDate = strtotime($borrow['due_date']);
                    if ($now > $dueDate) {
                        $daysLate = (int)ceil(($now - $dueDate) / (60 * 60 * 24));
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    } else { $fine = 0; }
                }
                $totalFines += $fine;
            }
            return $totalFines;
        } catch (PDOException $e) {
            error_log("Error calculating total fines for user " . $userId . ": " . $e->getMessage());
            return 0;
        }
    }

    public function getUserFineDetails(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.title, br.due_date, br.return_date, br.status, br.fine_amount, br.is_fine_paid
                FROM borrows br JOIN books b ON br.book_id = b.id
                WHERE br.user_id = ? AND (br.fine_amount > 0 OR (br.status = 'borrowed' AND br.due_date < NOW()))
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute([$userId]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($records as &$row) {
                $fine = (float)($row['fine_amount'] ?? 0);
                $isOverdue = false;

                if ($row['status'] === 'borrowed' && !empty($row['due_date'])) {
                    $now = time();
                    $dueDate = strtotime($row['due_date']);
                    if ($now > $dueDate) {
                        $isOverdue = true;
                        $daysLate = (int)ceil(($now - $dueDate) / (60 * 60 * 24));
                        if ($daysLate <= 3) $fine = $daysLate * 50;
                        elseif ($daysLate <= 10) $fine = $daysLate * 100;
                        else $fine = $daysLate * 150;
                    }
                }
                $row['calculated_fine'] = $fine;
                $row['is_live_overdue'] = $isOverdue;
            }

            return $records;
        } catch (PDOException $e) {
            error_log("Error fetching fine details: " . $e->getMessage());
            return [];
        }
    }

    public function getUsersWithStatus(): array
    {
        try {
            $sql = "
                SELECT u.id as user_id, u.name, u.email, u.credit_score,
                    COUNT(CASE WHEN br.status = 'borrowed' THEN 1 END) as active_borrows,
                    SUM(CASE WHEN br.status = 'borrowed' THEN br.fine_amount ELSE 0 END) as total_fines
                FROM users u
                LEFT JOIN borrows br ON u.id = br.user_id
                GROUP BY u.id, u.name, u.email, u.credit_score
                ORDER BY u.name ASC
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['account_status'] = ((int)($user['active_borrows'] ?? 0) > 0) ? 'Active' : 'Inactive';
            }

            return $users;
        } catch (PDOException $e) {
            error_log("Get users status error: " . $e->getMessage());
            return [];
        }
    }

    // ==================== USER MANAGEMENT ====================

    public function getAllUsers(): array
    {
        try {
            $stmt = $this->pdo->query("
                SELECT u.*,
                    (SELECT COUNT(*) FROM borrows WHERE user_id = u.id AND status = 'borrowed') as active_borrows
                FROM users u
                ORDER BY u.id DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all users: " . $e->getMessage());
            return [];
        }
    }

    public function canDeleteUser(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM borrows WHERE user_id = ? AND status = 'borrowed'");
            $stmt->execute([$userId]);
            $activeBorrows = (int)$stmt->fetchColumn();

            if ($activeBorrows > 0) {
                return ['allowed' => false, 'message' => 'Cannot delete user: user still has active borrows.'];
            }

            return ['allowed' => true, 'message' => ''];
        } catch (PDOException $e) {
            return ['allowed' => false, 'message' => 'Database error checking user status.'];
        }
    }

    public function deleteUserWithHistory(int $userId): bool
    {
        try {
            // Start a transaction to ensure all-or-nothing execution
            $this->pdo->beginTransaction();

            // 2. Delete borrow records
            $stmt = $this->pdo->prepare("DELETE FROM borrows WHERE user_id = ?");
            $stmt->execute([$userId]);

            // 3. Delete the user (Double-check if your primary key column is 'id' or 'user_id')
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            // Rollback changes if something goes wrong
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Write the exact DB error message to your server error log
            error_log("Error deleting user with ID {$userId}: " . $e->getMessage());
            return false;
        }
    }

    public function getUserCirculationLogs(int $userId): array
    {
        $sql = "
            SELECT 
                b.title,
                b.author,
                b.is_exclusive AS type,
                TIME(br.borrow_date) AS time_borrowed,
                DATE(br.borrow_date) AS date_borrowed,
                br.due_date,
                br.return_date AS date_returned,
                br.fine_amount,
                br.status
            FROM borrows br
            JOIN books b ON br.book_id = b.id
            WHERE br.user_id = :user_id
            ORDER BY br.borrow_date DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($logs as &$log) {
            $daysLate = 0;
            $fine = (float)($log['fine_amount'] ?? 0);

            if (!empty($log['due_date'])) {
                $endDate = !empty($log['date_returned']) ? strtotime($log['date_returned']) : time();
                $dueDate = strtotime($log['due_date']);

                if ($endDate > $dueDate) {
                    $daysLate = (int)ceil(($endDate - $dueDate) / 86400);

                    if ($log['status'] === 'borrowed') {
                        if ($daysLate <= 3) {
                            $fine = $daysLate * 50;
                        } elseif ($daysLate <= 10) {
                            $fine = $daysLate * 100;
                        } else {
                            $fine = $daysLate * 150;
                        }
                    }
                }
            }

            $log['type'] = $log['type'] ? 'Exclusive' : 'Regular';
            $log['days_late'] = $daysLate;
            $log['total_fine'] = number_format($fine, 2);

            if (!empty($log['time_borrowed'])) {
                $log['time_borrowed'] = date("h:i A", strtotime($log['time_borrowed']));
            }
            if (!empty($log['date_borrowed'])) {
                $log['date_borrowed'] = date("M d, Y", strtotime($log['date_borrowed']));
            }
            if (!empty($log['due_date'])) {
                $log['due_date'] = date("M d, Y", strtotime($log['due_date']));
            }
            if (!empty($log['date_returned'])) {
                $log['date_returned'] = date("M d, Y", strtotime($log['date_returned']));
            }
        }
        unset($log);

        return $logs;
    }

    // ==================== ADMIN SETTINGS ====================

    public function getAdminBySession(string $adminUser): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
            $stmt->execute([$adminUser]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            return $admin ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function updateAdminId(string $currentId, string $newId): array
    {
        if (empty(trim($newId))) {
            return ['success' => false, 'message' => 'Admin ID cannot be empty.'];
        }

        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE admin_id = ?");
            $stmt->execute([$newId]);
            if ($stmt->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Admin ID already taken.'];
            }

            $stmt = $this->pdo->prepare("UPDATE admins SET admin_id = ? WHERE admin_id = ?");
            $stmt->execute([$newId, $currentId]);
            return ['success' => true, 'message' => 'Admin ID updated successfully.', 'new_id' => $newId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function updatePassword(string $adminUser, string $oldPass, string $newPass, string $repeatPass, array $admin): array
    {
        if ($newPass !== $repeatPass) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }

        if (strlen($newPass) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
        }

        if (!password_verify($oldPass, $admin['password'] ?? '')) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        try {
            $hashed = password_hash($newPass, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE admins SET password = ? WHERE admin_id = ?");
            $stmt->execute([$hashed, $adminUser]);
            return ['success' => true, 'message' => 'Password updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function deleteAdminAccount(string $adminUser): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
            $stmt->execute([$adminUser]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}
