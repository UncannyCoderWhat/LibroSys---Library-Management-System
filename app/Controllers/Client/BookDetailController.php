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
            return ['redirect' => 'index.php?page=book_detail&id=' . $bookId];
        }
        if ($action === 'bookmark' && $bookId > 0) {
            $this->handleBookmark($userId, $bookId);
            return ['redirect' => 'index.php?page=book_detail&id=' . $bookId];
        }

        if ($bookId <= 0) {
            return ['redirect' => 'index.php?page=home'];
        }

        $book = $this->getBookDetail($bookId);
        if (!$book) {
            return ['redirect' => 'index.php?page=home'];
        }

        $ebook = $this->getBookEbook($bookId);
        $savedPage = 1;
        $savedChapterId = 0;
        $userBorrow = null;

        // Determine each independent status the user has for this book
        $userStatuses = $this->getUserBookStatuses($userId, $bookId);
        $isReading = $userStatuses['reading'];
        $isBookmarked = $userStatuses['bookmarked'];
        $isBorrowed = $userStatuses['borrowed'];
        $isReserved = $userStatuses['reserved'];

        // Prefer borrow record if both exist (for extend/return actions)
        if ($isBorrowed) {
            $stmt = $this->pdo->prepare("
                SELECT id, status, due_date, extension_used FROM borrows 
                WHERE user_id = ? AND book_id = ? AND status = 'borrowed'
                LIMIT 1
            ");
            $stmt->execute([$userId, $bookId]);
            $userBorrow = $stmt->fetch(PDO::FETCH_ASSOC);
        } elseif ($isReading) {
            $stmt = $this->pdo->prepare("
                SELECT id, status, due_date, extension_used FROM borrows 
                WHERE user_id = ? AND book_id = ? AND status = 'reading'
                LIMIT 1
            ");
            $stmt->execute([$userId, $bookId]);
            $userBorrow = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $bookType = strtolower($book['book_type'] ?? '');
        $genre = strtolower($book['genre'] ?? '');
        $isManga = str_contains($bookType, 'manga') || str_contains($bookType, 'manhwa') || str_contains($bookType, 'manhua') || str_contains($genre, 'manga') || str_contains($genre, 'manhua') || str_contains($genre, 'webtoon');

        if ($isManga) {
            $progress = $this->getMangaReadingProgress($userId, $bookId);
            $savedPage = $progress['page_number'] ?? 1;
            $savedChapterId = $progress['chapter_id'] ?? 0;
        } else {
            $savedPage = $this->getReadingProgress($userId, $bookId);
        }

        return [
            'book' => $book,
            'userStatus' => $userStatuses,  // now an array of booleans
            'isReading' => $isReading,
            'isBookmarked' => $isBookmarked,
            'isBorrowed' => $isBorrowed,
            'isReserved' => $isReserved,
            'ebook' => $ebook,
            'cartCount' => $this->getCartCount($session),
            'savedPage' => $savedPage,
            'savedChapterId' => $savedChapterId,
            'userBorrow' => $userBorrow,
        ];
    }

    private function getBookDetail(int $bookId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   COALESCE(a.name, b.author) as author_name,
                   (SELECT COUNT(*) FROM borrows WHERE book_id = b.id AND status = 'borrowed') as borrowed_count,
                   (SELECT COUNT(*) FROM book_copies WHERE book_id = b.id) as actual_copies
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            WHERE b.id = ? AND b.is_deleted = 0 AND b.status != 'archived'
        ");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            return null;
        }

        // Use book_copies table as the sole source of truth for total copies
        $totalCopies = (int)($book['actual_copies'] ?? 0);

        $borrowedCount = (int)($book['borrowed_count'] ?? 0);
        $book['available_copies'] = max(0, $totalCopies - $borrowedCount);
        // Override the denormalized 'copies' field with the actual count from book_copies
        $book['copies'] = $totalCopies;

        return $book;
    }

    private function getUserBookStatuses(int $userId, int $bookId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked', 'borrowed', 'reserved')
        ");
        $stmt->execute([$userId, $bookId]);
        $rows = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return [
            'reading' => in_array('reading', $rows, true),
            'bookmarked' => in_array('bookmarked', $rows, true),
            'borrowed' => in_array('borrowed', $rows, true),
            'reserved' => in_array('reserved', $rows, true),
        ];
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

    private function getMangaReadingProgress(int $userId, int $bookId): array
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT chapter_id, page_number FROM reading_progress 
                WHERE user_id = ? AND book_id = ? AND chapter_id IS NOT NULL
                ORDER BY updated_at DESC
                LIMIT 1
            ");
            $stmt->execute([$userId, $bookId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return [
                    'chapter_id' => (int)$row['chapter_id'],
                    'page_number' => (int)$row['page_number'],
                ];
            }
        } catch (PDOException $e) {
            // chapter_id column may not exist yet; fall back to generic progress below
        }

        $stmt = $this->pdo->prepare("
            SELECT page_number FROM reading_progress 
            WHERE user_id = ? AND book_id = ?
            ORDER BY updated_at DESC
            LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'chapter_id' => 0,
            'page_number' => $row ? (int)$row['page_number'] : 1,
        ];
    }

    private function getReadingProgress(int $userId, int $bookId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT page_number FROM reading_progress 
            WHERE user_id = ? AND book_id = ? AND (chapter_id IS NULL OR chapter_id = 0)
            ORDER BY updated_at DESC LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['page_number'] : 1;
    }

    public function handleReadNow(int $userId, int $bookId): array
    {
        // Check if user already has a 'reading' record for this book
        $stmt = $this->pdo->prepare("
            SELECT id FROM borrows 
            WHERE user_id = ? AND book_id = ? AND status = 'reading'
            LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $existingReading = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingReading) {
            return ['status' => 'info', 'message' => 'You are already reading this book.'];
        }

        // Check if user has a 'bookmarked' record to upgrade
        $stmt = $this->pdo->prepare("
            SELECT id FROM borrows 
            WHERE user_id = ? AND book_id = ? AND status = 'bookmarked'
            LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $existingBookmark = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingBookmark) {
            $update = $this->pdo->prepare("UPDATE borrows SET status = 'reading', borrow_date = NOW() WHERE id = ?");
            $update->execute([$existingBookmark['id']]);
            $this->bookModel->syncBookAvailability($bookId);
            return ['status' => 'success', 'message' => 'Book moved to Reading!'];
        }

        $stmt = $this->pdo->prepare("SELECT id FROM books WHERE id = ? AND is_deleted = 0 AND status != 'archived'");
        $stmt->execute([$bookId]);
        if (!$stmt->fetch()) {
            return ['status' => 'error', 'message' => 'Book not found or no longer available.'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO borrows (book_id, user_id, borrow_date, status) 
            VALUES (?, ?, NOW(), 'reading')
        ");
        $stmt->execute([$bookId, $userId]);

        $this->bookModel->syncBookAvailability($bookId);

        return ['status' => 'success', 'message' => 'Book added to your Reading list!'];
    }

    public function handleBookmark(int $userId, int $bookId): array
    {
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

            if ($existing['status'] === 'bookmarked') {
                $this->pdo->prepare("DELETE FROM borrows WHERE id = ? AND user_id = ? AND book_id = ? AND status = 'bookmarked'")
                    ->execute([$existing['id'], $userId, $bookId]);
                $this->bookModel->syncBookAvailability($bookId);
                return ['status' => 'success', 'message' => 'Bookmark removed.'];
            }
        }

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