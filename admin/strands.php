<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireAdminLogin();

$basePath = '../';
$navContext = 'admin';
$pageTitle = 'Manage Strands';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Your session expired. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'add' || $action === 'edit') {
            $code = trim($_POST['code'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon = in_array($_POST['icon'] ?? '', ['calculator', 'flask', 'book'], true) ? $_POST['icon'] : 'book';
            $color = in_array($_POST['color_theme'] ?? '', ['navy', 'maroon', 'green'], true) ? $_POST['color_theme'] : 'navy';

            if ($code === '' || $name === '') {
                $error = 'Strand code and name are required.';
            } else {
                try {
                    if ($action === 'add') {
                        $stmt = $pdo->prepare('INSERT INTO strands (code, name, description, icon, color_theme) VALUES (?, ?, ?, ?, ?)');
                        $stmt->execute([$code, $name, $description, $icon, $color]);
                        $message = 'Strand added successfully.';
                    } else {
                        $id = (int) $_POST['id'];
                        $stmt = $pdo->prepare('UPDATE strands SET code = ?, name = ?, description = ?, icon = ?, color_theme = ? WHERE id = ?');
                        $stmt->execute([$code, $name, $description, $icon, $color, $id]);
                        $message = 'Strand updated successfully.';
                    }
                } catch (PDOException $e) {
                    $error = (strpos($e->getMessage(), 'Duplicate') !== false)
                        ? 'That strand code is already in use.'
                        : 'Something went wrong while saving the strand.';
                }
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM strands WHERE id = ?');
            $stmt->execute([$id]);
            $message = 'Strand deleted.';
        }
    }
}

$strands = getAllStrands($pdo);
$editStrand = isset($_GET['edit']) ? getStrandById($pdo, (int) $_GET['edit']) : null;

include '../includes/header.php';
?>
<div class="container">
  <h1 class="page-title">Manage Strands</h1>

  <?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-error"><?php echo h($error); ?></div><?php endif; ?>

  <div class="panel">
    <h2><?php echo $editStrand ? 'Edit Strand' : 'Add New Strand'; ?></h2>
    <form method="POST" class="form form-inline">
      <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
      <input type="hidden" name="action" value="<?php echo $editStrand ? 'edit' : 'add'; ?>">
      <?php if ($editStrand): ?><input type="hidden" name="id" value="<?php echo (int) $editStrand['id']; ?>"><?php endif; ?>

      <label>Code
        <input type="text" name="code" required maxlength="20" placeholder="e.g. ABM"
               value="<?php echo h($editStrand['code'] ?? ''); ?>">
      </label>
      <label>Full Name
        <input type="text" name="name" required maxlength="150" placeholder="e.g. ABM"
               value="<?php echo h($editStrand['name'] ?? ''); ?>">
      </label>
      <label>Description
        <input type="text" name="description" maxlength="255" placeholder="e.g. Accountancy, Business, and Management"
               value="<?php echo h($editStrand['description'] ?? ''); ?>">
      </label>
      <label>Icon
        <select name="icon">
          <?php foreach (['calculator', 'flask', 'book'] as $ic): ?>
          <option value="<?php echo $ic; ?>" <?php echo (($editStrand['icon'] ?? 'book') === $ic) ? 'selected' : ''; ?>>
            <?php echo ucfirst($ic); ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>Color Theme
        <select name="color_theme">
          <?php foreach (['navy' => 'Navy', 'maroon' => 'Maroon', 'green' => 'Green'] as $val => $labelText): ?>
          <option value="<?php echo $val; ?>" <?php echo (($editStrand['color_theme'] ?? 'navy') === $val) ? 'selected' : ''; ?>>
            <?php echo $labelText; ?>
          </option>
          <?php endforeach; ?>
        </select>
      </label>

      <button type="submit" class="btn btn-primary"><?php echo $editStrand ? 'Update Strand' : 'Add Strand'; ?></button>
      <?php if ($editStrand): ?><a href="strands.php" class="btn btn-link">Cancel</a><?php endif; ?>
    </form>
  </div>

  <div class="table-scroll">
  <table class="data-table">
    <thead><tr><th>Code</th><th>Name</th><th>Description</th><th>Sections</th><th>Actions</th></tr></thead>
    <tbody>
      <?php foreach ($strands as $s): ?>
      <tr>
        <td><strong><?php echo h($s['code']); ?></strong></td>
        <td><?php echo h($s['name']); ?></td>
        <td><?php echo h($s['description']); ?></td>
        <td><?php echo count(getSectionsByStrand($pdo, $s['id'])); ?></td>
        <td class="actions">
          <a href="strands.php?edit=<?php echo (int) $s['id']; ?>" class="btn btn-small">Edit</a>
          <form method="POST" class="inline-form delete-form"
                data-confirm="Deleting this strand will also delete all of its sections, students, and payment records. This cannot be undone. Continue?">
            <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?php echo (int) $s['id']; ?>">
            <button type="submit" class="btn btn-small btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($strands)): ?><tr><td colspan="5" class="empty-state">No strands yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
