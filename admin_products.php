<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$message = '';
$error = '';
$editingProduct = null;

if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $editStmt = $connection->prepare('SELECT id, name, description, price, stock, image_url, is_active FROM products WHERE id = ? LIMIT 1');
    $editStmt->bind_param('i', $editId);
    $editStmt->execute();
    $editingProduct = $editStmt->get_result()->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save_product') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);
        $stock = (int) ($_POST['stock'] ?? 0);
        $imageUrl = trim($_POST['image_url'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $adminId = (int) ($_SESSION['user_id'] ?? 0);

        if ($name === '' || $description === '' || $price <= 0 || $stock < 0) {
            $error = 'Please provide valid product details.';
        } else {
            if ($productId > 0) {
                $stmt = $connection->prepare('UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image_url = ?, is_active = ? WHERE id = ?');
                $stmt->bind_param('ssdisii', $name, $description, $price, $stock, $imageUrl, $isActive, $productId);
                if ($stmt->execute()) {
                    $message = 'Product updated.';
                } else {
                    $error = 'Failed to update product.';
                }
            } else {
                $stmt = $connection->prepare('INSERT INTO products (name, description, price, stock, image_url, is_active, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssdisii', $name, $description, $price, $stock, $imageUrl, $isActive, $adminId);
                if ($stmt->execute()) {
                    $message = 'Product added.';
                } else {
                    $error = 'Failed to add product.';
                }
            }
        }
    } elseif ($action === 'delete_product') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stmt = $connection->prepare('DELETE FROM products WHERE id = ?');
        $stmt->bind_param('i', $productId);
        if ($stmt->execute()) {
            $message = 'Product deleted.';
        } else {
            $error = 'Failed to delete product.';
        }
    }
}

$products = $connection->query('SELECT id, name, price, stock, is_active, created_at FROM products ORDER BY created_at DESC');

$pageTitle = 'Manage Products';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Manage Products</h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <h2><?= $editingProduct ? 'Edit Product' : 'Add Product' ?></h2>
        <input type="hidden" name="product_id" value="<?= (int) ($editingProduct['id'] ?? 0) ?>">

        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?= e($editingProduct['name'] ?? '') ?>" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3" required><?= e($editingProduct['description'] ?? '') ?></textarea>

        <label for="price">Price</label>
        <input type="number" step="0.01" id="price" name="price" value="<?= e((string) ($editingProduct['price'] ?? '')) ?>" required>

        <label for="stock">Stock</label>
        <input type="number" id="stock" name="stock" value="<?= e((string) ($editingProduct['stock'] ?? '0')) ?>" min="0" required>

        <label for="image_url">Image URL</label>
        <input type="url" id="image_url" name="image_url" value="<?= e($editingProduct['image_url'] ?? '') ?>">

        <label class="checkbox-label">
            <input type="checkbox" name="is_active" <?= isset($editingProduct['is_active']) && (int) $editingProduct['is_active'] === 0 ? '' : 'checked' ?>> Active
        </label>

        <button type="submit" name="action" value="save_product" class="btn btn-primary">Save Product</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($product['name']) ?></td>
                            <td>LKR <?= number_format((float) $product['price'], 2) ?></td>
                            <td><?= (int) $product['stock'] ?></td>
                            <td><?= (int) $product['is_active'] === 1 ? 'Active' : 'Inactive' ?></td>
                            <td><?= e($product['created_at']) ?></td>
                            <td>
                                <a href="admin_products.php?edit=<?= (int) $product['id'] ?>" class="btn small">Edit</a>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
                                    <button type="submit" name="action" value="delete_product" class="btn small danger" onclick="return confirm('Delete this product?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
