<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$basePath = '';
$navContext = 'public';
$pageTitle = 'Admin Login';

if (isAdminLoggedIn()) {
    header('Location: admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: admin/dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

include 'includes/header.php';
?>
<div class="container narrow">
  <div class="auth-card">
    <h1>Admin Login</h1>
    <p class="muted">Sign in to manage strands, sections, students, and payments.</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="form" novalidate>
      <label>Username
        <input type="text" name="username" required autofocus
               value="<?php echo h($_POST['username'] ?? ''); ?>">
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button type="submit" class="btn btn-primary btn-block">Log In</button>
    </form>

    <p class="muted small">
      First time here? Run <code>setup.php</code> once to create the
      default account (<code>admin</code> / <code>Admin@123</code>),
      then change the password from the Account page after logging in.
    </p>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
