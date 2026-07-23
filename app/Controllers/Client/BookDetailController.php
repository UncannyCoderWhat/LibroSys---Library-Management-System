<?php
// app/Controllers/Client/BookDetailController.php
require_once __DIR__ . '/ClientController.php';

class BookDetailController extends ClientController
{
    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
    }

    public function handleRequest(array &$session): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        $userId = (int)($session['user_id'] ?? 0);
        $bookId = (int)($_GET['id'] ?? 0);

        // Handle read_now / bookmark actions from book detail buttons
        $action = $_GET['action'] ?? '';
        if ($action === 'read_now' && $bookId > 0) {
            $this->handleReadNow($userId, $bookId);
            return ['redirect' => 'index.php?page=library'];
        }
        if ($action === 'bookmark' && $bookId > 0) {
            $this->handleBookmark($userId, $bookId);
            return ['redirect' => 'index.php?page=library'];
        }

        if ($bookId <= 0) {
            return ['redirect' => 'index.php?page=home'];
        }

        $book = $this->getBookDetail($bookId);
        if (!$book) {
            return ['redirect' => 'index.php?page=home'];
        }

        $userStatus = $this->getUserBookStatus($userId, $bookId);
        $ebook = $this->getBookEbook($bookId);

        return [
            'book' => $book,
            'userStatus' => $userStatus,
            'ebook' => $ebook,
            'cartCount' => $this->getCartCount($session),
        ];
    }

    private function getBookDetail(int $bookId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   COALESCE(a.name, b.author) as author_name,
                   (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as borrowed_count
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            WHERE b.id = ? AND b.is_deleted = 0 AND b.status != 'archived'
        ");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            return null;
        }

        // Get available copies count
        $totalCopies = (int)($book['copies'] ?? 1);
        $borrowedCount = (int)($book['borrowed_count'] ?? 0);
        $book['available_copies'] = max(0, $totalCopies - $borrowedCount);

        return $book;
    }

    private function getUserBookStatus(int $userId, int $bookId): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked', 'borrowed', 'reserved')
            ORDER BY borrow_date DESC LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['status'] : null;
    }

    private function getBookEbook(int $bookId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM ebooks 
            WHERE book_id = ? AND file_type = 'pdf' 
            LIMIT 1
        ");
        $stmt->execute([$bookId]);
        $ebook = $stmt->fetch(PDO::FETCH_ASSOC);
        return $ebook ?: null;
    }

    public function handleReadNow(int $userId, int $bookId): array
    {
        // Check if already reading or bookmarked
        $stmt = $this->pdo->prepare("
            SELECT id, status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked')
        ");
        $stmt->execute([$userId, $bookId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['status'] === 'bookmarked') {
                // Upgrade from bookmarked to reading
                $update = $this->pdo->prepare("UPDATE borrows SET status = 'reading', borrow_date = NOW() WHERE id = ?");
                $update->execute([$existing['id']]);
                return ['status' => 'success', 'message' => 'Book moved to Reading!'];
            }
            return ['status' => 'info', 'message' => 'Book is already in your Reading list.'];
        }

        // Check book exists and is not deleted or archived
        $stmt = $this->pdo->prepare("SELECT id FROM books WHERE id = ? AND is_deleted = 0 AND status != 'archived'");
        $stmt->execute([$bookId]);
        if (!$stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Book not found or no longer available.'];
        }

        $insert = $this->pdo->prepare("
            INSERT INTO borrows (book_id, user_id, borrow_date, status) 
            VALUES (?, ?, NOW(), 'reading')
        ");
        $insert->execute([$bookId, $userId]);
        return ['status' => 'success', 'message' => 'Book added to your Reading list!'];
    }

    public function handleBookmark(int $userId, int $bookId): array
    {
        // Check if already reading or bookmarked
        $stmt = $this->pdo->prepare("
            SELECT id, status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked')
        ");
        $stmt->execute([$userId, $bookId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['status'] === 'reading') {
                return ['status' => 'info', 'message' => 'You are currently reading this book!'];
            }
            return ['status' => 'info', 'message' => 'Book is already bookmarked.'];
        }

        // Check book exists and is not deleted or archived
        $stmt = $this->pdo->prepare("SELECT id FROM books WHERE id = ? AND is_deleted = 0 AND status != 'archived'");
        $stmt->execute([$bookId]);
        if (!$stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Book not found or no longer available.'];
        }

        $insert = $this->pdo->prepare("
            INSERT INTO borrows (book_id, user_id, borrow_date, status) 
            VALUES (?, ?, NOW(), 'bookmarked')
        ");
        $insert->execute([$bookId, $userId]);
        return ['status' => 'success', 'message' => 'Book bookmarked!'];
    }
}
