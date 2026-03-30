<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

$cart = getCart();
$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['product_id'] ?? 0);

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        header('Location: cart.php?status=cleared');
        exit;
    }

    if ($productId > 0) {
        $stmt = $connection->prepare('SELECT id, stock, is_active FROM products WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product || (int) $product['is_active'] !== 1) {
            if ($action === 'add') {
                header('Location: index.php?cart=unavailable');
            } else {
                header('Location: cart.php?status=unavailable');
            }
            exit;
        }

        $stock = (int) $product['stock'];

        if ($action === 'add') {
            $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
            $currentQty = (int) ($cart[$productId] ?? 0);
            $newQty = min($currentQty + $quantity, max(0, $stock));

            if ($newQty > 0) {
                $cart[$productId] = $newQty;
            }

            $_SESSION['cart'] = $cart;
            header('Location: index.php?cart=added');
            exit;
        }

        if ($action === 'update') {
            $quantity = (int) ($_POST['quantity'] ?? 0);

            if ($quantity <= 0) {
                unset($cart[$productId]);
            } else {
                $cart[$productId] = min($quantity, max(0, $stock));
            }

            $_SESSION['cart'] = $cart;
            header('Location: cart.php?status=updated');
            exit;
        }

        if ($action === 'remove') {
            unset($cart[$productId]);
            $_SESSION['cart'] = $cart;
            header('Location: cart.php?status=removed');
            exit;
        }
    }
}

$status = trim($_GET['status'] ?? '');
if ($status === 'updated') {
    $message = 'Cart updated successfully.';
} elseif ($status === 'removed') {
    $message = 'Item removed from cart.';
} elseif ($status === 'cleared') {
    $message = 'Cart cleared.';
} elseif ($status === 'unavailable') {
    $error = 'One of your selected items is unavailable.';
}

$cart = getCart();
$items = [];
$total = 0.0;

if (!empty($cart)) {
    $productIds = array_map('intval', array_keys($cart));
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $types = str_repeat('i', count($productIds));

    $stmt = $connection->prepare("SELECT id, name, price, stock, image_url, is_active FROM products WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$productIds);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ((int) $row['is_active'] !== 1) {
            continue;
        }

        $id = (int) $row['id'];
        $qty = min((int) ($cart[$id] ?? 0), (int) $row['stock']);

        if ($qty <= 0) {
            continue;
        }

        $lineTotal = (float) $row['price'] * $qty;
        $total += $lineTotal;

        $items[] = [
            'id' => $id,
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'stock' => (int) $row['stock'],
            'image_url' => $row['image_url'],
            'quantity' => $qty,
            'line_total' => $lineTotal,
        ];

        $cart[$id] = $qty;
    }

    $_SESSION['cart'] = $cart;
}

$pageTitle = 'Cart';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Your Cart</h1>
    <p>Review selected items and update quantities.</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (count($items) === 0): ?>
        <div class="notice">Your cart is empty. <a href="index.php">Browse products</a>.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <div class="cart-item">
                                    <img src="<?= e($item['image_url'] ?: 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=700') ?>" alt="<?= e($item['name']) ?>">
                                    <div>
                                        <strong><?= e($item['name']) ?></strong>
                                        <div>Available: <?= (int) $item['stock'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>LKR <?= number_format($item['price'], 2) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                    <input type="number" name="quantity" value="<?= (int) $item['quantity'] ?>" min="0" max="<?= (int) $item['stock'] ?>" class="qty-input">
                                    <button type="submit" class="btn small">Update</button>
                                </form>
                            </td>
                            <td>LKR <?= number_format($item['line_total'], 2) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="product_id" value="<?= (int) $item['id'] ?>">
                                    <button type="submit" class="btn small danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-total">
            <h2>Total: LKR <?= number_format($total, 2) ?></h2>
            <div class="quick-links">
                <a href="index.php" class="btn">Continue Shopping</a>
                <form method="post" class="inline-form">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn danger">Clear Cart</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
