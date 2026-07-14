<?php
// app/Controllers/Admin/BooksController.php

require_once __DIR__ . '/../../Models/Admin/BookModel.php';
require_once __DIR__ . '/../../Models/Admin/XmlModel.php';

class BooksController
{
    private PDO $pdo;
    private BookModel $bookModel;
    private XmlModel $xmlModel;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->bookModel = new BookModel($pdo);
        $this->xmlModel = new XmlModel($pdo);
    }

    public function handleActions(array $get, array $post, array $files): void
    {
        // Handle XML Export
        if (isset($get['export_xml'])) {
            $this->xmlModel->exportBooksToXML();
        }

        // Handle XML Import
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['import_xml'])) {
            if (isset($files['xml_file']) && ($files['xml_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $count = $this->xmlModel->importBooksFromXML($files['xml_file']['tmp_name']);
                echo "<script>alert('Successfully imported " . addslashes((string)$count) . " books from XML.'); window.location.href='index.php?page=admin_books';</script>";
                exit();
            }
        }

        // Upload Book
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['add_book'])) {
            $result = $this->bookModel->addBook($post, $files);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // Update Book
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['update_book'])) {
            $result = $this->bookModel->updateBook($post, $files);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // Delete Book
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['delete_book'])) {
            $bookId = (int)($post['book_id'] ?? 0);
            $result = $this->bookModel->deleteBook($bookId);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // Archive Book
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['archive_book'])) {
            $bookId = (int)($post['book_id'] ?? 0);
            $result = $this->bookModel->archiveBook($bookId);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // Restore Book
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['restore_book'])) {
            $bookId = (int)($post['book_id'] ?? 0);
            $result = $this->bookModel->restoreBook($bookId);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // Mark Unavailable
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['unavailable_book'])) {
            $bookId = (int)($post['book_id'] ?? 0);
            $result = $this->bookModel->markUnavailable($bookId);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }

        // ============ CATEGORIES ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['add_category'])) {
            $result = $this->bookModel->addCategory($post['category_name'] ?? '', $post['category_description'] ?? '');
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['update_category'])) {
            $result = $this->bookModel->updateCategory((int)($post['category_id'] ?? 0), $post['category_name'] ?? '', $post['category_description'] ?? '');
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (isset($get['delete_category'])) {
            $result = $this->bookModel->deleteCategory((int)($get['delete_category'] ?? 0));
            header("Location: index.php?page=admin_books");
            exit();
        }

        // ============ AUTHORS ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['add_author'])) {
            $birthYear = !empty($post['author_birth_year']) ? (int)$post['author_birth_year'] : null;
            $result = $this->bookModel->addAuthor($post['author_name'] ?? '', $post['author_bio'] ?? '', $birthYear);
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['update_author'])) {
            $birthYear = !empty($post['author_birth_year']) ? (int)$post['author_birth_year'] : null;
            $result = $this->bookModel->updateAuthor((int)($post['author_id'] ?? 0), $post['author_name'] ?? '', $post['author_bio'] ?? '', $birthYear);
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (isset($get['delete_author'])) {
            $result = $this->bookModel->deleteAuthor((int)($get['delete_author'] ?? 0));
            header("Location: index.php?page=admin_books");
            exit();
        }

        // ============ PUBLISHERS ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['add_publisher'])) {
            $result = $this->bookModel->addPublisher($post['publisher_name'] ?? '', $post['publisher_address'] ?? '', $post['publisher_website'] ?? '');
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['update_publisher'])) {
            $result = $this->bookModel->updatePublisher((int)($post['publisher_id'] ?? 0), $post['publisher_name'] ?? '', $post['publisher_address'] ?? '', $post['publisher_website'] ?? '');
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (isset($get['delete_publisher'])) {
            $result = $this->bookModel->deletePublisher((int)($get['delete_publisher'] ?? 0));
            header("Location: index.php?page=admin_books");
            exit();
        }

        // ============ EBOOK MANAGEMENT ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['upload_ebook'])) {
            $bookId = (int)($post['ebook_book_id'] ?? 0);
            if (isset($files['ebook_file'])) {
                $result = $this->bookModel->uploadEBook($bookId, $files['ebook_file']);
                echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
                exit();
            }
        }
        if (isset($get['delete_ebook'])) {
            $ebookId = (int)($get['delete_ebook'] ?? 0);
            $result = $this->bookModel->deleteEBook($ebookId);
            header("Location: index.php?page=admin_books");
            exit();
        }
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['update_ebook_settings'])) {
            $ebookId = (int)($post['ebook_id'] ?? 0);
            $settings = [
                'download_allowed' => isset($post['download_allowed']) ? 1 : 0,
                'online_only' => isset($post['online_only']) ? 1 : 0,
                'page_flipping' => $post['page_flipping'] ?? 'enabled',
                'theme' => $post['reader_theme'] ?? 'light',
                'font_size' => $post['font_size'] ?? 'medium',
            ];
            $result = $this->bookModel->updateEBookSettings($ebookId, $settings);
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }

        // ============ BOOK COPIES ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['add_copy'])) {
            $bookId = (int)($post['copy_book_id'] ?? 0);
            $result = $this->bookModel->addCopy($bookId, $post['copy_label'] ?? null);
            echo "<script>alert('" . addslashes($result['message']) . "'); window.location.href='index.php?page=admin_books';</script>";
            exit();
        }
        if (isset($get['delete_copy'])) {
            $copyId = (int)($get['delete_copy'] ?? 0);
            $result = $this->bookModel->deleteCopy($copyId);
            header("Location: index.php?page=admin_books");
            exit();
        }

        // ============ AJAX ENDPOINTS ============
        if (isset($get['ajax'])) {
            $ajax = $get['ajax'];
            $bookId = (int)($get['book_id'] ?? 0);
            
            if ($ajax === 'get_ebooks' && $bookId > 0) {
                $this->renderEbookModalAjax($bookId);
                exit();
            }
            if ($ajax === 'get_copies' && $bookId > 0) {
                $this->renderCopiesModalAjax($bookId);
                exit();
            }
        }

        // ============ API BOOK IMPORT ============
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($post['api_import'])) {
            $identifier = $post['api_identifier'] ?? '';
            $source = $post['api_source'] ?? 'isbn'; // isbn, google_books_id, open_library_id
            $result = $this->importBookFromApi($identifier, $source);
            if ($result['success']) {
                header("Location: index.php?page=admin_books");
                exit();
            }
            echo "<script>alert('" . addslashes($result['message']) . "');</script>";
        }
    }

    /**
     * Import book data from external APIs.
     */
    private function importBookFromApi(string $identifier, string $source): array
    {
        if (empty($identifier)) {
            return ['success' => false, 'message' => 'No identifier provided.'];
        }

        $apiData = [];

        // Try Google Books API first (works with ISBN, Google Books ID, or Open Library ID)
        $googleUrl = "https://www.googleapis.com/books/v1/volumes?q=";
        switch ($source) {
            case 'isbn':
                $googleUrl .= "isbn:{$identifier}";
                break;
            case 'google_books_id':
                // For a specific volume ID, we use a different endpoint
                $volumeUrl = "https://www.googleapis.com/books/v1/volumes/{$identifier}";
                $response = @file_get_contents($volumeUrl);
                if ($response !== false) {
                    $data = json_decode($response, true);
                    if ($data && isset($data['volumeInfo'])) {
                        $apiData = $this->parseGoogleBooksVolume($data);
                        if (!empty($apiData['title'])) {
                            return $this->bookModel->importFromApi($apiData);
                        }
                    }
                }
                return ['success' => false, 'message' => 'Could not fetch book data from Google Books API.'];
            case 'open_library_id':
                $googleUrl .= "{$identifier}";
                break;
            default:
                $googleUrl .= "isbn:{$identifier}";
        }

        $response = @file_get_contents($googleUrl);
        if ($response !== false) {
            $data = json_decode($response, true);
            if ($data && isset($data['items'][0]['volumeInfo'])) {
                $apiData = $this->parseGoogleBooksVolume($data['items'][0]);
                if (!empty($apiData['title'])) {
                    return $this->bookModel->importFromApi($apiData);
                }
            }
        }

        // Fallback: Try Open Library API
        $openLibraryUrl = "https://openlibrary.org/api/books?bibkeys=";
        if ($source === 'isbn' || $source === 'open_library_id') {
            $key = ($source === 'open_library_id') ? "OLID:{$identifier}" : "ISBN:{$identifier}";
            $openLibraryUrl .= $key . "&format=json&jscmd=data";
            $response = @file_get_contents($openLibraryUrl);
            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data && isset($data[$key])) {
                    $bookData = $data[$key];
                    $apiData = [
                        'title' => $bookData['title'] ?? '',
                        'author' => isset($bookData['authors'][0]['name']) ? $bookData['authors'][0]['name'] : '',
                        'isbn' => $identifier,
                        'publisher' => isset($bookData['publishers'][0]['name']) ? $bookData['publishers'][0]['name'] : '',
                        'publishedDate' => $bookData['publish_date'] ?? '',
                        'description' => $bookData['notes'] ?? $bookData['description'] ?? '',
                        'language' => 'English',
                        'categories' => isset($bookData['subjects']) ? array_column($bookData['subjects'], 'name') : [],
                        'thumbnail' => $bookData['cover']['large'] ?? $bookData['cover']['medium'] ?? $bookData['cover']['small'] ?? '',
                    ];
                    if (!empty($apiData['title'])) {
                        return $this->bookModel->importFromApi($apiData);
                    }
                }
            }
        }

        return ['success' => false, 'message' => 'Could not find book data. Please check the identifier and try again.'];
    }

    private function parseGoogleBooksVolume(array $item): array
    {
        $info = $item['volumeInfo'] ?? $item;
        return [
            'title' => $info['title'] ?? '',
            'author' => isset($info['authors'][0]) ? $info['authors'][0] : '',
            'isbn' => $this->extractIsbn($info['industryIdentifiers'] ?? []),
            'publisher' => $info['publisher'] ?? '',
            'publishedDate' => $info['publishedDate'] ?? '',
            'description' => $info['description'] ?? '',
            'language' => $info['language'] ?? 'English',
            'categories' => $info['categories'] ?? [],
            'thumbnail' => $info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? '',
        ];
    }

    private function extractIsbn(array $identifiers): string
    {
        foreach ($identifiers as $id) {
            if (($id['type'] ?? '') === 'ISBN_13') {
                return $id['identifier'] ?? '';
            }
        }
        foreach ($identifiers as $id) {
            if (($id['type'] ?? '') === 'ISBN_10') {
                return $id['identifier'] ?? '';
            }
        }
        return $identifiers[0]['identifier'] ?? '';
    }

    public function getAllBooks(): array
    {
        return $this->bookModel->getAllBooks();
    }

    public function getBookById(int $id): ?array
    {
        return $this->bookModel->getBookById($id);
    }

    public function getAllCategories(): array
    {
        return $this->bookModel->getAllCategories();
    }

    public function getAllAuthors(): array
    {
        return $this->bookModel->getAllAuthors();
    }

    public function getAllPublishers(): array
    {
        return $this->bookModel->getAllPublishers();
    }

    public function getEBooks(int $bookId): array
    {
        return $this->bookModel->getEBooks($bookId);
    }

    public function getBookCopies(int $bookId): array
    {
        return $this->bookModel->getBookCopies($bookId);
    }

    /**
     * Render eBook management modal content via AJAX.
     */
    private function renderEbookModalAjax(int $bookId): void
    {
        $book = $this->bookModel->getBookById($bookId);
        if (!$book) {
            echo '<p style="text-align:center;padding:20px;color:red;">Book not found.</p>';
            return;
        }
        $ebooks = $this->bookModel->getEBooks($bookId);
        ?>
        <h3 style="margin-bottom:12px;">eBook Management: <em><?php echo htmlspecialchars($book['title']); ?></em></h3>
        
        <!-- Upload form -->
        <form action="index.php?page=admin_books" method="POST" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;margin-bottom:15px;padding:12px;background:#f0f4f8;border-radius:8px;">
            <input type="hidden" name="ebook_book_id" value="<?php echo $bookId; ?>">
            <input type="file" name="ebook_file" accept=".pdf,.epub" required style="font-size:13px;flex:1;">
            <button type="submit" name="upload_ebook" class="submit-btn" style="padding:8px 16px;font-size:12px;">Upload eBook</button>
        </form>

        <?php if (empty($ebooks)): ?>
            <p style="text-align:center;padding:15px;color:#888;">No eBook files uploaded for this book.</p>
        <?php else: ?>
            <div class="ebook-list">
            <?php foreach ($ebooks as $ebook): ?>
                <div class="ebook-item" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:#f7fafc;border-radius:8px;margin-bottom:8px;">
                    <div class="file-info" style="display:flex;align-items:center;gap:10px;">
                        <i class="fa-solid fa-file-<?php echo $ebook['file_type'] === 'pdf' ? 'pdf' : 'book'; ?>" style="font-size:20px;color:#e74c3c;"></i>
                        <span style="font-weight:600;"><?php echo strtoupper($ebook['file_type']); ?></span>
                        <span style="color:#555;font-size:12px;"><?php echo round($ebook['file_size'] / 1024, 1); ?> KB</span>
                        <span style="color:#888;font-size:11px;">Uploaded: <?php echo $ebook['uploaded_at']; ?></span>
                    </div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <a href="<?php echo $ebook['file_path']; ?>" target="_blank" class="btn-sm btn-info" style="text-decoration:none;padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#3498db;">View</a>
                        <a href="index.php?page=admin_books&delete_ebook=<?php echo $ebook['id']; ?>" class="btn-sm btn-danger" style="text-decoration:none;padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#e74c3c;" onclick="return confirm('Delete this eBook?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif;
    }

    /**
     * Render copies management modal content via AJAX.
     */
    private function renderCopiesModalAjax(int $bookId): void
    {
        $book = $this->bookModel->getBookById($bookId);
        if (!$book) {
            echo '<p style="text-align:center;padding:20px;color:red;">Book not found.</p>';
            return;
        }
        $copies = $this->bookModel->getBookCopies($bookId);
        ?>
        <h3 style="margin-bottom:12px;">Copies Management: <em><?php echo htmlspecialchars($book['title']); ?></em></h3>
        
        <!-- Add copy form -->
        <form action="index.php?page=admin_books" method="POST" style="display:flex;gap:10px;align-items:center;margin-bottom:15px;padding:12px;background:#f0f4f8;border-radius:8px;">
            <input type="hidden" name="copy_book_id" value="<?php echo $bookId; ?>">
            <input type="text" name="copy_label" placeholder="Copy label (e.g., Copy #5)" style="flex:1;padding:8px 12px;border:1px solid #ddd;border-radius:6px;font-size:13px;">
            <button type="submit" name="add_copy" class="submit-btn" style="padding:8px 16px;font-size:12px;">Add Copy</button>
        </form>

        <?php if (empty($copies)): ?>
            <p style="text-align:center;padding:15px;color:#888;">No copies recorded for this book.</p>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead><tr style="background:#f8f9fa;">
                    <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #e2e8f0;">Label</th>
                    <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #e2e8f0;">Status</th>
                    <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #e2e8f0;">Created</th>
                    <th style="padding:10px 14px;text-align:left;border-bottom:2px solid #e2e8f0;">Actions</th>
                </tr></thead>
                <tbody>
                <?php foreach ($copies as $copy): ?>
                    <tr>
                        <td style="padding:10px 14px;border-bottom:1px solid #eef0f2;"><?php echo htmlspecialchars($copy['copy_label'] ?? 'Unlabeled'); ?></td>
                        <td style="padding:10px 14px;border-bottom:1px solid #eef0f2;">
                            <span class="copy-status-badge" style="display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;background:<?php echo $copy['status'] === 'available' ? '#d4edda' : ($copy['status'] === 'borrowed' ? '#fff3cd' : '#f8d7da'); ?>;color:<?php echo $copy['status'] === 'available' ? '#155724' : ($copy['status'] === 'borrowed' ? '#856404' : '#721c24'); ?>;">
                                <?php echo ucfirst($copy['status']); ?>
                            </span>
                        </td>
                        <td style="padding:10px 14px;border-bottom:1px solid #eef0f2;font-size:12px;"><?php echo $copy['created_at']; ?></td>
                        <td style="padding:10px 14px;border-bottom:1px solid #eef0f2;">
                            <a href="index.php?page=admin_books&delete_copy=<?php echo $copy['id']; ?>" class="btn-sm btn-danger" style="text-decoration:none;padding:4px 10px;font-size:11px;border:none;border-radius:4px;color:#fff;background:#e74c3c;" onclick="return confirm('Remove this copy?')">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif;
    }
}
