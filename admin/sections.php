<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Manage Sections';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name'] ?? '');
            $strandId = (int) ($_POST['strand_id'] ?? 0);

            if ($name === '' || !$strandId) {
                $error = 'Section name and strand are required.';
            } elseif ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO sections (strand_id, name) VALUES (?, ?)');
                $stmt->execute([$strandId, $name]);
                $message = 'Section added successfully.';
            } else {
                $id = (int) $_POST['id'];
                $stmt = $pdo->prepare('UPDATE sections SET strand_id = ?, name = ? WHERE id = ?');
                $stmt->execute([$strandId, $name, $id]);
                $message = 'Section updated successfully.';
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM sections WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Section deleted.';
        }
    }
}

$strands = getAllStrands($pdo);
$filterStrandId = (int) ($_GET['strand_id'] ?? 0);
$editSection = isset($_GET['edit']) ? getSectionById($pdo, (int) $_GET['edit']) : null;

$sql = 'SELECT sec.*, st.code AS strand_code FROM sections sec JOIN strands st ON sec.strand_id = st.id';
$params = [];
if ($filterStrandId) {
    $sql .= ' WHERE sec.strand_id = ?';
    $params[] = $filterStrandId;
}
$sql .= ' ORDER BY st.code ASC, sec.name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sections = $stmt->fetchAll();

include '../includes/header.php';
?>
<div class="container">
  <h1 class="page-title">Manage Sections</h1>

  <?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <div class="panel">
    <h2><?php echo $editSection ? 'Edit Section' : 'Add New Section'; ?></h2>
    <form method="POST" class="form form-inline">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <input type="hidden" name="action" value="<?php echo $editSection ? 'edit' : 'add'; ?>">
      <?php if ($editSection): ?><input type="hidden" name="id" value="<?php echo (int) $editSection['id']; ?>"><?php endif; ?>

      <label>Strand
        <select name="strand_id" required>
          <option value="">Select Strand</option>
          <?php foreach ($strands as $st): ?>
          <option value="<?php echo (int) $st['id']; ?>" <?php echo (($editSection['strand_id'] ?? 0) == $st['id']) ? 'selected' : ''; ?>>
            <?php echo h($st['code']); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Section Name
        <input type="text" name="name" required maxlength="100" placeholder="e.g. ABM 11-A"
               value="<?php echo h($editSection['name'] ?? ''); ?>">
      </label>

      <button type="submit" class="btn btn-primary"><?php echo $editSection ? 'Update Section' : 'Add Section'; ?></button>
      <?php if ($editSection): ?><a href="sections.php" class="btn btn-link">Cancel</a><?php endif; ?>
    </form>
  </div>

  <div class="filter-bar">
    <a href="sections.php" class="btn btn-small <?php echo !$filterStrandId ? 'btn-active' : ''; ?>">All</a>
    <?php foreach ($strands as $st): ?>
      <a href="sections.php?strand_id=<?php echo (int) $st['id']; ?>"
         class="btn btn-small <?php echo $filterStrandId == $st['id'] ? 'btn-active' : ''; ?>"><?php echo h($st['code']); ?></a>
    <?php endforeach; ?>
  </div>

  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Strand</th><th>Section Name</th><th>Students</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($sections as $sec): ?>
      <tr>
        <td><?php echo h($sec['strand_code']); ?></td>
        <td><?php echo h($sec['name']); ?></td>
        <td><?php echo countStudentsInSection($pdo, $sec['id']); ?></td>
        <td class="actions">
          <a href="sections.php?edit=<?php echo (int) $sec['id']; ?>" class="btn btn-small">Edit</a>
          <form method="POST" class="inline-form delete-form"
                data-confirm="Are you sure you want to delete this section? Students assigned to this section may also be affected.">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int) $sec['id']; ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($sections)): ?><tr><td colspan="4" class="empty-state">No sections found.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
