<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$message = '';
$error = '';
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $targetUserId = (int) ($_POST['user_id'] ?? 0);

    if ($targetUserId <= 0) {
        $error = 'Invalid user selection.';
    } elseif ($action === 'toggle_role') {
        if ($targetUserId === $currentUserId) {
            $error = 'You cannot change your own admin role.';
        } else {
            $stmt = $connection->prepare("UPDATE users SET role = CASE WHEN role = 'admin' THEN 'customer' ELSE 'admin' END WHERE id = ?");
            $stmt->bind_param('i', $targetUserId);

            if ($stmt->execute()) {
                $message = 'User role updated successfully.';
            } else {
                $error = 'Failed to update role.';
            }
        }
    } elseif ($action === 'delete_user') {
        if ($targetUserId === $currentUserId) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $connection->prepare('DELETE FROM users WHERE id = ?');
            $stmt->bind_param('i', $targetUserId);

            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
            } else {
                $error = 'Failed to delete user.';
            }
        }
    }
}

$users = $connection->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Manage Users</h1>
    <p>Toggle user roles or remove accounts.</p>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users && $users->num_rows > 0): ?>
                    <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><?= e($user['role']) ?></td>
                            <td><?= e($user['created_at']) ?></td>
                            <td>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                                    <button type="submit" name="action" value="toggle_role" class="btn small">Toggle Role</button>
                                    <button type="submit" name="action" value="delete_user" class="btn small danger" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
