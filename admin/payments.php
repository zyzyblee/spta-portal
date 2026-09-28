<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';

$studentId = (int) ($_GET['student_id'] ?? 0);
$student = getStudentById($pdo, $studentId);

if (!$student) {
    header('Location: students.php');
    exit;
}

$strand = getStrandById($pdo, $student['strand_id']);
$section = getSectionById($pdo, $student['section_id']);
$pageTitle = 'Payments — ' . $student['full_name'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $requirementId = (int) ($_POST['requirement_id'] ?? 0);
        $status = ($_POST['status'] ?? 'unpaid') === 'paid' ? 'paid' : 'unpaid';
        $datePaid = $_POST['date_paid'] ?? '';

        if ($status === 'paid' && $datePaid === '') {
            $datePaid = date('Y-m-d');
        }

        updateStudentPayment($pdo, $studentId, $requirementId, $status, $datePaid);
        $message = 'Payment record updated.';
    }
}

$payments = getStudentPayments($pdo, $studentId);
$totals = calculateStudentTotals($payments);

include '../includes/header.php';
?>
<div class="container">
  <a href="students.php" class="back-link">&#8249; Back to Students</a>
  <h1 class="page-title">Payment Record</h1>

  <?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <div class="record-card">
    <div class="student-info-grid">
      <div><span class="label">Student ID</span><span class="value"><?php echo h($student['student_id']); ?></span></div>
      <div><span class="label">Student Name</span><span class="value"><?php echo h($student['full_name']); ?></span></div>
      <div><span class="label">Strand</span><span class="value"><?php echo h($strand['code']); ?></span></div>
      <div><span class="label">Section</span><span class="value"><?php echo h($section['name']); ?></span></div>
    </div>

    <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr><th>Requirement</th><th>Amount</th><th>Status</th><th>Date Paid</th><th>Update</th></tr>
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
          <td>
            <form method="POST" class="payment-update-form">
              <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
              <input type="hidden" name="requirement_id" value="<?php echo (int) $p['requirement_id']; ?>">
              <select name="status" class="status-select">
                <option value="unpaid" <?php echo $p['status'] === 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                <option value="paid" <?php echo $p['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
              </select>
              <input type="date" name="date_paid" value="<?php echo h($p['date_paid'] ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>">
              <button type="submit" class="btn btn-small btn-primary">Save</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr><td>Total Required</td><td colspan="4"><?php echo formatCurrency($totals['required']); ?></td></tr>
        <tr><td>Total Paid</td><td colspan="4"><?php echo formatCurrency($totals['paid']); ?></td></tr>
        <tr><td>Remaining Balance</td><td colspan="4"><?php echo formatCurrency($totals['balance']); ?></td></tr>
      </tfoot>
    </table>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
