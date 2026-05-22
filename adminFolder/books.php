<?php
session_start();
$currentPage = 'books';
include "sidebar.php" ;
require_once '../dbForLogin/db.php';
require_once 'BookManager.php';

$bookManager = new BookManager($pdo);

// Handle XML Export
if (isset($_GET['export_xml'])) {
    $bookManager->exportBooksToXML();
}

// Handle XML Import
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_xml'])) {
    if (isset($_FILES['xml_file']) && $_FILES['xml_file']['error'] === UPLOAD_ERR_OK) {
        $count = $bookManager->importBooksFromXML($_FILES['xml_file']['tmp_name']);
        echo "<script>alert('Successfully imported $count books from XML.'); window.location.href='books.php';</script>";
        exit();
    }
}

// 1. Handle the "Upload Book" Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $is_exclusive = isset($_POST['is_exclusive']) ? 1 : 0;
    $cover_path = 'images/book-placeholder.jpg';

    // Handle file upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_file_type, $allowed_extensions)) {
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $cover_path = 'uploads/' . $file_name; // Save relative to root
            }
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, genre, is_exclusive, cover_path) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $isbn, $genre, $is_exclusive, $cover_path]);
        // Refresh to see changes
        header("Location: books.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error adding book: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// 1.5 Handle the "Update Book" Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
    $id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $isbn = $_POST['isbn'];
    $genre = $_POST['genre'];
    $is_exclusive = isset($_POST['is_exclusive']) ? 1 : 0;
    $cover_path = $_POST['current_cover'];

    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $file_name = time() . '_' . basename($_FILES['cover_image']['name']);
        $target_file = $upload_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_file_type, $allowed_extensions)) {
            if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $target_file)) {
                $cover_path = 'uploads/' . $file_name;
            }
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, genre = ?, is_exclusive = ?, cover_path = ? WHERE id = ?");
        $stmt->execute([$title, $author, $isbn, $genre, $is_exclusive, $cover_path, $id]);
        header("Location: books.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error updating book: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// Handle Delete Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_book'])) {
    $id = $_POST['book_id'];
    try {
        $stmt = $pdo->prepare("UPDATE books SET is_deleted = 1 WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: books.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Error deleting book: " . addslashes($e->getMessage()) . "');</script>";
    }
}

// 2. Fetch all books from the database
$stmt = $pdo->query("SELECT * FROM books WHERE is_deleted = 0 ORDER BY created_at DESC");
$all_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Books</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="../images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Books</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="../images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- XML Integration Controls -->
        <section class="activity-section" style="background: #f0f0f0; padding: 15px; border-radius: 10px; display: flex; gap: 20px; align-items: center;">
            <div style="flex: 1;">
                <h4 style="margin-bottom: 10px;"><i class="fa-solid fa-file-code"></i> Data Management (XML)</h4>
                <a href="books.php?export_xml=1" class="submit-btn" style="text-decoration: none; padding: 8px 15px; font-size: 12px; display: inline-block;">EXPORT BOOKS TO XML</a>
            </div>
            <div style="flex: 1; border-left: 1px solid #ccc; padding-left: 20px;">
                <form action="books.php" method="POST" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
                    <input type="file" name="xml_file" accept=".xml" required style="font-size: 12px;">
                    <button type="submit" name="import_xml" class="submit-btn" style="padding: 8px 15px; font-size: 12px;">IMPORT XML</button>
                </form>
            </div>
        </section>

        <!-- New Upload Book Form Section -->
        <section class="activity-section">
            <h2 class="section-title">UPLOAD NEW BOOK</h2>
            <form action="books.php" method="POST" enctype="multipart/form-data" class="add-book-form">
                <div class="form-group">
                    <input type="text" name="title" placeholder="Book Title" required>
                    <input type="text" name="author" placeholder="Author" required>
                    <input type="text" name="isbn" placeholder="ISBN/ID" required>
                    <select name="genre" required style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        <option value="" disabled selected>Select Genre</option>
                        <option value="Fiction">Fiction</option>
                        <option value="Non-Fiction">Non-Fiction</option>
                        <option value="Mystery">Mystery</option>
                        <option value="Sci-Fi">Sci-Fi</option>
                        <option value="Fantasy">Fantasy</option>
                        <option value="Romance">Romance</option>
                        <option value="Horror">Horror</option>
                        <option value="History">History</option>
                        <option value="Biography">Biography</option>
                        <option value="Action">Action</option>
                    </select>
                    <input type="file" name="cover_image" accept="image/*" class="file-input">
                    <label class="checkbox-container">
                        <input type="checkbox" name="is_exclusive">
                        <span class="checkmark"></span>
                        Exclusive Perk
                    </label>
                    <button type="submit" name="add_book" class="submit-btn">UPLOAD BOOK</button>
                </div>
            </form>
        </section>

        <!-- Search and Filter Section -->
        <section class="activity-section">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="bookSearch" placeholder="Search by title or author...">
                </div>
                <div class="filter-box">
                    <select id="categoryFilter">
                        <option value="all">All Categories</option>
                        <option value="regular">Regular Books</option>
                        <option value="exclusive">Exclusive Books</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Dynamic Book Display -->
        <section class="activity-section">
            <h2 class="section-title">LIBRARY COLLECTION</h2>
            <div class="book-grid" id="bookGrid">
                <?php if (empty($all_books)): ?>
                    <p>No books found. Use the form above to add some!</p>
                <?php else: ?>
                    <?php foreach ($all_books as $book): ?>
                        <div class="book-card" 
                             data-title="<?php echo strtolower(htmlspecialchars($book['title'])); ?>" 
                             data-author="<?php echo strtolower(htmlspecialchars($book['author'])); ?>"
                             data-genre="<?php echo strtolower(htmlspecialchars($book['genre'])); ?>"
                             data-category="<?php echo $book['is_exclusive'] ? 'exclusive' : 'regular'; ?>">
                            <div class="book-cover">
                                <img src="../<?php echo htmlspecialchars($book['cover_path']); ?>" alt="Cover" width="200" height="300" />
                                <?php if($book['is_exclusive']): ?>
                                    <div class="exclusive-badge"><i class="fa-solid fa-star"></i></div>
                                <?php endif; ?>
                                <div class="edit-btn-overlay" onclick='openEditModal(<?php echo htmlspecialchars(json_encode($book), ENT_QUOTES); ?>)'>
                                    <i class="fa-solid fa-pen-to-square" title="Edit Book"></i>
                                    <i class="fa-solid fa-trash-can" onclick="event.stopPropagation(); confirmDelete(<?php echo $book['id']; ?>)" title="Delete Book"></i>
                                </div>
                            </div>
                            <div class="book-content">
                                <h3 style="font-size: 14px; margin-bottom: 5px; height: 40px; overflow: hidden;">
                                    <?php echo htmlspecialchars($book['title']); ?></h3>
                                <p style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($book['author']); ?></p>
                                <div class="book-meta">
                                    <span class="genre"><?php echo htmlspecialchars($book['genre']); ?></span>
                                    <span class="ID"><?php echo htmlspecialchars($book['isbn']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Edit Book Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2 class="section-title">EDIT BOOK DETAILS</h2>
            <form action="books.php" method="POST" enctype="multipart/form-data" class="edit-book-form">
                <input type="hidden" name="book_id" id="edit_book_id">
                <input type="hidden" name="current_cover" id="edit_current_cover">

                <div class="modal-body">
                    <div class="form-group-vertical">
                        <label>Book Title</label>
                        <input type="text" name="title" id="edit_title" required>

                        <label>Author</label>
                        <input type="text" name="author" id="edit_author" required>

                        <label>ISBN/ID</label>
                        <input type="text" name="isbn" id="edit_isbn" required>

                        <label>Genre</label>
                        <select name="genre" id="edit_genre" required style="padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Mystery">Mystery</option>
                            <option value="Sci-Fi">Sci-Fi</option>
                            <option value="Fantasy">Fantasy</option>
                            <option value="Romance">Romance</option>
                            <option value="Horror">Horror</option>
                            <option value="History">History</option>
                            <option value="Biography">Biography</option>
                        </select>

                        <label>Change Cover Image (Optional)</label>
                        <input type="file" name="cover_image" accept="image/*">

                        <label class="checkbox-container">
                            <input type="checkbox" name="is_exclusive" id="edit_is_exclusive">
                            <span class="checkmark"></span>
                            Exclusive Perk
                        </label>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="delete-btn-modal" onclick="confirmDelete(document.getElementById('edit_book_id').value)">DELETE BOOK</button>
                        <button type="submit" name="update_book" class="submit-btn">SAVE CHANGES</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="books.js"></script>
</body>
</html>