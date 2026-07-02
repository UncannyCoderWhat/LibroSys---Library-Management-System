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
    }

    public function getAllBooks(): array
    {
        return $this->bookModel->getAllBooks();
    }
}
