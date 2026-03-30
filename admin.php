<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$usersCount = 0;
$productsCount = 0;
$contentCount = 0;

$userResult = $connection->query('SELECT COUNT(*) AS total FROM users');
$productResult = $connection->query('SELECT COUNT(*) AS total FROM products');
$contentResult = $connection->query('SELECT COUNT(*) AS total FROM site_content');

if ($userResult) {
    $usersCount = (int) ($userResult->fetch_assoc()['total'] ?? 0);
}
if ($productResult) {
    $productsCount = (int) ($productResult->fetch_assoc()['total'] ?? 0);
}
if ($contentResult) {
    $contentCount = (int) ($contentResult->fetch_assoc()['total'] ?? 0);
}

$pageTitle = 'Admin Panel';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Admin Panel</h1>
    <p>Manage users and store content from one place.</p>

    <div class="stats-grid">
        <article class="stat-card"><h3>Total Users</h3><p><?= $usersCount ?></p></article>
        <article class="stat-card"><h3>Total Products</h3><p><?= $productsCount ?></p></article>
        <article class="stat-card"><h3>Content Blocks</h3><p><?= $contentCount ?></p></article>
    </div>

    <div class="quick-links">
        <a class="btn btn-primary" href="admin_users.php">Manage Users</a>
        <a class="btn btn-primary" href="admin_products.php">Manage Products</a>
        <a class="btn btn-primary" href="admin_content.php">Manage Website Content</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
