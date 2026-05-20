<?php 
$currentPage = 'users';
include 'sidebar.php';
require_once '../dbForLogin/db.php';
require_once 'dashboardController.php';

// Initialize DashboardController to use its methods
$controller = new DashboardController($pdo);

// Fetch real users and their borrow counts
$stmt = $pdo->query("SELECT u.*, (SELECT COUNT(*) FROM borrows WHERE user_id = u.id AND status = 'borrowed') as active_borrows FROM users u");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <img src="../images/LibroSys.png" alt="Logo">
    </div>

    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="../images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Users</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="../images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar Section -->
        <section class="activity-section" style="margin-top: 25px;">
            <div class="search-filter-container">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="userSearch" placeholder="Search by name, email, or User ID...">
                </div>
            </div>
        </section>

        <!-- Populated User Cards Container -->
        <div class="user-card-container" id="userContainer">
            <?php foreach ($users as $user): ?>
                <div class="user-card" 
                     data-name="<?php echo strtolower(htmlspecialchars($user['name'])); ?>" 
                     data-id="<?php echo strtolower(htmlspecialchars($user['user_id'])); ?>"
                     data-email="<?php echo strtolower(htmlspecialchars($user['email'])); ?>">
                    
                    <div class="user-avatar">
                        <img src="../images/profile.png" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <p><strong>User ID:</strong> <?php echo htmlspecialchars($user['user_id']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Credit Score:</strong> <span style="color: <?php echo ($user['credit_score'] <= 5) ? 'red' : 'green'; ?>; font-weight: bold;"><?php echo htmlspecialchars($user['credit_score']); ?> / 10</span></p>
                        <?php $totalFines = $controller->getUserTotalFines($user['id']); ?>
                        <p><strong>Total Fines Owed:</strong> <span style="color: <?php echo ($totalFines > 0) ? 'red' : 'green'; ?>; font-weight: bold;">₱<?php echo number_format($totalFines, 2); ?></span></p>
                        <p><strong>Books Borrowed:</strong> <?php echo htmlspecialchars($user['active_borrows']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const userSearch = document.getElementById('userSearch');
        const userContainer = document.getElementById('userContainer');

        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = userContainer.querySelectorAll('.user-card');

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const id = card.getAttribute('data-id');
                const email = card.getAttribute('data-email');

                const matches = name.includes(searchTerm) || 
                                id.includes(searchTerm) || 
                                email.includes(searchTerm);
                card.style.display = matches ? 'flex' : 'none';
            });
        });
    });
    </script>
</body>
</html>
