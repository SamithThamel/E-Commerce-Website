<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

requireAdmin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentId = (int) ($_POST['content_id'] ?? 0);
    $contentKey = trim($_POST['content_key'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $adminId = (int) ($_SESSION['user_id'] ?? 0);

    if ($contentKey === '' || $title === '' || $body === '') {
        $error = 'All fields are required.';
    } else {
        if ($contentId > 0) {
            $stmt = $connection->prepare('UPDATE site_content SET content_key = ?, title = ?, body = ?, updated_by = ? WHERE id = ?');
            $stmt->bind_param('sssii', $contentKey, $title, $body, $adminId, $contentId);
            if ($stmt->execute()) {
                $message = 'Content updated.';
            } else {
                $error = 'Failed to update content.';
            }
        } else {
            $stmt = $connection->prepare('INSERT INTO site_content (content_key, title, body, updated_by) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $contentKey, $title, $body, $adminId);
            if ($stmt->execute()) {
                $message = 'Content block created.';
            } else {
                $error = 'Failed to create content. Ensure content key is unique.';
            }
        }
    }
}

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $connection->prepare('DELETE FROM site_content WHERE id = ?');
    $stmt->bind_param('i', $deleteId);
    if ($stmt->execute()) {
        $message = 'Content deleted.';
    } else {
        $error = 'Failed to delete content.';
    }
}

$editingContent = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $connection->prepare('SELECT id, content_key, title, body FROM site_content WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editingContent = $stmt->get_result()->fetch_assoc();
}

$contents = $connection->query('SELECT id, content_key, title, updated_at FROM site_content ORDER BY updated_at DESC');

$pageTitle = 'Manage Website Content';
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel reveal">
    <h1>Manage Website Content</h1>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <h2><?= $editingContent ? 'Edit Content Block' : 'Create Content Block' ?></h2>
        <input type="hidden" name="content_id" value="<?= (int) ($editingContent['id'] ?? 0) ?>">

        <label for="content_key">Content Key</label>
        <input type="text" id="content_key" name="content_key" placeholder="home_hero" value="<?= e($editingContent['content_key'] ?? '') ?>" required>

        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($editingContent['title'] ?? '') ?>" required>

        <label for="body">Body</label>
        <textarea id="body" name="body" rows="4" required><?= e($editingContent['body'] ?? '') ?></textarea>

        <button type="submit" class="btn btn-primary">Save Content</button>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Content Key</th>
                    <th>Title</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($contents && $contents->num_rows > 0): ?>
                    <?php while ($content = $contents->fetch_assoc()): ?>
                        <tr>
                            <td><?= e($content['content_key']) ?></td>
                            <td><?= e($content['title']) ?></td>
                            <td><?= e($content['updated_at']) ?></td>
                            <td>
                                <a href="admin_content.php?edit=<?= (int) $content['id'] ?>" class="btn small">Edit</a>
                                <a href="admin_content.php?delete=<?= (int) $content['id'] ?>" class="btn small danger" onclick="return confirm('Delete this content block?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">No content blocks created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
