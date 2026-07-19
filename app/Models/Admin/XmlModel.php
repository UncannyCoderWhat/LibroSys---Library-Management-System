<?php
// app/Models/Admin/XmlModel.php
// XML export/import business logic using DOMDocument

class XmlModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * GUIDELINE 5A: Export Books to XML
     */
    public function exportBooksToXML(): void
    {
        $stmt = $this->pdo->query("SELECT * FROM books WHERE is_deleted = 0");
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;

        $root = $dom->createElement("books");
        $dom->appendChild($root);

        foreach ($books as $book) {
            $node = $dom->createElement("book");

            $node->appendChild($dom->createElement("id", htmlspecialchars($book['id'] ?? '')));
            $node->appendChild($dom->createElement("title", htmlspecialchars($book['title'] ?? '')));
            $node->appendChild($dom->createElement("author", htmlspecialchars($book['author'] ?? '')));
            $node->appendChild($dom->createElement("isbn", htmlspecialchars($book['isbn'] ?? '')));
            $node->appendChild($dom->createElement("genre", htmlspecialchars($book['genre'] ?? '')));
            $node->appendChild($dom->createElement("publisher", htmlspecialchars($book['publisher'] ?? '')));
            $node->appendChild($dom->createElement("publication_year", htmlspecialchars($book['publication_year'] ?? '')));
            $node->appendChild($dom->createElement("language", htmlspecialchars($book['language'] ?? 'English')));
            $node->appendChild($dom->createElement("shelf_location", htmlspecialchars($book['shelf_location'] ?? '')));
            $node->appendChild($dom->createElement("copies", htmlspecialchars($book['copies'] ?? '1')));
            $node->appendChild($dom->createElement("description", htmlspecialchars($book['description'] ?? '')));
            $node->appendChild($dom->createElement("is_exclusive", htmlspecialchars($book['is_exclusive'] ?? '0')));
            $node->appendChild($dom->createElement("status", htmlspecialchars($book['status'] ?? 'available')));
            $node->appendChild($dom->createElement("cover_path", htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png')));
            $node->appendChild($dom->createElement("category_id", htmlspecialchars($book['category_id'] ?? '')));
            $node->appendChild($dom->createElement("author_id", htmlspecialchars($book['author_id'] ?? '')));
            $node->appendChild($dom->createElement("publisher_id", htmlspecialchars($book['publisher_id'] ?? '')));

            $root->appendChild($node);
        }

        ob_clean();
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment; filename="librosys_books.xml"');
        echo $dom->saveXML();
        exit();
    }

    /**
     * GUIDELINE 5B: Import Books from XML
     */
    public function importBooksFromXML(string $filePath): int
    {
        $dom = new DOMDocument();
        if (!$dom->load($filePath)) return 0;

        $books = $dom->getElementsByTagName("book");
        $successCount = 0;

        foreach ($books as $book) {
            $title = $book->getElementsByTagName("title")->item(0)->nodeValue;
            $author = $book->getElementsByTagName("author")->item(0)->nodeValue;
            $isbn = $book->getElementsByTagName("isbn")->item(0)->nodeValue;
            $genre = $book->getElementsByTagName("genre")->item(0)->nodeValue;
            $publisher = $book->getElementsByTagName("publisher")->item(0) ? $book->getElementsByTagName("publisher")->item(0)->nodeValue : '';
            $publication_year = $book->getElementsByTagName("publication_year")->item(0) ? $book->getElementsByTagName("publication_year")->item(0)->nodeValue : null;
            $language = $book->getElementsByTagName("language")->item(0) ? $book->getElementsByTagName("language")->item(0)->nodeValue : 'English';
            $shelf_location = $book->getElementsByTagName("shelf_location")->item(0) ? $book->getElementsByTagName("shelf_location")->item(0)->nodeValue : '';
            $copies = $book->getElementsByTagName("copies")->item(0) ? (int)$book->getElementsByTagName("copies")->item(0)->nodeValue : 1;
            $description = $book->getElementsByTagName("description")->item(0) ? $book->getElementsByTagName("description")->item(0)->nodeValue : '';
            $is_exclusive = $book->getElementsByTagName("is_exclusive")->item(0) ? (int)$book->getElementsByTagName("is_exclusive")->item(0)->nodeValue : 0;
            $status = $book->getElementsByTagName("status")->item(0) ? $book->getElementsByTagName("status")->item(0)->nodeValue : 'available';
            $cover_path = $book->getElementsByTagName("cover_path")->item(0) ? $book->getElementsByTagName("cover_path")->item(0)->nodeValue : 'images/book-icon.png';
            $category_id = $book->getElementsByTagName("category_id")->item(0) ? (int)$book->getElementsByTagName("category_id")->item(0)->nodeValue : null;
            $author_id = $book->getElementsByTagName("author_id")->item(0) ? (int)$book->getElementsByTagName("author_id")->item(0)->nodeValue : null;
            $publisher_id = $book->getElementsByTagName("publisher_id")->item(0) ? (int)$book->getElementsByTagName("publisher_id")->item(0)->nodeValue : null;

            $check = $this->pdo->prepare("SELECT COUNT(*) FROM books WHERE isbn = ?");
            $check->execute([$isbn]);

            if ($check->fetchColumn() == 0) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO books 
                        (title, author, isbn, genre, publisher, publication_year, language, 
                         shelf_location, copies, description, is_exclusive, status, cover_path,
                         category_id, author_id, publisher_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $title, $author, $isbn, $genre, $publisher, $publication_year, $language,
                    $shelf_location, $copies, $description, $is_exclusive, $status, $cover_path,
                    $category_id, $author_id, $publisher_id
                ]);

                $bookId = $this->pdo->lastInsertId();

                // Auto-create book copies based on the 'copies' count
                if ($copies > 0) {
                    for ($i = 1; $i <= $copies; $i++) {
                        $label = "Copy #{$i}";
                        $this->pdo->prepare("INSERT INTO book_copies (book_id, copy_label, status) VALUES (?, ?, 'available')")
                                  ->execute([$bookId, $label]);
                    }
                }

                $successCount++;
            }
        }
        return $successCount;
    }

    /**
     * Export Users and their Borrowing History to XML
     */
    public function exportUsersToXML(): void
    {
        $stmt = $this->pdo->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;

        $root = $dom->createElement("library_members");
        $dom->appendChild($root);

        foreach ($users as $user) {
            $userNode = $dom->createElement("user");

            $userNode->appendChild($dom->createElement("user_id", $user['user_id']));
            $userNode->appendChild($dom->createElement("name", htmlspecialchars($user['name'])));
            $userNode->appendChild($dom->createElement("email", htmlspecialchars($user['email'])));
            $userNode->appendChild($dom->createElement("password", $user['password']));
            $userNode->appendChild($dom->createElement("credit_score", $user['credit_score']));

            $borrowsNode = $dom->createElement("borrows");
            $stmtB = $this->pdo->prepare("SELECT * FROM borrows WHERE user_id = ?");
            $stmtB->execute([$user['id']]);
            $borrows = $stmtB->fetchAll(PDO::FETCH_ASSOC);

            foreach ($borrows as $borrow) {
                $bNode = $dom->createElement("borrow");
                $bNode->appendChild($dom->createElement("book_id", $borrow['book_id']));
                $bNode->appendChild($dom->createElement("borrow_date", $borrow['borrow_date']));
                $bNode->appendChild($dom->createElement("due_date", $borrow['due_date']));
                $bNode->appendChild($dom->createElement("return_date", $borrow['return_date']));
                $bNode->appendChild($dom->createElement("status", $borrow['status']));
                $bNode->appendChild($dom->createElement("fine_amount", $borrow['fine_amount']));
                $bNode->appendChild($dom->createElement("is_fine_paid", $borrow['is_fine_paid']));
                $borrowsNode->appendChild($bNode);
            }

            $userNode->appendChild($borrowsNode);
            $root->appendChild($userNode);
        }

        ob_clean();
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment; filename="librosys_users_backup.xml"');
        echo $dom->saveXML();
        exit();
    }

    /**
     * Import Users from XML
     */
    public function importUsersFromXML(string $filePath): int
    {
        $dom = new DOMDocument();
        if (!$dom->load($filePath)) return 0;

        $users = $dom->getElementsByTagName("user");
        $successCount = 0;

        foreach ($users as $user) {
            $user_id = $user->getElementsByTagName("user_id")->item(0)->nodeValue;
            $name = $user->getElementsByTagName("name")->item(0)->nodeValue;
            $email = $user->getElementsByTagName("email")->item(0)->nodeValue;
            $password = $user->getElementsByTagName("password")->item(0)->nodeValue;
            $credit_score = $user->getElementsByTagName("credit_score")->item(0)->nodeValue;

            $check = $this->pdo->prepare("SELECT id FROM users WHERE user_id = ? OR email = ?");
            $check->execute([$user_id, $email]);
            $existingUser = $check->fetch();

            if (!$existingUser) {
                $stmt = $this->pdo->prepare("INSERT INTO users (user_id, name, email, password, credit_score) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $email, $password, $credit_score]);
                $newDbId = $this->pdo->lastInsertId();
                $successCount++;

                $borrows = $user->getElementsByTagName("borrow");
                foreach ($borrows as $borrow) {
                    $book_id = $borrow->getElementsByTagName("book_id")->item(0)->nodeValue;
                    $bookCheck = $this->pdo->prepare("SELECT COUNT(*) FROM books WHERE id = ?");
                    $bookCheck->execute([$book_id]);

                    if ($bookCheck->fetchColumn() > 0) {
                        $insB = $this->pdo->prepare("INSERT INTO borrows (book_id, user_id, borrow_date, due_date, return_date, status, fine_amount, is_fine_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $insB->execute([
                            $book_id, $newDbId,
                            $borrow->getElementsByTagName("borrow_date")->item(0)->nodeValue,
                            $borrow->getElementsByTagName("due_date")->item(0)->nodeValue,
                            $borrow->getElementsByTagName("return_date")->item(0)->nodeValue,
                            $borrow->getElementsByTagName("status")->item(0)->nodeValue,
                            $borrow->getElementsByTagName("fine_amount")->item(0)->nodeValue,
                            $borrow->getElementsByTagName("is_fine_paid")->item(0)->nodeValue
                        ]);
                    }
                }
            }
        }
        return $successCount;
    }

    /**
     * GUIDELINE 5A: Export Entire System (Books, Users, and Borrows) to a single XML
     */
    public function exportFullSystemToXML(): void
    {
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;

        $root = $dom->createElement("librosys_backup");
        $dom->appendChild($root);

        $booksNode = $dom->createElement("books");
        $root->appendChild($booksNode);
        $stmtBooks = $this->pdo->query("SELECT * FROM books WHERE is_deleted = 0");
        while ($book = $stmtBooks->fetch(PDO::FETCH_ASSOC)) {
            $bNode = $dom->createElement("book");
            $bNode->appendChild($dom->createElement("id", htmlspecialchars($book['id'] ?? '')));
            $bNode->appendChild($dom->createElement("title", htmlspecialchars($book['title'] ?? '')));
            $bNode->appendChild($dom->createElement("author", htmlspecialchars($book['author'] ?? '')));
            $bNode->appendChild($dom->createElement("isbn", htmlspecialchars($book['isbn'] ?? '')));
            $bNode->appendChild($dom->createElement("genre", htmlspecialchars($book['genre'] ?? '')));
            $bNode->appendChild($dom->createElement("publisher", htmlspecialchars($book['publisher'] ?? '')));
            $bNode->appendChild($dom->createElement("publication_year", htmlspecialchars($book['publication_year'] ?? '')));
            $bNode->appendChild($dom->createElement("language", htmlspecialchars($book['language'] ?? 'English')));
            $bNode->appendChild($dom->createElement("shelf_location", htmlspecialchars($book['shelf_location'] ?? '')));
            $bNode->appendChild($dom->createElement("copies", htmlspecialchars($book['copies'] ?? '1')));
            $bNode->appendChild($dom->createElement("description", htmlspecialchars($book['description'] ?? '')));
            $bNode->appendChild($dom->createElement("is_exclusive", htmlspecialchars($book['is_exclusive'] ?? '0')));
            $bNode->appendChild($dom->createElement("status", htmlspecialchars($book['status'] ?? 'available')));
            $bNode->appendChild($dom->createElement("cover_path", htmlspecialchars($book['cover_path'] ?? 'images/book-icon.png')));
            $bNode->appendChild($dom->createElement("category_id", htmlspecialchars($book['category_id'] ?? '')));
            $bNode->appendChild($dom->createElement("author_id", htmlspecialchars($book['author_id'] ?? '')));
            $bNode->appendChild($dom->createElement("publisher_id", htmlspecialchars($book['publisher_id'] ?? '')));
            $booksNode->appendChild($bNode);
        }

        $usersNode = $dom->createElement("users");
        $root->appendChild($usersNode);
        $stmtUsers = $this->pdo->query("SELECT * FROM users");
        while ($user = $stmtUsers->fetch(PDO::FETCH_ASSOC)) {
            $uNode = $dom->createElement("user");
            $uNode->appendChild($dom->createElement("user_id", $user['user_id']));

            $nameNode = $dom->createElement("name");
            $nameNode->appendChild($dom->createTextNode($user['name']));
            $uNode->appendChild($nameNode);

            $uNode->appendChild($dom->createElement("email", htmlspecialchars($user['email'])));
            $uNode->appendChild($dom->createElement("credit_score", $user['credit_score']));

            $borrowsNode = $dom->createElement("borrows");
            $uNode->appendChild($borrowsNode);
            $stmtB = $this->pdo->prepare("SELECT * FROM borrows WHERE user_id = ?");
            $stmtB->execute([$user['id']]);
            while ($borrow = $stmtB->fetch(PDO::FETCH_ASSOC)) {
                $brNode = $dom->createElement("borrow");
                $brNode->appendChild($dom->createElement("book_id", $borrow['book_id']));
                $brNode->appendChild($dom->createElement("status", $borrow['status']));
                $brNode->appendChild($dom->createElement("borrow_date", $borrow['borrow_date']));
                $brNode->appendChild($dom->createElement("due_date", $borrow['due_date']));
                $brNode->appendChild($dom->createElement("fine_amount", $borrow['fine_amount']));
                $borrowsNode->appendChild($brNode);
            }
            $usersNode->appendChild($uNode);
        }

        ob_clean();
        header('Content-Type: text/xml');
        header('Content-Disposition: attachment; filename="LibroSys_FULL_BACKUP_' . date('Y-m-d') . '.xml"');
        echo $dom->saveXML();
        exit();
    }
}
