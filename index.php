<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

$basePath = '';
$navContext = 'public';
$pageTitle = 'Home';

$strands = getAllStrands($pdo);

include 'includes/header.php';
?>
<div class="container">
  <h1 class="page-title">Select Your Strand</h1>
  <p class="page-subtitle">Choose a strand below to view its sections and SPTA payment records.</p>

  <?php if (empty($strands)): ?>
    <p class="empty-state">No strands have been set up yet. Please check back later.</p>
  <?php else: ?>
  <div class="strand-cards">
    <?php foreach ($strands as $strand): ?>
    <a href="student/sections.php?strand_id=<?php echo (int) $strand['id']; ?>"
       class="strand-card theme-<?php echo h($strand['color_theme']); ?>">
      <span class="strand-icon"><?php echo strandIcon($strand['icon']); ?></span>
      <span class="strand-info">
        <span class="strand-code"><?php echo h($strand['code']); ?></span>
        <span class="strand-desc"><?php echo h($strand['description']); ?></span>
      </span>
      <span class="strand-arrow" aria-hidden="true">&#8250;</span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
