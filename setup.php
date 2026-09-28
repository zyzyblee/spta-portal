<?php
/**
 * ONE-TIME SETUP SCRIPT
 * ----------------------------------------------------------------
 * Run this once in your browser after importing database.sql:
 *     http://localhost/spta-portal/setup.php
 *
 * It creates the default administrator account using PHP's own
 * password_hash() function on YOUR machine, so the password always
 * verifies correctly no matter which PHP build you're running.
 *
 * Default account created:
 *     Username: admin
 *     Password: Admin@123
 *
 * For security, delete this file (or rename it) once setup is done.
 * It will also refuse to run again once an admin account exists.
 * ----------------------------------------------------------------
 */

require_once __DIR__ . '/config/database.php';

$defaultUsername = 'admin';
$defaultPassword = 'Admin@123';

$stmt = $pdo->query('SELECT COUNT(*) FROM admin_users');
$adminCount = (int) $stmt->fetchColumn();

$alreadyExists = $adminCount > 0;

if (!$alreadyExists) {
    $hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
    $insert = $pdo->prepare(
        'INSERT INTO admin_users (username, password, full_name) VALUES (?, ?, ?)'
    );
    $insert->execute([$defaultUsername, $hash, 'System Administrator']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPTA Portal Setup</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container narrow" style="padding-top:60px;">
  <div class="auth-card">
    <?php if ($alreadyExists): ?>
      <h1>Setup Already Complete</h1>
      <div class="alert alert-warning">
        An administrator account already exists, so no changes were made.
      </div>
      <p class="muted">
        If you forgot the password, use the "Change Password" screen
        after logging in, or update the <code>admin_users</code> table
        directly in phpMyAdmin.
      </p>
    <?php else: ?>
      <h1>Setup Complete</h1>
      <div class="alert alert-success">
        The default administrator account has been created successfully.
      </div>
      <p><strong>Username:</strong> <code>admin</code><br>
         <strong>Password:</strong> <code>Admin@123</code></p>
      <p class="muted small">
        Please log in and change this password right away, then delete
        <code>setup.php</code> from the project folder for security.
      </p>
    <?php endif; ?>
    <p><a class="btn btn-primary" href="login.php">Go to Admin Login</a></p>
  </div>
</div>
</body>
</html>
