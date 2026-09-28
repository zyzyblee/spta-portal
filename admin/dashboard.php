<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Dashboard';

$totalStudents  = (int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$totalPaidTx    = (int) $pdo->query("SELECT COUNT(*) FROM student_payments WHERE status = 'paid'")->fetchColumn();
$totalUnpaidTxRecorded = (int) $pdo->query("SELECT COUNT(*) FROM student_payments WHERE status = 'unpaid'")->fetchColumn();
$totalRequirements = (int) $pdo->query('SELECT COUNT(*) FROM payment_requirements')->fetchColumn();

// Transactions with no student_payments row yet are still "unpaid" by
// default (see getStudentPayments()), so include them in the count.
$rowsWithRecord = $totalPaidTx + $totalUnpaidTxRecorded;
$totalUnpaidTx = $totalUnpaidTxRecorded + max(0, ($totalStudents * $totalRequirements) - $rowsWithRecord);

$totalCollected = (float) $pdo->query(
    "SELECT COALESCE(SUM(pr.amount), 0)
     FROM student_payments sp
     JOIN payment_requirements pr ON sp.requirement_id = pr.id
     WHERE sp.status = 'paid'"
)->fetchColumn();

$totalRequiredAll = (float) $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM payment_requirements')->fetchColumn();
$totalOutstanding = max(0, ($totalRequiredAll * $totalStudents) - $totalCollected);

$fullyPaidCount = 0;
$withBalanceCount = 0;
$studentIds = $pdo->query('SELECT id FROM students')->fetchAll(PDO::FETCH_COLUMN);
foreach ($studentIds as $sid) {
    $totals = calculateStudentTotals(getStudentPayments($pdo, $sid));
    if ($totals['balance'] <= 0) {
        $fullyPaidCount++;
    } else {
        $withBalanceCount++;
    }
}

include '../includes/header.php';
?>
<div class="container">
  <h1 class="page-title">Admin Dashboard</h1>
  <p class="page-subtitle">Live snapshot of SPTA payment monitoring across all strands.</p>

  <div class="stat-grid">
    <div class="stat-card">
      <span class="stat-value"><?php echo $totalStudents; ?></span>
      <span class="stat-label">Total Students</span>
    </div>
    <div class="stat-card stat-success">
      <span class="stat-value"><?php echo $fullyPaidCount; ?></span>
      <span class="stat-label">Fully Paid Students</span>
    </div>
    <div class="stat-card stat-warning">
      <span class="stat-value"><?php echo $withBalanceCount; ?></span>
      <span class="stat-label">Students With Balance</span>
    </div>
    <div class="stat-card">
      <span class="stat-value"><?php echo formatCurrency($totalCollected); ?></span>
      <span class="stat-label">Total Amount Collected</span>
    </div>
    <div class="stat-card">
      <span class="stat-value"><?php echo formatCurrency($totalOutstanding); ?></span>
      <span class="stat-label">Total Outstanding Balance</span>
    </div>
    <div class="stat-card">
      <span class="stat-value"><?php echo $totalPaidTx; ?></span>
      <span class="stat-label">Paid Transactions</span>
    </div>
    <div class="stat-card">
      <span class="stat-value"><?php echo $totalUnpaidTx; ?></span>
      <span class="stat-label">Unpaid Transactions</span>
    </div>
  </div>

  <div class="panel">
    <h2>Quick Links</h2>
    <div class="quick-links">
      <a href="strands.php" class="quick-link">Manage Strands</a>
      <a href="sections.php" class="quick-link">Manage Sections</a>
      <a href="students.php" class="quick-link">Manage Students</a>
      <a href="reports.php" class="quick-link">View Reports</a>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
