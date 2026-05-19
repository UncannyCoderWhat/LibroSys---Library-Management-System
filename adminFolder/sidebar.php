<link rel="stylesheet" href="style.css">

<div class="topbar">
    <img src="../images/LibroSys.png" alt="Logo">
</div>

<div class="sidebar">

    <div class="nav-top">
        <a href="dashboard.php" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="books.php" class="<?php echo ($currentPage == 'books') ? 'active' : ''; ?>">Books</a>
        <a href="ledger.php" class="<?php echo ($currentPage == 'ledger') ? 'active' : ''; ?>">Ledger</a>
        <a href="borrowed.php" class="<?php echo ($currentPage == 'borrowed') ? 'active' : ''; ?>">Borrowed Books</a>
        <a href="users.php" class="<?php echo ($currentPage == 'users') ? 'active' : ''; ?>">Users</a>
        <a href="settings.php" class="<?php echo ($currentPage == 'settings') ? 'active' : ''; ?>">Settings</a>
    </div>

    <div class="nav-bottom">
        <a href="logout.php">Logout</a>
    </div>

</div>
