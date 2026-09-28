<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$basePath = '../';
$navContext = 'public';

$strandId = (int) ($_GET['strand_id'] ?? 0);
$strand = getStrandById($pdo, $strandId);

if (!$strand) {
    header('Location: ../index.php');
    exit;
}

$pageTitle = $strand['code'] . ' Sections';
$sections = getSectionsByStrand($pdo, $strandId);

include '../includes/header.php';
?>
<div class="container">
  <a href="../index.php" class="back-link">&#8249; Back to Strands</a>
  <h1 class="page-title"><?php echo h($strand['code']); ?> Sections</h1>
  <p class="page-subtitle"><?php echo h($strand['description']); ?></p>

  <?php if (empty($sections)): ?>
    <p class="empty-state">No sections have been added for this strand yet.</p>
  <?php else: ?>
  <div class="section-grid">
    <?php foreach ($sections as $section): ?>
    <a href="students.php?section_id=<?php echo (int) $section['id']; ?>" class="section-card">
      <span class="section-name"><?php echo h($section['name']); ?></span>
      <span class="section-arrow" aria-hidden="true">&#8250;</span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
