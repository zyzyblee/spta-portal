<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Change Password';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($current, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters long.';
        } elseif ($new !== $confirm) {
            $error = 'New password and confirmation do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE admin_users SET password = ? WHERE id = ?');
            $update->execute([$newHash, $admin['id']]);
            $message = 'Password updated successfully.';
        }
    }
}

include '../includes/header.php';
?>
<div class="container narrow">
  <div class="auth-card">
    <h1>Change Password</h1>
    <p class="muted">Logged in as <strong><?php echo h($_SESSION['admin_username'] ?? ''); ?></strong>.</p>

    <?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

    <form method="POST" class="form">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <label>Current Password <input type="password" name="current_password" required></label>
      <label>New Password <input type="password" name="new_password" required minlength="8"></label>
      <label>Confirm New Password <input type="password" name="confirm_password" required minlength="8"></label>
      <button type="submit" class="btn btn-primary btn-block">Update Password</button>
    </form>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
