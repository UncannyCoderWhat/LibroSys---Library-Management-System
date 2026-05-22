<?php
class BookManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * GUIDELINE 5A: XML Export using DOMDocument
     */
    public function exportBooksToXML() {
        $stmt = $this->pdo->query("SELECT * FROM books WHERE is_deleted = 0");
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;

        $root = $dom->createElement("books");
        $dom->appendChild($root);

        foreach ($books as $book) {
            $node = $dom->createElement("book");
            
            // Using createTextNode is safer for data containing symbols like & or < >
            $idNode = $dom->createElement("id");
            $idNode->appendChild($dom->createTextNode($book['id']));
            $node->appendChild($idNode);

            $titleNode = $dom->createElement("title");
            $titleNode->appendChild($dom->createTextNode($book['title']));
            $node->appendChild($titleNode);

            $authorNode = $dom->createElement("author");
            $authorNode->appendChild($dom->createTextNode($book['author']));
            $node->appendChild($authorNode);

            $isbnNode = $dom->createElement("isbn");
            $isbnNode->appendChild($dom->createTextNode($book['isbn']));
            $node->appendChild($isbnNode);

            $genreNode = $dom->createElement("genre");
            $genreNode->appendChild($dom->createTextNode($book['genre']));
            $node->appendChild($genreNode);
            
            $root->appendChild($node);
        }

        ob_clean(); // Clears any whitespace/output buffer before sending XML
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment; filename="librosys_books.xml"');
        echo $dom->saveXML();
        exit();
    }

    /**
     * GUIDELINE 5B: XML Import using DOMDocument
     */
    public function importBooksFromXML($filePath) {
        $dom = new DOMDocument();
        if (!$dom->load($filePath)) return false;

        $books = $dom->getElementsByTagName("book");
        $successCount = 0;

        foreach ($books as $book) {
            $title = $book->getElementsByTagName("title")->item(0)->nodeValue;
            $author = $book->getElementsByTagName("author")->item(0)->nodeValue;
            $isbn = $book->getElementsByTagName("isbn")->item(0)->nodeValue;
            $genre = $book->getElementsByTagName("genre")->item(0)->nodeValue;

            // Check if ISBN exists
            $check = $this->pdo->prepare("SELECT COUNT(*) FROM books WHERE isbn = ?");
            $check->execute([$isbn]);

            if ($check->fetchColumn() == 0) {
                $stmt = $this->pdo->prepare("INSERT INTO books (title, author, isbn, genre, cover_path) VALUES (?, ?, ?, ?, 'images/book-placeholder.jpg')");
                $stmt->execute([$title, $author, $isbn, $genre]);
                $successCount++;
            }
        }
        return $successCount;
    }
}
?>