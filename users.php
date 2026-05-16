<?php 
include 'sidebar.php';

// Static sample data array to mimic database rows for the UI preview
$sample_users = [
    [
        'user_id' => 'USR-001',
        'username' => 'John Doe',
        'account_status' => 'Active',
        'book_type' => 'Exclusive',
        'books_borrowed' => '2'
    ],
    [
        'user_id' => 'USR-002',
        'username' => 'Jane Smith',
        'account_status' => 'Inactive',
        'book_type' => 'Regular',
        'books_borrowed' => '0'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <img src="images/LibroSys.png" alt="Logo">
    </div>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Users</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- Populated User Cards Container -->
        <div class="user-card-container">
            <?php foreach ($sample_users as $user): ?>
                <div class="user-card">
                    <div class="user-avatar">
                        <img src="images/profile.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Account Status:</strong> <?php echo htmlspecialchars($user['account_status']); ?></p>
                        <p><strong>Book Type:</strong> <?php echo htmlspecialchars($user['book_type']); ?></p>
                        <p><strong>Books Borrowed:</strong> <?php echo htmlspecialchars($user['books_borrowed']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
</body>
</html>
