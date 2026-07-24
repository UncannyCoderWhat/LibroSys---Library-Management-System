<?php
// app/Models/Admin/BookModel.php
// Enhanced book management business logic

class BookModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ==================== BOOKS CRUD ====================

    public function getAllBooks(): array
    {
        $stmt = $this->pdo->query("
            SELECT b.*, 
                   c.name AS category_name, 
                   a.name AS author_name,
                   p.name AS publisher_name
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN publishers p ON b.publisher_id = p.id
            WHERE b.is_deleted = 0
            ORDER BY b.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBookById(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.*, 
                   c.name AS category_name, 
                   a.name AS author_name,
                   p.name AS publisher_name
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            LEFT JOIN authors a ON b.author_id = a.id
            LEFT JOIN publishers p ON b.publisher_id = p.id
            WHERE b.id = ? AND b.is_deleted = 0
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getAllBookTypes(): array
    {
        // Return distinct book_type values that exist, plus common defaults
        $stmt = $this->pdo->query("SELECT DISTINCT book_type FROM books WHERE book_type IS NOT NULL AND book_type != '' ORDER BY book_type ASC");
        $existing = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $defaults = ['Novel', 'Manga', 'Light Novel', 'Comic', 'Graphic Novel', 'Textbook', 'Reference', 'Other'];
        // Merge existing with defaults, keep order
        $all = $defaults;
        foreach ($existing as $e) {
            if (!in_array($e, $all, true)) {
                $all[] = $e;
            }
        }
        return $all;
    }

    public function addBook(array $post, array $files): array
    {
        $title           = $post['title'] ?? '';
        $author          = $post['author'] ?? '';
        $isbn            = $post['isbn'] ?? '';
        $genre           = $post['genre'] ?? '';
        $publisher       = $post['publisher'] ?? '';
        $publication_year = !empty($post['publication_year']) ? (int)$post['publication_year'] : null;
        $language        = $post['language'] ?? 'English';
        $shelf_location  = $post['shelf_location'] ?? '';
        $copies          = max(1, (int)($post['copies'] ?? 1));
        $description     = $post['description'] ?? '';
        $is_exclusive    = isset($post['is_exclusive']) ? 1 : 0;
        $status          = $post['status'] ?? 'available';
        $category_id     = !empty($post['category_id']) ? (int)$post['category_id'] : null;
        $author_id       = !empty($post['author_id']) ? (int)$post['author_id'] : null;
        $publisher_id    = !empty($post['publisher_id']) ? (int)$post['publisher_id'] : null;
        $cover_path      = 'images/book-icon.png';

        // Auto-create author if not selected but name is provided
        if (empty($author_id) && !empty($author)) {
            $stmt = $this->pdo->prepare("SELECT id FROM authors WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$author]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $author_id = (int)$existing['id'];
            } else {
                $addResult = $this->addAuthor($author, null, null);
                if ($addResult['success']) {
                    $author_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Auto-create publisher if not selected but name is provided
        if (empty($publisher_id) && !empty($publisher)) {
            $stmt = $this->pdo->prepare("SELECT id FROM publishers WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$publisher]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $publisher_id = (int)$existing['id'];
            } else {
                $addResult = $this->addPublisher($publisher, null, null);
                if ($addResult['success']) {
                    $publisher_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Handle cover image upload
        if (isset($files['cover_image']) && ($files['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($files['cover_image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($image_file_type, $allowed_extensions, true)) {
                if (move_uploaded_file($files['cover_image']['tmp_name'], $target_file)) {
                    $cover_path = 'uploads/' . $file_name;
                }
            }
        }

        $book_type = $post['book_type'] ?? '';

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO books 
                    (title, author, isbn, genre, book_type, publisher, publication_year, language, 
                     shelf_location, copies, description, is_exclusive, status, 
                     category_id, author_id, publisher_id, cover_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $author, $isbn, $genre, $book_type, $publisher, $publication_year, $language,
                $shelf_location, $copies, $description, $is_exclusive, $status,
                $category_id, $author_id, $publisher_id, $cover_path
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

            $this->syncBookAvailability($bookId);

            return ['success' => true, 'message' => 'Book added successfully.', 'book_id' => $bookId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding book: ' . $e->getMessage()];
        }
    }

    public function updateBook(array $post, array $files): array
    {
        $id               = (int)($post['book_id'] ?? 0);
        $title            = $post['title'] ?? '';
        $author           = $post['author'] ?? '';
        $isbn             = $post['isbn'] ?? '';
        $genre            = $post['genre'] ?? '';
        $book_type        = $post['book_type'] ?? '';
        $publisher        = $post['publisher'] ?? '';
        $publication_year = !empty($post['publication_year']) ? (int)$post['publication_year'] : null;
        $language         = $post['language'] ?? 'English';
        $shelf_location   = $post['shelf_location'] ?? '';
        $copies           = max(1, (int)($post['copies'] ?? 1));
        $description      = $post['description'] ?? '';
        $is_exclusive     = isset($post['is_exclusive']) ? 1 : 0;
        $status           = $post['status'] ?? 'available';
        $category_id      = !empty($post['category_id']) ? (int)$post['category_id'] : null;
        $author_id        = !empty($post['author_id']) ? (int)$post['author_id'] : null;
        $publisher_id     = !empty($post['publisher_id']) ? (int)$post['publisher_id'] : null;
        $cover_path       = $post['current_cover'] ?? 'images/book-icon.png';

        // Auto-create author if not selected but name is provided
        if (empty($author_id) && !empty($author)) {
            $stmt = $this->pdo->prepare("SELECT id FROM authors WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$author]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $author_id = (int)$existing['id'];
            } else {
                $addResult = $this->addAuthor($author, null, null);
                if ($addResult['success']) {
                    $author_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Auto-create publisher if not selected but name is provided
        if (empty($publisher_id) && !empty($publisher)) {
            $stmt = $this->pdo->prepare("SELECT id FROM publishers WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$publisher]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $publisher_id = (int)$existing['id'];
            } else {
                $addResult = $this->addPublisher($publisher, null, null);
                if ($addResult['success']) {
                    $publisher_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Handle cover image upload
        if (isset($files['cover_image']) && ($files['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/';
            $file_name = time() . '_' . basename($files['cover_image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (in_array($image_file_type, $allowed_extensions, true)) {
                if (move_uploaded_file($files['cover_image']['tmp_name'], $target_file)) {
                    $cover_path = 'uploads/' . $file_name;
                }
            }
        }

        try {
            $stmt = $this->pdo->prepare("
                UPDATE books SET 
                    title = ?, author = ?, isbn = ?, genre = ?, book_type = ?, publisher = ?, 
                    publication_year = ?, language = ?, shelf_location = ?, 
                    copies = ?, description = ?, is_exclusive = ?, status = ?,
                    category_id = ?, author_id = ?, publisher_id = ?, cover_path = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $title, $author, $isbn, $genre, $book_type, $publisher, $publication_year, $language,
                $shelf_location, $copies, $description, $is_exclusive, $status,
                $category_id, $author_id, $publisher_id, $cover_path, $id
            ]);

            // Sync copies: ensure at least $copies copies exist
            $existingCopies = $this->getBookCopies($id);
            $currentCount = count($existingCopies);
            if ($copies > $currentCount) {
                for ($i = $currentCount + 1; $i <= $copies; $i++) {
                    $label = "Copy #{$i}";
                    $this->pdo->prepare("INSERT INTO book_copies (book_id, copy_label, status) VALUES (?, ?, 'available')")
                              ->execute([$id, $label]);
                }
            }

            $this->syncBookAvailability($id);

            return ['success' => true, 'message' => 'Book updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating book: ' . $e->getMessage()];
        }
    }

    public function deleteBook(int $bookId): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE books SET is_deleted = 1 WHERE id = ?");
            $stmt->execute([$bookId]);
            return ['success' => true, 'message' => 'Book deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting book: ' . $e->getMessage()];
        }
    }

    public function archiveBook(int $bookId): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'archived' WHERE id = ?");
            $stmt->execute([$bookId]);
            return ['success' => true, 'message' => 'Book archived successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error archiving book: ' . $e->getMessage()];
        }
    }

    public function restoreBook(int $bookId): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'available' WHERE id = ?");
            $stmt->execute([$bookId]);
            return ['success' => true, 'message' => 'Book restored successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error restoring book: ' . $e->getMessage()];
        }
    }

    public function markUnavailable(int $bookId): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE books SET status = 'unavailable' WHERE id = ?");
            $stmt->execute([$bookId]);
            return ['success' => true, 'message' => 'Book marked as unavailable successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating book: ' . $e->getMessage()];
        }
    }

    public function syncBookAvailability(int $bookId): void
    {
        $book = $this->getBookById($bookId);
        if (!$book || ($book['status'] ?? '') === 'archived') {
            return;
        }

        $totalCopies = (int)($book['copies'] ?? 1);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM borrows 
            WHERE book_id = ? AND status IN ('borrowed', 'reserved')
        ");
        $stmt->execute([$bookId]);
        $activeBorrows = (int)$stmt->fetchColumn();

        if ($activeBorrows >= $totalCopies) {
            if (($book['status'] ?? '') !== 'unavailable') {
                $this->pdo->prepare("UPDATE books SET status = 'unavailable' WHERE id = ?")->execute([$bookId]);
            }
        } elseif ($activeBorrows < $totalCopies && ($book['status'] ?? '') === 'unavailable') {
            $this->pdo->prepare("UPDATE books SET status = 'available' WHERE id = ?")->execute([$bookId]);
        }
    }

    // ==================== CATEGORIES CRUD ====================

    public function getAllCategories(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCategory(string $name, ?string $description): array
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            return ['success' => true, 'message' => 'Category added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding category: ' . $e->getMessage()];
        }
    }

    public function updateCategory(int $id, string $name, ?string $description): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            return ['success' => true, 'message' => 'Category updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating category: ' . $e->getMessage()];
        }
    }

    public function deleteCategory(int $id): array
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Category deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting category: ' . $e->getMessage()];
        }
    }

    // ==================== AUTHORS CRUD ====================

    public function getAllAuthors(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM authors ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAuthor(string $name, ?string $bio, ?int $birthYear): array
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO authors (name, bio, birth_year) VALUES (?, ?, ?)");
            $stmt->execute([$name, $bio, $birthYear]);
            return ['success' => true, 'message' => 'Author added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding author: ' . $e->getMessage()];
        }
    }

    public function updateAuthor(int $id, string $name, ?string $bio, ?int $birthYear): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE authors SET name = ?, bio = ?, birth_year = ? WHERE id = ?");
            $stmt->execute([$name, $bio, $birthYear, $id]);
            return ['success' => true, 'message' => 'Author updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating author: ' . $e->getMessage()];
        }
    }

    public function deleteAuthor(int $id): array
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM authors WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Author deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting author: ' . $e->getMessage()];
        }
    }

    // ==================== PUBLISHERS CRUD ====================

    public function getAllPublishers(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM publishers ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addPublisher(string $name, ?string $address, ?string $website): array
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO publishers (name, address, website) VALUES (?, ?, ?)");
            $stmt->execute([$name, $address, $website]);
            return ['success' => true, 'message' => 'Publisher added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding publisher: ' . $e->getMessage()];
        }
    }

    public function updatePublisher(int $id, string $name, ?string $address, ?string $website): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE publishers SET name = ?, address = ?, website = ? WHERE id = ?");
            $stmt->execute([$name, $address, $website, $id]);
            return ['success' => true, 'message' => 'Publisher updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating publisher: ' . $e->getMessage()];
        }
    }

    public function getBooksByAuthor(int $authorId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.title, b.author, b.isbn, b.genre, b.publication_year, b.cover_path
            FROM books b
            WHERE b.author_id = ? AND b.is_deleted = 0
            ORDER BY b.title ASC
        ");
        $stmt->execute([$authorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBooksByPublisher(int $publisherId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT b.id, b.title, b.author, b.isbn, b.genre, b.publication_year, b.cover_path
            FROM books b
            WHERE b.publisher_id = ? AND b.is_deleted = 0
            ORDER BY b.title ASC
        ");
        $stmt->execute([$publisherId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deletePublisher(int $id): array
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM publishers WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Publisher deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting publisher: ' . $e->getMessage()];
        }
    }

    // ==================== EBOOK MANAGEMENT ====================

    public function getEBooks(int $bookId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM ebooks WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function uploadEBook(int $bookId, array $file): array
    {
        $allowed_types = ['pdf', 'epub'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types, true)) {
            return ['success' => false, 'message' => 'Only PDF and EPUB files are allowed.'];
        }

        $upload_dir = __DIR__ . '/../../../uploads/ebooks/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($file['name']);
        $target_file = $upload_dir . $file_name;
        $file_size = $file['size'];

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO ebooks (book_id, file_path, file_type, file_size) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$bookId, 'uploads/ebooks/' . $file_name, $file_ext, $file_size]);
                return ['success' => true, 'message' => 'eBook uploaded successfully.', 'file_path' => 'uploads/ebooks/' . $file_name];
            } catch (PDOException $e) {
                return ['success' => false, 'message' => 'Error saving eBook: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Failed to upload eBook file.'];
    }

    public function deleteEBook(int $ebookId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT file_path FROM ebooks WHERE id = ?");
            $stmt->execute([$ebookId]);
            $ebook = $stmt->fetch();

            if ($ebook) {
                $file_path = __DIR__ . '/../../../' . $ebook['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
                $this->pdo->prepare("DELETE FROM ebooks WHERE id = ?")->execute([$ebookId]);
                return ['success' => true, 'message' => 'eBook deleted successfully.'];
            }
            return ['success' => false, 'message' => 'eBook not found.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting eBook: ' . $e->getMessage()];
        }
    }

    public function updateEBookSettings(int $ebookId, array $settings): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE ebooks SET reader_settings = ?, download_allowed = ?, online_only = ? WHERE id = ?");
            $stmt->execute([
                json_encode($settings),
                $settings['download_allowed'] ?? 1,
                $settings['online_only'] ?? 0,
                $ebookId
            ]);
            return ['success' => true, 'message' => 'eBook settings updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating eBook settings: ' . $e->getMessage()];
        }
    }

    // ==================== BOOK COPIES MANAGEMENT ====================

    public function getBookCopies(int $bookId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM book_copies WHERE book_id = ? ORDER BY id ASC");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addCopy(int $bookId, ?string $label): array
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO book_copies (book_id, copy_label, status) VALUES (?, ?, 'available')");
            $stmt->execute([$bookId, $label ?? 'Copy #' . (count($this->getBookCopies($bookId)) + 1)]);
            // Also increment the copies count in the books table
            $this->pdo->prepare("UPDATE books SET copies = copies + 1 WHERE id = ?")->execute([$bookId]);
            $this->syncBookAvailability($bookId);
            return ['success' => true, 'message' => 'Copy added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding copy: ' . $e->getMessage()];
        }
    }

    public function updateCopyStatus(int $copyId, string $status): array
    {
        try {
            $stmt = $this->pdo->prepare("UPDATE book_copies SET status = ? WHERE id = ?");
            $stmt->execute([$status, $copyId]);
            return ['success' => true, 'message' => 'Copy status updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating copy: ' . $e->getMessage()];
        }
    }

    public function deleteCopy(int $copyId): array
    {
        try {
            // Get the book_id before deleting the copy
            $stmt = $this->pdo->prepare("SELECT book_id FROM book_copies WHERE id = ?");
            $stmt->execute([$copyId]);
            $copy = $stmt->fetch(PDO::FETCH_ASSOC);
            $bookId = $copy['book_id'] ?? 0;

            $this->pdo->prepare("DELETE FROM book_copies WHERE id = ?")->execute([$copyId]);
            
            // Decrement the copies count in the books table
            if ($bookId > 0) {
                $this->pdo->prepare("UPDATE books SET copies = GREATEST(1, copies - 1) WHERE id = ?")->execute([$bookId]);
                $this->syncBookAvailability($bookId);
            }
            
            return ['success' => true, 'message' => 'Copy removed successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error removing copy: ' . $e->getMessage()];
        }
    }

    // ==================== API BOOK IMPORT ====================

    public function importFromApi(array $apiData): array
    {
        // Expects: title, author, isbn, publisher, publishedDate, description, 
        //          categories (array), pageCount, language, thumbnail, etc.
        $title = $apiData['title'] ?? '';
        $author = $apiData['author'] ?? '';
        $isbn = $apiData['isbn'] ?? '';
        $publisher = $apiData['publisher'] ?? '';
        $publication_year = !empty($apiData['publishedDate']) ? (int)substr($apiData['publishedDate'], 0, 4) : null;
        $description = $apiData['description'] ?? '';
        $language = $apiData['language'] ?? 'English';
        $genre = is_array($apiData['categories'] ?? null) ? implode(', ', $apiData['categories']) : ($apiData['categories'] ?? '');
        $book_type = $apiData['book_type'] ?? '';
        $cover_path = 'images/book-icon.png';
        $author_id = null;
        $publisher_id = null;

        // Auto-create author (no duplicate)
        if (!empty($author)) {
            $stmt = $this->pdo->prepare("SELECT id FROM authors WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$author]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $author_id = (int)$existing['id'];
            } else {
                $addResult = $this->addAuthor($author, null, null);
                if ($addResult['success']) {
                    $author_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Auto-create publisher (no duplicate)
        if (!empty($publisher)) {
            $stmt = $this->pdo->prepare("SELECT id FROM publishers WHERE LOWER(name) = LOWER(?)");
            $stmt->execute([$publisher]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $publisher_id = (int)$existing['id'];
            } else {
                $addResult = $this->addPublisher($publisher, null, null);
                if ($addResult['success']) {
                    $publisher_id = (int)$this->pdo->lastInsertId();
                }
            }
        }

        // Download cover image from URL if provided
        if (!empty($apiData['thumbnail'])) {
            $upload_dir = __DIR__ . '/../../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $image_data = @file_get_contents($apiData['thumbnail']);
            if ($image_data !== false) {
                $ext = 'jpg';
                $file_name = time() . '_api_cover.' . $ext;
                $target_file = $upload_dir . $file_name;
                if (file_put_contents($target_file, $image_data)) {
                    $cover_path = 'uploads/' . $file_name;
                }
            }
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO books 
                    (title, author, isbn, genre, publisher, publication_year, language, 
                     description, cover_path, copies, author_id, publisher_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $author, $isbn, $genre, $publisher, $publication_year, $language,
                $description, $cover_path, 1, $author_id, $publisher_id
            ]);

            $bookId = $this->pdo->lastInsertId();
            // Create one copy for the imported book
            $this->pdo->prepare("INSERT INTO book_copies (book_id, copy_label, status) VALUES (?, ?, 'available')")
                      ->execute([$bookId, 'Copy #1']);

            return ['success' => true, 'message' => 'Book imported successfully.', 'book_id' => $bookId];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error importing book: ' . $e->getMessage()];;
        }
    }

    // ==================== MANGA CHAPTER MANAGEMENT ====================

    public function getMangaChapters(int $bookId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM chapters 
            WHERE book_id = ? 
            ORDER BY CAST(SUBSTRING_INDEX(chapter_number, ' ', -1) AS UNSIGNED) ASC, chapter_number ASC
        ");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getChapter(int $chapterId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM chapters WHERE id = ?");
        $stmt->execute([$chapterId]);
        $chapter = $stmt->fetch(PDO::FETCH_ASSOC);
        return $chapter ?: null;
    }

    public function getChapterPages(int $chapterId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM chapter_pages 
            WHERE chapter_id = ? 
            ORDER BY page_number ASC
        ");
        $stmt->execute([$chapterId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMangaChapter(int $bookId, string $chapterNumber, ?string $title = null): array
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO chapters (book_id, chapter_number, title, status) 
                VALUES (?, ?, ?, 'draft')
            ");
            $stmt->execute([$bookId, $chapterNumber, $title]);
            return ['success' => true, 'chapter_id' => (int)$this->pdo->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error creating chapter: ' . $e->getMessage()];;
        }
    }

    public function updateMangaChapter(int $chapterId, string $chapterNumber, ?string $title = null): array
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE chapters SET chapter_number = ?, title = ? 
                WHERE id = ?
            ");
            $stmt->execute([$chapterNumber, $title, $chapterId]);
            return ['success' => true, 'message' => 'Chapter updated successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error updating chapter: ' . $e->getMessage()];;
        }
    }

    public function uploadChapterPage(int $chapterId, array $file): array
    {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext, true)) {
            return ['success' => false, 'message' => 'Only image files are allowed.'];
        }

        $upload_dir = __DIR__ . '/../../../uploads/manga/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . uniqid() . '_' . basename($file['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            try {
                $nextPage = (int)$this->pdo->query("SELECT COALESCE(MAX(page_number), 0) + 1 FROM chapter_pages WHERE chapter_id = " . (int)$chapterId)->fetchColumn();
                $stmt = $this->pdo->prepare("
                    INSERT INTO chapter_pages (chapter_id, page_number, image_path) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$chapterId, $nextPage, 'uploads/manga/' . $file_name]);
                $this->pdo->prepare("UPDATE chapters SET total_pages = total_pages + 1 WHERE id = ?")->execute([$chapterId]);
                return ['success' => true, 'message' => 'Page uploaded successfully.'];
            } catch (PDOException $e) {
                @unlink($target_file);
                return ['success' => false, 'message' => 'Error saving page: ' . $e->getMessage()];
            }
        }

        return ['success' => false, 'message' => 'Failed to upload file.'];
    }

    public function uploadChapterPagesFromZip(int $chapterId, array $file): array
    {
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext !== 'zip' && $file_ext !== 'cbz') {
            return ['success' => false, 'message' => 'Only ZIP/CBZ files are allowed for chapter upload.'];
        }

        $upload_dir = __DIR__ . '/../../../uploads/manga/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $tmp_file = $upload_dir . 'tmp_' . time() . '_' . uniqid() . '.zip';
        if (!move_uploaded_file($file['tmp_name'], $tmp_file)) {
            return ['success' => false, 'message' => 'Failed to upload archive.'];
        }

        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare("DELETE FROM chapter_pages WHERE chapter_id = ?")->execute([$chapterId]);

            $image_files = [];
            $normalizedTmp = str_replace('\\', '/', $tmp_file);

            if (class_exists('ZipArchive')) {

                $zip = new ZipArchive();
                if ($zip->open($tmp_file) === true) {

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $name = $zip->getNameIndex($i);
                        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                            $image_files[] = $name;
                        }
                    }
                    sort($image_files, SORT_NATURAL | SORT_FLAG_CASE);

                    foreach ($image_files as $page_number => $img_name) {
                        $image_data = $zip->getFromName($img_name);
                        $valid = $image_data !== false && $this->isValidImageData($image_data);

                        if ($valid) {
                            $ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
                            $file_name = time() . '_' . uniqid() . '_' . ($page_number + 1) . '.' . $ext;
                            $target_file = $upload_dir . $file_name;
                            file_put_contents($target_file, $image_data, LOCK_EX);
                            $this->pdo->prepare("
                                INSERT INTO chapter_pages (chapter_id, page_number, image_path) 
                                VALUES (?, ?, ?)
                            ")->execute([$chapterId, $page_number + 1, 'uploads/manga/' . $file_name]);
                        }
                    }
                    $zip->close();
                } else {

                }
            } else {
                $extracted = false;
                $extract_dir = $upload_dir . 'extract_' . time() . '_' . uniqid() . '/';
                if (mkdir($extract_dir, 0777, true)) {
                    if (class_exists('PharData')) {
                        try {
                            $phar = new PharData($normalizedTmp);
                            if ($phar->extractTo($extract_dir)) {
                                $extracted = true;
                            }
                        } catch (Exception $e) {
                            $extracted = false;
                        }
                    }

                    if (!$extracted && stripos(PHP_OS, 'WIN') === 0) {
                        $extract_dir = rtrim(str_replace('\\', '/', $extract_dir), '/');
                        $tarCmd = 'tar -xf ' . escapeshellarg($normalizedTmp) . ' -C ' . escapeshellarg($extract_dir);
                        exec($tarCmd . ' 2>&1', $output, $returnVar);
                        if ($returnVar === 0) {
                            $extracted = true;
                        }
                    }

                    if ($extracted && is_dir($extract_dir)) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extract_dir), RecursiveIteratorIterator::SELF_FIRST);
                        foreach ($files as $fileInfo) {
                            if (!$fileInfo->isFile()) {
                                continue;
                            }
                            $path = $fileInfo->getPathname();
                            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                                $image_files[] = $path;
                            }
                        }
                        sort($image_files, SORT_NATURAL | SORT_FLAG_CASE);
                        foreach ($image_files as $page_number => $source_path) {
                            $ext = strtolower(pathinfo($source_path, PATHINFO_EXTENSION));
                            $file_name = time() . '_' . uniqid() . '_' . ($page_number + 1) . '.' . $ext;
                            $target_file = $upload_dir . $file_name;
                            $src = @fopen($source_path, 'rb');
                            $dst = @fopen($target_file, 'wb');
                            if ($src && $dst) {
                                stream_copy_to_stream($src, $dst);
                                fclose($src);
                                fclose($dst);
                                if ($this->isValidImageFile($target_file)) {
                                    $this->pdo->prepare("
                                        INSERT INTO chapter_pages (chapter_id, page_number, image_path) 
                                        VALUES (?, ?, ?)
                                    ")->execute([$chapterId, $page_number + 1, 'uploads/manga/' . $file_name]);
                                } else {
                                    @unlink($target_file);
                                }
                            } else {
                                if ($src) fclose($src);
                                if ($dst) fclose($dst);
                            }
                        }
                        $this->deleteDirectory($extract_dir);
                    }
                }
            }

            @unlink($tmp_file);

            $total_pages = (int)$this->pdo->query("SELECT COUNT(*) FROM chapter_pages WHERE chapter_id = " . (int)$chapterId)->fetchColumn();
            $this->pdo->prepare("
                UPDATE chapters SET status = 'ready', total_pages = ? 
                WHERE id = ?
            ")->execute([$total_pages, $chapterId]);

            $this->pdo->commit();

            return ['success' => true, 'message' => "Chapter uploaded with " . $total_pages . " pages."];
        } catch (Exception $e) {
            @unlink($tmp_file);
            $this->pdo->rollBack();

            return ['success' => false, 'message' => 'Error processing archive: ' . $e->getMessage()];
        }
    }

    public function deleteChapter(int $chapterId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT book_id FROM chapters WHERE id = ?");
            $stmt->execute([$chapterId]);
            $chapter = $stmt->fetch(PDO::FETCH_ASSOC);

            $pages = $this->getChapterPages($chapterId);
            foreach ($pages as $page) {
                $file_path = __DIR__ . '/../../../' . $page['image_path'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }

            $this->pdo->prepare("DELETE FROM chapter_pages WHERE chapter_id = ?")->execute([$chapterId]);
            $this->pdo->prepare("DELETE FROM chapters WHERE id = ?")->execute([$chapterId]);

            return ['success' => true, 'message' => 'Chapter deleted successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting chapter: ' . $e->getMessage()];;
        }
    }

    public function deleteChapterPage(int $pageId): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT image_path FROM chapter_pages WHERE id = ?");
            $stmt->execute([$pageId]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($page) {
                $file_path = __DIR__ . '/../../../' . $page['image_path'];
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
                $this->pdo->prepare("DELETE FROM chapter_pages WHERE id = ?")->execute([$pageId]);
                return ['success' => true, 'message' => 'Page deleted successfully.'];
            }

            return ['success' => false, 'message' => 'Page not found.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error deleting page: ' . $e->getMessage()];;
        }
    }

    public function renumberChapterPages(int $chapterId): void
    {
        $stmt = $this->pdo->prepare("
            SELECT id FROM chapter_pages 
            WHERE chapter_id = ? 
            ORDER BY page_number ASC, id ASC
        ");
        $stmt->execute([$chapterId]);
        $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $update = $this->pdo->prepare("UPDATE chapter_pages SET page_number = ? WHERE id = ?");
        foreach ($pages as $index => $page) {
            $update->execute([$index + 1, $page['id']]);
        }

        $this->pdo->prepare("
            UPDATE chapters SET total_pages = ? 
            WHERE id = ?
        ")->execute([count($pages), $chapterId]);
    }

    private function isValidImageData(string $data): bool
    {
        if (empty($data) || strlen($data) < 16) {
            return false;
        }
        $info = @getimagesizefromstring($data);
        return $info !== false && isset($info[0], $info[1]);
    }

    private function isValidImageFile(string $path): bool
    {
        if (!is_file($path) || filesize($path) < 16) {
            return false;
        }
        $info = @getimagesize($path);
        return $info !== false && isset($info[0], $info[1]);
    }

    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) {
            return true;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }
        return @rmdir($dir);
    }
}





