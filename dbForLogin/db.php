<?php
$host = "localhost";
$dbname = "library_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_PERSISTENT, false);

} catch (PDOException $e) {
    die("Database Connection Error: " . $e->getMessage());
}
?>
