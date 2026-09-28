<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Manage Students';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add' || $action === 'edit') {
            $studentCode = trim($_POST['student_id'] ?? '');
            $fullName = trim($_POST['full_name'] ?? '');
            $strandId = (int) ($_POST['strand_id'] ?? 0);
            $sectionId = (int) ($_POST['section_id'] ?? 0);

            if ($studentCode === '' || $fullName === '' || !$strandId || !$sectionId) {
                $error = 'All fields are required.';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare('INSERT INTO students (student_id, full_name, strand_id, section_id) VALUES (?, ?, ?, ?)');
                        $stmt->execute([$studentCode, $fullName, $strandId, $sectionId]);
                        $message = 'Student added successfully.';
                    } else {
                        $id = (int) $_POST['id'];
                        $stmt = $pdo->prepare('UPDATE students SET student_id = ?, full_name = ?, strand_id = ?, section_id = ? WHERE id = ?');
                        $stmt->execute([$studentCode, $fullName, $strandId, $sectionId, $id]);
                        $message = 'Student updated successfully.';
                    }
                } catch (PDOException $e) {
                    $error = (strpos($e->getMessage(), 'Duplicate') !== false)
                        ? 'That Student ID is already in use.'
                        : 'Something went wrong while saving the student.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Student deleted.';
        }
    }
}

$strands = getAllStrands($pdo);
$sectionsGrouped = getSectionsGroupedByStrand($pdo);
$editStudent = isset($_GET['edit']) ? getStudentById($pdo, (int) $_GET['edit']) : null;

$search = trim($_GET['search'] ?? '');
$strandFilter = (int) ($_GET['strand_id'] ?? 0);
$statusFilter = $_GET['status'] ?? '';

$students = getAllStudents($pdo, $search, $strandFilter ?: null);
$students = filterStudentsByBalance($pdo, $students, $statusFilter);

include '../includes/header.php';
?>
<div class="container wide">
  <h1 class="page-title">Manage Students</h1>

  <?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <div class="panel">
    <h2><?php echo $editStudent ? 'Edit Student' : 'Add New Student'; ?></h2>
    <form method="POST" class="form form-inline">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <input type="hidden" name="action" value="<?php echo $editStudent ? 'edit' : 'add'; ?>">
      <?php if ($editStudent): ?><input type="hidden" name="id" value="<?php echo (int) $editStudent['id']; ?>"><?php endif; ?>

      <label>Student ID
        <input type="text" name="student_id" required maxlength="30" placeholder="e.g. 2026-001"
               value="<?php echo h($editStudent['student_id'] ?? ''); ?>">
      </label>
      <label>Full Name
        <input type="text" name="full_name" required maxlength="150" placeholder="e.g. Juan Dela Cruz"
               value="<?php echo h($editStudent['full_name'] ?? ''); ?>">
      </label>
      <label>Strand
        <select name="strand_id" id="strand_select" required>
          <option value="">Select Strand</option>
          <?php foreach ($strands as $st): ?>
          <option value="<?php echo (int) $st['id']; ?>" <?php echo (($editStudent['strand_id'] ?? 0) == $st['id']) ? 'selected' : ''; ?>>
            <?php echo h($st['code']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Section
        <select name="section_id" id="section_select" required>
          <option value="">Select Section</option>
        </select>
      </label>

      <button type="submit" class="btn btn-primary"><?php echo $editStudent ? 'Update Student' : 'Add Student'; ?></button>
      <?php if ($editStudent): ?><a href="students.php" class="btn btn-link">Cancel</a><?php endif; ?>
    </form>
  </div>

  <form method="GET" class="filter-bar">
    <input type="text" name="search" placeholder="Search name or student ID" value="<?php echo h($search); ?>">
    <select name="strand_id">
      <option value="">All Strands</option>
      <?php foreach ($strands as $st): ?>
      <option value="<?php echo (int) $st['id']; ?>" <?php echo $strandFilter == $st['id'] ? 'selected' : ''; ?>>
        <?php echo h($st['code']); ?>
      </option>
      <?php endforeach; ?>
    </select>
    <select name="status">
      <option value="">All Statuses</option>
      <option value="fully_paid" <?php echo $statusFilter === 'fully_paid' ? 'selected' : ''; ?>>Fully Paid</option>
      <option value="with_balance" <?php echo $statusFilter === 'with_balance' ? 'selected' : ''; ?>>With Balance</option>
    </select>
    <button type="submit" class="btn btn-secondary">Filter</button>
    <a href="students.php" class="btn btn-link">Clear</a>
  </form>

  <div class="table-scroll">
  <table class="data-table">
    <thead>
      <tr><th>Student ID</th><th>Name</th><th>Strand</th><th>Section</th><th>Total Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php foreach ($students as $s):
        $totals = calculateStudentTotals(getStudentPayments($pdo, $s['id']));
      ?>
      <tr>
        <td><?php echo h($s['student_id']); ?></td>
        <td><?php echo h($s['full_name']); ?></td>
        <td><?php echo h($s['strand_code']); ?></td>
        <td><?php echo h($s['section_name']); ?></td>
        <td><?php echo formatCurrency($totals['paid']); ?></td>
        <td><?php echo formatCurrency($totals['balance']); ?></td>
        <td>
          <span class="badge badge-<?php echo $totals['balance'] <= 0 ? 'success' : 'warning'; ?>">
            <?php echo $totals['balance'] <= 0 ? 'Fully Paid' : 'With Balance'; ?>
          </span>
        </td>
        <td class="actions">
          <a href="payments.php?student_id=<?php echo (int) $s['id']; ?>" class="btn btn-small">Payments</a>
          <a href="students.php?edit=<?php echo (int) $s['id']; ?>" class="btn btn-small">Edit</a>
          <form method="POST" class="inline-form delete-form"
                data-confirm="Are you sure you want to delete this student record? This will also delete their payment history.">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int) $s['id']; ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($students)): ?><tr><td colspan="8" class="empty-state">No students found.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<script>
  var sectionsByStrand = <?php echo json_encode($sectionsGrouped, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
  var selectedStrandId = <?php echo json_encode($editStudent['strand_id'] ?? null); ?>;
  var selectedSectionId = <?php echo json_encode($editStudent['section_id'] ?? null); ?>;
  document.addEventListener('DOMContentLoaded', function () {
    initStrandSectionCascade('strand_select', 'section_select', sectionsByStrand, selectedStrandId, selectedSectionId);
  });
</script>
<?php include '../includes/footer.php'; ?>
