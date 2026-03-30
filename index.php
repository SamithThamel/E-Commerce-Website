<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';

$pageTitle = 'NovaCart Store';
require_once __DIR__ . '/includes/header.php';

$contentResult = $connection->query("SELECT title, body FROM site_content WHERE content_key = 'home_hero' LIMIT 1");
$hero = $contentResult ? $contentResult->fetch_assoc() : null;
$cartMessage = trim($_GET['cart'] ?? '');

$productsResult = $connection->query('SELECT id, name, description, price, stock, image_url FROM products WHERE is_active = 1 ORDER BY created_at DESC');
?>
<section class="hero">
    <div>
        <p class="eyebrow">Online Store Assignment</p>
        <h1><?= e($hero['title'] ?? 'Fresh picks for your next order') ?></h1>
        <p><?= e($hero['body'] ?? 'This store demonstrates PHP + MySQL with authentication and admin content management.') ?></p>
    </div>
    <a href="register.php" class="btn btn-primary">Create Account</a>
</section>

<section>
    <h2 class="section-title">Available Products</h2>

    <?php if ($cartMessage === 'added'): ?>
        <div class="alert alert-success">Product added to cart. <a href="cart.php">View cart</a></div>
    <?php elseif ($cartMessage === 'unavailable'): ?>
        <div class="alert alert-error">Selected product is currently unavailable.</div>
    <?php endif; ?>

    <?php if ($productsResult && $productsResult->num_rows > 0): ?>
        <div class="product-grid">
            <?php while ($product = $productsResult->fetch_assoc()): ?>
                <article class="card reveal">
                    <img src="<?= e($product['image_url'] ?: 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=700') ?>" alt="<?= e($product['name']) ?>">
                    <div class="card-body">
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['description']) ?></p>
                        <div class="card-meta">
                            <span>LKR <?= number_format((float) $product['price'], 2) ?></span>
                            <small>Stock: <?= (int) $product['stock'] ?></small>
                        </div>
                        <form method="post" action="cart.php" class="cart-add-form">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?= (int) max(1, $product['stock']) ?>" class="qty-input" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>
                            <button type="submit" class="btn btn-primary" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>
                                <?= (int) $product['stock'] <= 0 ? 'Out of Stock' : 'Add to Cart' ?>
                            </button>
                        </form>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="notice">No products available yet. Admin can add products from the panel.</div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
