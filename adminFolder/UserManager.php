<?php
class UserManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllUsers() {
        $stmt = $this->pdo->query("SELECT u.*, (SELECT COUNT(*) FROM borrows WHERE user_id = u.id AND status = 'borrowed') as active_borrows FROM users u");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteUserWithHistory($userId) {
        // Logic moved from users.php to follow Encapsulation principles
        $delNotifs = $this->pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        $delNotifs->execute([$userId]);
        $delHistory = $this->pdo->prepare("DELETE FROM borrows WHERE user_id = ?");
        $delHistory->execute([$userId]);
        $delUser = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $delUser->execute([$userId]);
    }

    /**
     * Export Users and their Borrowing History to XML
     */
    public function exportUsersToXML() {
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

            // Nest Borrows for this user
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
     * Import Users and their History from XML
     */
    public function importUsersFromXML($filePath) {
        $dom = new DOMDocument();
        if (!$dom->load($filePath)) return false;

        $users = $dom->getElementsByTagName("user");
        $successCount = 0;

        foreach ($users as $user) {
            $user_id = $user->getElementsByTagName("user_id")->item(0)->nodeValue;
            $name = $user->getElementsByTagName("name")->item(0)->nodeValue;
            $email = $user->getElementsByTagName("email")->item(0)->nodeValue;
            $password = $user->getElementsByTagName("password")->item(0)->nodeValue;
            $credit_score = $user->getElementsByTagName("credit_score")->item(0)->nodeValue;

            // Check if user already exists
            $check = $this->pdo->prepare("SELECT id FROM users WHERE user_id = ? OR email = ?");
            $check->execute([$user_id, $email]);
            $existingUser = $check->fetch();

            if (!$existingUser) {
                $stmt = $this->pdo->prepare("INSERT INTO users (user_id, name, email, password, credit_score) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$user_id, $name, $email, $password, $credit_score]);
                $newDbId = $this->pdo->lastInsertId();
                $successCount++;

                // Import their borrow history
                $borrows = $user->getElementsByTagName("borrow");
                foreach ($borrows as $borrow) {
                    $book_id = $borrow->getElementsByTagName("book_id")->item(0)->nodeValue;
                    
                    // Validate that the book still exists in our current system
                    $bookCheck = $this->pdo->prepare("SELECT COUNT(*) FROM books WHERE id = ?");
                    $bookCheck->execute([$book_id]);
                    
                    if ($bookCheck->fetchColumn() > 0) {
                        $insB = $this->pdo->prepare("INSERT INTO borrows 
                            (book_id, user_id, borrow_date, due_date, return_date, status, fine_amount, is_fine_paid) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $insB->execute([
                            $book_id,
                            $newDbId,
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
    public function exportFullSystemToXML() {
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->formatOutput = true;

        $root = $dom->createElement("librosys_backup");
        $dom->appendChild($root);

        // Section 1: Books
        $booksNode = $dom->createElement("books");
        $root->appendChild($booksNode);
        $stmtBooks = $this->pdo->query("SELECT * FROM books WHERE is_deleted = 0");
        while ($book = $stmtBooks->fetch(PDO::FETCH_ASSOC)) {
            $bNode = $dom->createElement("book");
            $bNode->appendChild($dom->createElement("id", $book['id']));
            
            $titleNode = $dom->createElement("title");
            $titleNode->appendChild($dom->createTextNode($book['title']));
            $bNode->appendChild($titleNode);

            $authorNode = $dom->createElement("author");
            $authorNode->appendChild($dom->createTextNode($book['author']));
            $bNode->appendChild($authorNode);

            $bNode->appendChild($dom->createElement("isbn", $book['isbn']));
            $bNode->appendChild($dom->createElement("genre", $book['genre']));
            $bNode->appendChild($dom->createElement("is_exclusive", $book['is_exclusive']));
            $booksNode->appendChild($bNode);
        }

        // Section 2: Users and their nested Borrows
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
?>