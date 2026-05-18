<?php
session_start();
// Sample data lang
$username = "John_Batumbakal"; 
$user_id = "100-001";
$user_status = "Regular";
$credit_score = 8;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys | Profile</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="clientstyle.css">
</head>
<body class="profile-page">
    <img src="../images/library-background.png" alt="Library Background" class="bg-image">
    <header>
        <div class="client-top-bar">
            <img src="../images/LibroSys.png" alt="LibroSys Logo" class="logo">
            <nav class="navigation">
                <div class="nav-links">
                    <a href="home.php">Home</a>
                    <a href="browse.php">Browse</a>
                    <a href="profile.php" class="nav-profile-link active">
                         <img src="../images/profile.png" alt="User">
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <aside class="profile-sidebar">
            <div class="user-avatar-large">
                <img src="../images/profile.png" alt="User Avatar">
            </div>
            <h2 class="display-name">John_Batumbakal</h2>
            
            <div class="info-group">
                <label>Username:</label>
                <span><strong><?php echo $username; ?></strong></span>
            </div>
            <div class="info-group">
                <label>Member ID:</label>
                <span><strong><?php echo $user_id; ?></strong></span>
            </div>
            <div class="info-group">
                <label>Membership Status:</label>
                <span><strong><?php echo $user_status; ?></strong></span>
            </div>
            
            <div class="info-group">
                <label>Credit Score:</label>
                <span class="score-display" style="color: <?php echo ($credit_score < 5) ? '#e74c3c' : '#27ae60'; ?>;">
                    <strong><?php echo $credit_score; ?> / 10</strong>
                </span>
            </div>
        
            <div class="sidebar-footer">
                <a href="settings.php"><i class='bx bx-cog'></i> Settings</a>
                <a href="logout.php"><i class='bx bx-log-out'></i> Logout</a>
            </div>
        </aside>

        <main class="profile-main">
            <div class="metrics-row">
                <div class="metric-card">
                    <p class="metric-label">TOTAL RETURNED BOOKS</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value">8</span>
                    </div>
                    <p class="metric-sub">All returned books</p>
                </div>
            
                <div class="metric-card">
                    <p class="metric-label">TOTAL BORROWED BOOKS</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value">10</span>
                    </div>
                    <p class="metric-sub">All borrowed books</p>
                </div>
            
                <div class="metric-card">
                    <p class="metric-label">TOTAL PENDING RETURN</p>
                    <div class="metric-body">
                        <img src="../images/book-icon.png" alt="Book">
                        <span class="metric-value">2</span>
                    </div>
                    <p class="metric-sub">All unreturned books</p>
                </div>
            
                <div class="metric-card credit-card" style="background-color: <?php echo ($credit_score < 5) ? '#e74c3c' : 'var(--main-color)'; ?>;">
                    <p class="metric-label">YOUR CREDIT SCORE</p>
                    <div class="metric-body">
                        <i class='bx bxs-star' style="font-size: 3rem; color: #fff;"></i>
                        <span class="metric-value"><?php echo $credit_score; ?></span>
                    </div>
                    <p class="metric-sub" style="color: #fff; font-weight: bold;">
                        <?php echo ($credit_score >= 5) ? 'GOOD STANDING' : 'PERKS LOCKED'; ?>
                    </p>
                </div>
            </div>


            <div class="records-section">
                <h3>RECORDS</h3>
                <div class="table-container">
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Date Borrowed</th>
                                <th>Due Date</th>
                                <th>Date Returned</th>
                                <th>Fare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                            <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                            <tr><td>&nbsp;</td><td></td><td></td><td></td><td></td><td></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>