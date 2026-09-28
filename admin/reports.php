<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Reports';

$strands = getAllStrands($pdo);

$strandSummary = [];
$sectionSummary = [];
$fullyPaidList = [];
$withBalanceList = [];
$grandRequired = 0.0;
$grandPaid = 0.0;

foreach ($strands as $strand) {
    $sections = getSectionsByStrand($pdo, $strand['id']);
    $strandRequired = 0.0;
    $strandPaid = 0.0;
    $strandStudentCount = 0;

    foreach ($sections as $section) {
        $students = getStudentsBySection($pdo, $section['id']);
        $secRequired = 0.0;
        $secPaid = 0.0;

        foreach ($students as $student) {
            $totals = calculateStudentTotals(getStudentPayments($pdo, $student['id']));
            $secRequired += $totals['required'];
            $secPaid += $totals['paid'];

            $row = [
                'student_id' => $student['student_id'],
                'name'       => $student['full_name'],
                'strand'     => $strand['code'],
                'section'    => $section['name'],
                'paid'       => $totals['paid'],
                'balance'    => $totals['balance'],
            ];

            if ($totals['balance'] <= 0) {
                $fullyPaidList[] = $row;
            } else {
                $withBalanceList[] = $row;
            }
        }

        $sectionSummary[] = [
            'strand'        => $strand['code'],
            'section'       => $section['name'],
            'student_count' => count($students),
            'required'      => $secRequired,
            'paid'          => $secPaid,
            'balance'       => $secRequired - $secPaid,
        ];

        $strandRequired += $secRequired;
        $strandPaid += $secPaid;
        $strandStudentCount += count($students);
    }

    $strandSummary[] = [
        'strand'        => $strand['code'],
        'student_count' => $strandStudentCount,
        'required'      => $strandRequired,
        'paid'          => $strandPaid,
        'balance'       => $strandRequired - $strandPaid,
    ];

    $grandRequired += $strandRequired;
    $grandPaid += $strandPaid;
}

include '../includes/header.php';
?>
<div class="container wide">
  <div class="report-toolbar no-print">
    <h1 class="page-title">Reports</h1>
    <button type="button" onclick="window.print()" class="btn btn-secondary">Print Report</button>
  </div>

  <div class="stat-grid">
    <div class="stat-card"><span class="stat-value"><?php echo formatCurrency($grandPaid); ?></span><span class="stat-label">Total Collection</span></div>
    <div class="stat-card"><span class="stat-value"><?php echo formatCurrency($grandRequired - $grandPaid); ?></span><span class="stat-label">Total Outstanding Balance</span></div>
    <div class="stat-card stat-success"><span class="stat-value"><?php echo count($fullyPaidList); ?></span><span class="stat-label">Fully Paid Students</span></div>
    <div class="stat-card stat-warning"><span class="stat-value"><?php echo count($withBalanceList); ?></span><span class="stat-label">Students With Balance</span></div>
  </div>

  <h2>Payment Summary by Strand</h2>
  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Strand</th><th>Students</th><th>Total Required</th><th>Total Paid</th><th>Balance</th></tr></thead>
    <tbody>
      <?php foreach ($strandSummary as $row): ?>
      <tr>
        <td><?php echo h($row['strand']); ?></td>
        <td><?php echo $row['student_count']; ?></td>
        <td><?php echo formatCurrency($row['required']); ?></td>
        <td><?php echo formatCurrency($row['paid']); ?></td>
        <td><?php echo formatCurrency($row['balance']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <h2>Payment Summary by Section</h2>
  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Strand</th><th>Section</th><th>Students</th><th>Total Required</th><th>Total Paid</th><th>Balance</th></tr></thead>
    <tbody>
      <?php foreach ($sectionSummary as $row): ?>
      <tr>
        <td><?php echo h($row['strand']); ?></td>
        <td><?php echo h($row['section']); ?></td>
        <td><?php echo $row['student_count']; ?></td>
        <td><?php echo formatCurrency($row['required']); ?></td>
        <td><?php echo formatCurrency($row['paid']); ?></td>
        <td><?php echo formatCurrency($row['balance']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <h2>Fully Paid Students (<?php echo count($fullyPaidList); ?>)</h2>
  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Student ID</th><th>Name</th><th>Strand</th><th>Section</th><th>Total Paid</th></tr></thead>
    <tbody>
      <?php foreach ($fullyPaidList as $row): ?>
      <tr>
        <td><?php echo h($row['student_id']); ?></td>
        <td><?php echo h($row['name']); ?></td>
        <td><?php echo h($row['strand']); ?></td>
        <td><?php echo h($row['section']); ?></td>
        <td><?php echo formatCurrency($row['paid']); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($fullyPaidList)): ?><tr><td colspan="5" class="empty-state">None yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>

  <h2>Students With Balance (<?php echo count($withBalanceList); ?>)</h2>
  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Student ID</th><th>Name</th><th>Strand</th><th>Section</th><th>Balance</th></tr></thead>
    <tbody>
      <?php foreach ($withBalanceList as $row): ?>
      <tr>
        <td><?php echo h($row['student_id']); ?></td>
        <td><?php echo h($row['name']); ?></td>
        <td><?php echo h($row['strand']); ?></td>
        <td><?php echo h($row['section']); ?></td>
        <td><?php echo formatCurrency($row['balance']); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($withBalanceList)): ?><tr><td colspan="5" class="empty-state">None — great job!</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
