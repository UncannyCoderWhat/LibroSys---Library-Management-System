<?php
require_once 'dbForLogin/db.php';

try {
    // Prepare the SQL statement
    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, is_exclusive) VALUES (:title, :author, :isbn, :is_exclusive)");

    // 1. Insert 10 Exclusive Books
    for ($i = 1; $i <= 10; $i++) {
        $stmt->execute([
            ':title' => "Exclusive Book Title " . $i,
            ':author' => "Author Name " . $i,
            ':isbn' => "EXCL-" . str_pad($i, 5, "0", STR_PAD_LEFT),
            ':is_exclusive' => 1
        ]);
    }

    // 2. Insert 10 Regular Books
    for ($i = 1; $i <= 10; $i++) {
        $stmt->execute([
            ':title' => "Regular Book Title " . $i,
            ':author' => "Author Name " . ($i + 10),
            ':isbn' => "REG-" . str_pad($i, 5, "0", STR_PAD_LEFT),
            ':is_exclusive' => 0
        ]);
    }

    echo "Successfully inserted 20 books (10 Exclusive, 10 Regular).";

} catch (PDOException $e) {
    die("Error inserting books: " . $e->getMessage());
}
?>
