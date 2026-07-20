<?php
// app/Controllers/Client/LibraryController.php
require_once __DIR__ . '/ClientController.php';

class LibraryController extends ClientController
{
    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        if ($this->pdo) {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    public function handleRequest(array &$session): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        $userId = (int)($session['user_id'] ?? 0);
        if ($userId <= 0) {
            return ['redirect' => 'index.php?page=login'];
        }

        $debugInfo = [];

        // Check total borrows for this user
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM borrows WHERE user_id = ?");
            $stmt->execute([$userId]);
            $debugInfo['total_borrows'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $debugInfo['total_borrows_error'] = $e->getMessage();
        }

        // Check how many reading status
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM borrows WHERE user_id = ? AND status = 'reading'");
            $stmt->execute([$userId]);
            $debugInfo['reading_count_db'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $debugInfo['reading_count_error'] = $e->getMessage();
        }

        // Check how many bookmarked status
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as cnt FROM borrows WHERE user_id = ? AND status = 'bookmarked'");
            $stmt->execute([$userId]);
            $debugInfo['bookmarked_count_db'] = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $debugInfo['bookmarked_count_error'] = $e->getMessage();
        }

        // Fetch books grouped by status
        $readingBooks = $this->getBooksByStatus($userId, 'reading');
        $bookmarkedBooks = $this->getBooksByStatus($userId, 'bookmarked');
        $borrowedBooks = $this->getBooksByStatus($userId, 'borrowed');
        $historyBooks = $this->getHistoryBooks($userId);

        $debugInfo['readingBooks_count'] = count($readingBooks);
        $debugInfo['bookmarkedBooks_count'] = count($bookmarkedBooks);
        $debugInfo['borrowedBooks_count'] = count($borrowedBooks);
        $debugInfo['historyBooks_count'] = count($historyBooks);
        $debugInfo['user_id'] = $userId;

        return [
            'readingBooks' => $readingBooks,
            'bookmarkedBooks' => $bookmarkedBooks,
            'borrowedBooks' => $borrowedBooks,
            'historyBooks' => $historyBooks,
            'cartCount' => $this->getCartCount($session),
            'debugInfo' => $debugInfo,
        ];
    }

    private function getBooksByStatus(int $userId, string $status): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.id, b.title, b.author, b.cover_path, b.is_exclusive, b.genre,
                       b.description, b.isbn, b.publisher, b.publication_year, b.language,
                       b.shelf_location, b.copies, b.status as book_status,
                       br.id as borrow_id,
                       br.borrow_date,
                       br.due_date,
                       br.status as borrow_status
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                WHERE br.user_id = ? AND br.status = ? AND b.is_deleted = 0
                ORDER BY br.borrow_date DESC
            ");
            $stmt->execute([$userId, $status]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$row) {
                $row['author_name'] = $row['author'] ?? 'Unknown';
            }
            return $results;
        } catch (Exception $e) {
            // Return error info in the results so we can see it
            return [];
        }
    }

    private function getHistoryBooks(int $userId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT b.id, b.title, b.author, b.cover_path, b.is_exclusive, b.genre,
                       br.id as borrow_id,
                       br.borrow_date,
                       br.due_date,
                       br.return_date,
                       br.status as borrow_status,
                       br.fine_amount
                FROM borrows br
                JOIN books b ON br.book_id = b.id
                WHERE br.user_id = ? AND br.status IN ('returned') AND b.is_deleted = 0
                ORDER BY br.return_date DESC
            ");
            $stmt->execute([$userId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as &$row) {
                $row['author_name'] = $row['author'] ?? 'Unknown';
            }
            return $results;
        } catch (Exception $e) {
            return [];
        }
    }
}
