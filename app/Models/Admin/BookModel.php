<?php
// app/Models/Admin/BookModel.php
// Book management business logic

class BookModel
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllBooks(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM books WHERE is_deleted = 0 ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addBook(array $post, array $files): array
    {
        $title = $post['title'] ?? '';
        $author = $post['author'] ?? '';
        $isbn = $post['isbn'] ?? '';
        $genre = $post['genre'] ?? '';
        $is_exclusive = isset($post['is_exclusive']) ? 1 : 0;
        $cover_path = 'images/book-icon.png';

        if (isset($files['cover_image']) && ($files['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_name = time() . '_' . basename($files['cover_image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($image_file_type, $allowed_extensions, true)) {
                if (move_uploaded_file($files['cover_image']['tmp_name'], $target_file)) {
                    $cover_path = 'uploads/' . $file_name;
                }
            }
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO books (title, author, isbn, genre, is_exclusive, cover_path) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $isbn, $genre, $is_exclusive, $cover_path]);
            return ['success' => true, 'message' => 'Book added successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error adding book: ' . $e->getMessage()];
        }
    }

    public function updateBook(array $post, array $files): array
    {
        $id = $post['book_id'] ?? null;
        $title = $post['title'] ?? '';
        $author = $post['author'] ?? '';
        $isbn = $post['isbn'] ?? '';
        $genre = $post['genre'] ?? '';
        $is_exclusive = isset($post['is_exclusive']) ? 1 : 0;
        $cover_path = $post['current_cover'] ?? 'images/book-icon.png';

        if (isset($files['cover_image']) && ($files['cover_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../../uploads/';
            $file_name = time() . '_' . basename($files['cover_image']['name']);
            $target_file = $upload_dir . $file_name;
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($image_file_type, $allowed_extensions, true)) {
                if (move_uploaded_file($files['cover_image']['tmp_name'], $target_file)) {
                    $cover_path = 'uploads/' . $file_name;
                }
            }
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, genre = ?, is_exclusive = ?, cover_path = ? WHERE id = ?");
            $stmt->execute([$title, $author, $isbn, $genre, $is_exclusive, $cover_path, $id]);
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
}
