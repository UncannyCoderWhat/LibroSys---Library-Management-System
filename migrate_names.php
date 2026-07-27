<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN first_name VARCHAR(255) DEFAULT NULL AFTER name");
    echo "Column 'first_name' added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'first_name' already exists.\n";
    } else {
        echo "Error adding first_name: " . $e->getMessage() . "\n";
    }
}

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN last_name VARCHAR(255) DEFAULT NULL AFTER first_name");
    echo "Column 'last_name' added successfully.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "Column 'last_name' already exists.\n";
    } else {
        echo "Error adding last_name: " . $e->getMessage() . "\n";
    }
}

// Backfill existing users: set first_name = name, last_name = ''
try {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE first_name IS NULL OR first_name = ''");
    $count = 0;
    $updateStmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = '' WHERE id = ?");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $updateStmt->execute([$row['name'], $row['id']]);
        $count++;
    }
    echo "Backfilled {$count} existing users.\n";
} catch (PDOException $e) {
    echo "Error backfilling: " . $e->getMessage() . "\n";
}

echo "Migration complete.\n";
