<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$basePath = '../';
$navContext = 'public';

$studentId = (int) ($_GET['id'] ?? 0);
$student = getStudentById($pdo, $studentId);

if (!$student) {
    header('Location: ../index.php');
    exit;
}

$strand = getStrandById($pdo, $student['strand_id']);
$section = getSectionById($pdo, $student['section_id']);
$pageTitle = $student['full_name'] . ' — Payment Record';

$payments = getStudentPayments($pdo, $studentId);
$totals = calculateStudentTotals($payments);

include '../includes/header.php';
?>
<div class="container">
  <a href="students.php?section_id=<?php echo (int) $student['section_id']; ?>" class="back-link no-print">
    &#8249; Back to <?php echo h($section['name']); ?>
  </a>

  <div class="record-card">
    <h1 class="page-title">Student Payment Record</h1>

    <div class="student-info-grid">
      <div><span class="label">Student ID</span><span class="value"><?php echo h($student['student_id']); ?></span></div>
      <div><span class="label">Student Name</span><span class="value"><?php echo h($student['full_name']); ?></span></div>
      <div><span class="label">Strand</span><span class="value"><?php echo h($strand['code']); ?></span></div>
      <div><span class="label">Section</span><span class="value"><?php echo h($section['name']); ?></span></div>
    </div>

    <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>Requirement</th><th>Amount</th><th>Status</th><th>Date Paid</th></tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td><?php echo h($p['name']); ?></td>
          <td><?php echo formatCurrency($p['amount']); ?></td>
          <td>
            <span class="badge badge-<?php echo $p['status'] === 'paid' ? 'success' : 'warning'; ?>">
              <?php echo ucfirst($p['status']); ?>
            </span>
          </td>
          <td><?php echo formatDate($p['date_paid']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td>Total Required</td><td colspan="3"><?php echo formatCurrency($totals['required']); ?></td></tr>
        <tr><td>Total Paid</td><td colspan="3"><?php echo formatCurrency($totals['paid']); ?></td></tr>
        <tr><td>Remaining Balance</td><td colspan="3"><?php echo formatCurrency($totals['balance']); ?></td></tr>
      </tfoot>
    </table>
    </div>

    <button type="button" onclick="window.print()" class="btn btn-secondary no-print">Print Record</button>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
