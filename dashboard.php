<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Hello, <?= e($_SESSION['user_name'] ?? 'User') ?></h1>
    <p>You are logged in as <strong><?= e($_SESSION['role'] ?? 'customer') ?></strong>.</p>

    <div class="quick-links">
        <a class="btn" href="index.php">Browse Store</a>
        <?php if (isAdmin()): ?>
            <a class="btn btn-primary" href="admin.php">Open Admin Panel</a>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
