<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? h($pageTitle) . ' — ' : ''; ?>SPTA Payment Monitoring</title>
<link rel="stylesheet" href="<?php echo $basePath; ?>css/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-inner">
    <a href="<?php echo $basePath; ?>index.php" class="brand">
      <span class="brand-badge">SC</span>
      <span class="brand-text">Sta. Catalina National High School<small>SPTA Payment Monitoring</small></span>
    </a>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
    <?php if (($navContext ?? '') === 'admin' && isAdminLoggedIn()): ?>
    <nav class="main-nav" id="mainNav">
      <a href="<?php echo $basePath; ?>admin/dashboard.php">Dashboard</a>
      <a href="<?php echo $basePath; ?>admin/strands.php">Strands</a>
      <a href="<?php echo $basePath; ?>admin/sections.php">Sections</a>
      <a href="<?php echo $basePath; ?>admin/students.php">Students</a>
      <a href="<?php echo $basePath; ?>admin/reports.php">Reports</a>
      <a href="<?php echo $basePath; ?>admin/change_password.php">Account</a>
      <a href="<?php echo $basePath; ?>logout.php" class="nav-logout">Logout</a>
    </nav>
    <?php else: ?>
    <nav class="main-nav" id="mainNav">
      <a href="<?php echo $basePath; ?>index.php">Home</a>
      <a href="<?php echo $basePath; ?>login.php" class="nav-logout">Admin Login</a>
    </nav>
    <?php endif; ?>
  </div>
</header>
<main class="site-main">
