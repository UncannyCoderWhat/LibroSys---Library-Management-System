<?php
// app/Controllers/Client/ReadController.php
require_once __DIR__ . '/ClientController.php';

class ReadController extends ClientController
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

        if ($bookId <= 0) {
            return ['redirect' => 'index.php?page=library'];
        }

        // Fetch the book
        $book = $this->getBook($bookId);
        if (!$book) {
            return ['redirect' => 'index.php?page=library'];
        }

        // Check if user has this book in their library (reading, bookmarked, or borrowed)
        $stmt = $this->pdo->prepare("
            SELECT id, status FROM borrows 
            WHERE user_id = ? AND book_id = ? 
            AND status IN ('reading', 'bookmarked', 'borrowed')
            ORDER BY borrow_date DESC LIMIT 1
        ");
        $stmt->execute([$userId, $bookId]);
        $userBorrow = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user doesn't have this book in their library, redirect
        if (!$userBorrow) {
            return ['redirect' => 'index.php?page=book_detail&id=' . $bookId];
        }

        // Generate the book content pages
        $content = $this->generateBookContent($book);

        return [
            'book' => $book,
            'content' => $content,
            'userStatus' => $userBorrow['status'],
            'cartCount' => $this->getCartCount($session),
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

    /**
     * Generate sample book content for reading.
     * In production, this would come from a book_contents table or file storage.
     * For now, we generate placeholder content based on the book's description.
     */
    private function generateBookContent(array $book): array
    {
        $title = $book['title'] ?? 'Untitled';
        $author = $book['author_name'] ?: ($book['author'] ?? 'Unknown Author');
        $description = $book['description'] ?? '';

        // Split description into paragraphs for the sample content
        $paragraphs = [];
        if (!empty($description)) {
            $paragraphs = explode("\n", $description);
            $paragraphs = array_filter(array_map('trim', $paragraphs));
        }

        // If no description, create some sample paragraphs
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

        // Build pages: each page is a set of 2-3 paragraphs
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
}
