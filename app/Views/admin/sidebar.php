<?php
if (!isset($base_url)) {
    $base_url = '';
}
?>
<link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">

<div class="topbar">
    <img src="<?php echo $base_url; ?>/images/LibroSys.png" alt="Logo">
</div>

<div class="sidebar">

    <div class="nav-top">
        <a href="<?php echo $base_url; ?>/index.php?page=admin_dashboard" class="<?php echo ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="<?php echo $base_url; ?>/index.php?page=admin_books" class="<?php echo ($currentPage == 'books') ? 'active' : ''; ?>">Books</a>
        <a href="<?php echo $base_url; ?>/index.php?page=admin_users" class="<?php echo ($currentPage == 'users') ? 'active' : ''; ?>">Users</a>
        <a href="<?php echo $base_url; ?>/index.php?page=admin_settings" class="<?php echo ($currentPage == 'settings') ? 'active' : ''; ?>">Settings</a>
    </div>

    <div class="nav-bottom">
        <a href="<?php echo $base_url; ?>/index.php?page=admin_logout">Logout</a>
    </div>

</div>
