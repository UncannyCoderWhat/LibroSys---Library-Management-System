<?php
// app/Controllers/Client/ReadController.php
require_once __DIR__ . '/ClientController.php';
require_once __DIR__ . '/../../Models/Admin/BookModel.php';

class ReadController extends ClientController
{
    private BookModel $bookModel;

    public function __construct(?PDO $pdo = null)
    {
        parent::__construct($pdo);
        $this->bookModel = new BookModel($this->pdo);
    }

    public function handleRequest(array &$session): array
    {
        $authResult = $this->requireAuthentication($session);
        if ($authResult !== null) {
            return $authResult;
        }

        $userId = (int)($session['user_id'] ?? 0);
        $bookId = (int)($_GET['id'] ?? 0);

        if ($bookId <= 0) {
            return ['redirect' => 'index.php?page=library'];
        }

        $book = $this->getBook($bookId);
        if (!$book) {
            return ['redirect' => 'index.php?page=library'];
        }

        $stmt = $this->pdo->prepare("
            SELECT id, status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked', 'borrowed')
            ORDER BY borrow_date DESC LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $userBorrow = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userBorrow) {
            return ['redirect' => 'index.php?page=book_detail&id=' . $bookId];
        }

        $bookType = strtolower($book['book_type'] ?? '');
        $genre = strtolower($book['genre'] ?? '');
        $isManga = str_contains($bookType, 'manga') || str_contains($bookType, 'manhwa') || str_contains($bookType, 'manhua') || str_contains($genre, 'manga') || str_contains($genre, 'manhua') || str_contains($genre, 'webtoon');

        if ($isManga) {
            $chapterId = isset($_GET['chapter_id']) ? (int)$_GET['chapter_id'] : 0;
            $chapterData = $this->getMangaReadingData($bookId, $userId, $chapterId);
            return array_merge($chapterData, [
                'book' => $book,
                'userStatus' => $userBorrow['status'],
                'cartCount' => $this->getCartCount($session),
                'isManga' => true,
            ]);
        }

        $content = $this->generateBookContent($book);
        $ebook = $this->getBookEbook($bookId);
        $savedPage = $this->getReadingProgress($userId, $bookId);

        return [
            'book' => $book,
            'content' => $content,
            'ebook' => $ebook,
            'savedPage' => $savedPage,
            'userStatus' => $userBorrow['status'],
            'cartCount' => $this->getCartCount($session),
            'isManga' => false,
        ];
    }

    private function getBook(int $bookId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   COALESCE(a.name, b.author) as author_name
            FROM books b
            LEFT JOIN authors a ON b.author_id = a.id
            WHERE b.id = ? AND b.is_deleted = 0
        ");
        $stmt->execute([$bookId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
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

    private function getReadingProgress(int $userId, int $bookId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT page_number FROM reading_progress 
            WHERE user_id = ? AND book_id = ?
        ");
        $stmt->execute([$userId, $bookId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['page_number'] : 1;
    }

    private function generateBookContent(array $book): array
    {
        $title = $book['title'] ?? 'Untitled';
        $author = $book['author_name'] ?: ($book['author'] ?? 'Unknown Author');
        $description = $book['description'] ?? '';

        $paragraphs = [];
        if (!empty($description)) {
            $paragraphs = explode("\n", $description);
            $paragraphs = array_filter(array_map('trim', $paragraphs));
        }

        if (empty($paragraphs)) {
            $paragraphs = [
                "This is the beginning of \"" . $title . "\" by " . $author . ".",
                "The story unfolds in a world where every page turned reveals a new mystery, a new adventure, and a new character waiting to be discovered.",
                "As the protagonist navigates through the challenges ahead, readers will find themselves drawn into a narrative that explores the depths of human emotion and the power of imagination.",
                "Each chapter builds upon the last, creating a tapestry of interconnected stories that will keep you turning pages late into the night.",
                "The author's vivid descriptions and compelling characters bring this world to life, making it easy to lose yourself in the story.",
                "Whether you're a fan of the genre or new to the author's work, this book promises to be an unforgettable journey.",
                "So sit back, relax, and let the words transport you to another world. Happy reading!",
            ];
        }

        $pages = [];
        $chunkSize = 3;
        $chunks = array_chunk($paragraphs, $chunkSize);
        
        if (empty($chunks)) {
            $chunks = [["No content available for this book yet."]];
        }

        foreach ($chunks as $index => $chunk) {
            $pages[] = [
                'page_number' => $index + 1,
                'content' => implode("\n\n", $chunk),
            ];
        }

        return $pages;
    }

    private function getMangaReadingData(int $bookId, int $userId, int $requestedChapterId): array
    {
        $chapters = $this->bookModel->getMangaChapters($bookId);
        if (empty($chapters)) {
            return [
                'mangaChapters' => [],
                'mangaPages' => [],
                'currentChapter' => null,
                'currentPage' => 1,
                'nextChapter' => null,
            ];
        }

        $currentChapterId = $requestedChapterId > 0 ? $requestedChapterId : (int)($chapters[0]['id'] ?? 0);
        $currentChapter = null;
        $nextChapter = null;

        foreach ($chapters as $index => $ch) {
            if ((int)$ch['id'] === $currentChapterId) {
                $currentChapter = $ch;
                if (isset($chapters[$index + 1])) {
                    $nextChapter = $chapters[$index + 1];
                }
                break;
            }
        }

        if (!$currentChapter && !empty($chapters)) {
            $currentChapter = $chapters[0];
            $currentChapterId = (int)$currentChapter['id'];
            if (isset($chapters[1])) {
                $nextChapter = $chapters[1];
            }
        }

        $pages = $this->bookModel->getChapterPages($currentChapterId);
        $savedPage = $this->getReadingProgress($userId, $bookId);

        return [
            'mangaChapters' => $chapters,
            'mangaPages' => $pages,
            'currentChapter' => $currentChapter,
            'currentPage' => $savedPage,
            'nextChapter' => $nextChapter,
        ];
    }
}
