<?php
// View template: expects $currentPage, $logs, $base_url
$currentPage = 'activity_log';
if (!isset($base_url)) {
    $base_url = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LibroSys - Activity Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/sidebar.php'; ?>
    <main class="content-workspace">
        <!-- Orange Sub-Topbar Navigation Title -->
        <div class="z-index">
            <div class="dashboard-bar">
                <div class="left-title">
                    <img src="<?php echo $base_url; ?>/images/lineMenu.png" class="line-menu" alt="Menu Image">
                    <span>Activity Log</span>
                </div>
                <div class="books-right">
                    <span>Admin</span>
                    <div class="admin-profile">
                        <img src="<?php echo $base_url; ?>/images/profile.png" alt="Admin Image">
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Section -->
        <section class="activity-section">
            <h2 class="section-title"><i class="fa-solid fa-clock-rotate-left"></i> ADMIN ACTIVITY LOG</h2>
            <div class="ledger-table">
                <table class="ledger-activity-table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Administrator</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($logs)): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo date("M d, Y h:i A", strtotime($log['created_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($log['admin_id']); ?></td>
                                    <td><span class="status-badge <?php echo strtolower(str_replace(' ', '-', $log['action'])); ?>"><?php echo htmlspecialchars(ucfirst($log['action'])); ?></span></td>
                                    <td><?php echo htmlspecialchars($log['details'] ?? ''); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="no-data-cell">No activity logs found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
