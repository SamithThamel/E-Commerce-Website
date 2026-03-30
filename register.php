<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $checkStmt = $connection->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email is already registered.';
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $connection->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "customer")');
            $insertStmt->bind_param('sss', $name, $email, $passwordHash);

            if ($insertStmt->execute()) {
                $success = 'Registration successful. Please login.';
            } else {
                $error = 'Could not complete registration. Try again.';
            }
        }
    }
}

$pageTitle = 'Register';
require_once __DIR__ . '/includes/header.php';
?>
<section class="form-section reveal">
    <h1>Create Account</h1>
    <p>Join NovaCart to access your dashboard.</p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success"><?= e($success) ?> <a href="login.php">Login now</a>.</div>
    <?php endif; ?>

    <form method="post" class="form-card">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="6" required>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
