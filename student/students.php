<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$basePath = '../';
$navContext = 'public';

$sectionId = (int) ($_GET['section_id'] ?? 0);
$section = getSectionById($pdo, $sectionId);

if (!$section) {
    header('Location: ../index.php');
    exit;
}

$strand = getStrandById($pdo, $section['strand_id']);
$pageTitle = $section['name'] . ' Master List';

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

$students = getStudentsBySection($pdo, $sectionId, $search);
$students = filterStudentsByBalance($pdo, $students, $filter);
$requirements = getPaymentRequirements($pdo);

include '../includes/header.php';
?>
<div class="container wide">
  <a href="sections.php?strand_id=<?php echo (int) $strand['id']; ?>" class="back-link">
    &#8249; Back to <?php echo h($strand['code']); ?> Sections
  </a>
  <h1 class="page-title"><?php echo h($section['name']); ?> &mdash; Student Master List</h1>

  <form method="GET" class="filter-bar">
    <input type="hidden" name="section_id" value="<?php echo (int) $sectionId; ?>">
    <input type="text" name="search" placeholder="Search by name or student ID"
           value="<?php echo h($search); ?>">
    <select name="filter">
      <option value="">All Students</option>
      <option value="fully_paid" <?php echo $filter === 'fully_paid' ? 'selected' : ''; ?>>Fully Paid</option>
      <option value="with_balance" <?php echo $filter === 'with_balance' ? 'selected' : ''; ?>>With Balance</option>
    </select>
    <button type="submit" class="btn btn-secondary">Search</button>
    <?php if ($search !== '' || $filter !== ''): ?>
      <a href="students.php?section_id=<?php echo (int) $sectionId; ?>" class="btn btn-link">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (empty($students)): ?>
    <p class="empty-state">No students found.</p>
  <?php else: ?>
  <div class="table-scroll">
  <table class="data-table">
    <thead>
      <tr>
        <th>No.</th>
        <th>Student ID</th>
        <th>Student Name</th>
        <?php foreach ($requirements as $req): ?>
          <th><?php echo h($req['name']); ?></th>
        <?php endforeach; ?>
        <th>Total Paid</th>
        <th>Balance</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php $i = 1; foreach ($students as $student):
        $payments = getStudentPayments($pdo, $student['id']);
        $totals = calculateStudentTotals($payments);
      ?>
      <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo h($student['student_id']); ?></td>
        <td><?php echo h($student['full_name']); ?></td>
        <?php foreach ($payments as $p): ?>
          <td class="status-<?php echo $p['status']; ?>">
            <?php echo $p['status'] === 'paid' ? '&#10003;' : '&mdash;'; ?>
          </td>
        <?php endforeach; ?>
        <td><?php echo formatCurrency($totals['paid']); ?></td>
        <td><?php echo formatCurrency($totals['balance']); ?></td>
        <td>
          <span class="badge badge-<?php echo $totals['balance'] <= 0 ? 'success' : 'warning'; ?>">
            <?php echo $totals['balance'] <= 0 ? 'Fully Paid' : 'With Balance'; ?>
          </span>
        </td>
        <td><a href="payment_record.php?id=<?php echo (int) $student['id']; ?>" class="btn btn-small">View</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
