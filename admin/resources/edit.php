<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();
$id = (int)((isset($_GET['id']) ? $_GET['id'] : 0));
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM resources WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$r) { header('Location: index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name   = trim((isset($_POST['item_name']) ? $_POST['item_name'] : ''));
    $category    = trim((isset($_POST['category']) ? $_POST['category'] : ''));
    $description = trim((isset($_POST['description']) ? $_POST['description'] : ''));
    $quantity    = (int)((isset($_POST['quantity']) ? $_POST['quantity'] : 0));
    $unit        = trim((isset($_POST['unit']) ? $_POST['unit'] : ''));
    $condition   = (isset($_POST['condition_status']) ? $_POST['condition_status'] : 'Good');
    $location    = trim((isset($_POST['location']) ? $_POST['location'] : ''));
    $acquired    = (isset($_POST['acquired_date']) ? $_POST['acquired_date'] : '');
    $notes       = trim((isset($_POST['notes']) ? $_POST['notes'] : ''));

    if ($item_name === '') $errors[] = 'Item name is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE resources SET item_name=?,category=?,description=?,quantity=?,unit=?,condition_status=?,location=?,acquired_date=?,notes=? WHERE id=?");
        $stmt->bind_param('ssissssssi', $item_name,$category,$description,$quantity,$unit,$condition,$location,$acquired,$notes,$id);
        $stmt->execute();
        $stmt->close();
        logActivity("Updated resource ID $id", 'Resources');
        setFlash('success', 'Resource item updated.');
        header('Location: index.php'); exit;
    }
    $r = array_merge($r, $_POST);
}

$pageTitle = 'Edit Resource';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil me-2 text-success"></i>Edit Resource</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Resources</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-box-seam me-2"></i>Edit: <?= e($r['item_name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" value="<?= e($r['item_name']) ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?= e((isset($r['category']) ? $r['category'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" value="<?= e((isset($r['unit']) ? $r['unit'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="0" value="<?= (int)$r['quantity'] ?>"></div>
                    <div class="col-md-3"><label class="form-label">Condition</label>
                        <select name="condition_status" class="form-select">
                            <?php foreach(['Good','Fair','Poor','For Disposal'] as $c): ?>
                            <option value="<?= $c ?>" <?= ((isset($r['condition_status']) ? $r['condition_status'] : ''))===$c?'selected':'' ?>><?= $c ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" value="<?= e((isset($r['location']) ? $r['location'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Date Acquired</label>
                        <input type="date" name="acquired_date" class="form-control" value="<?= e((isset($r['acquired_date']) ? $r['acquired_date'] : '')) ?>"></div>
                    <div class="col-12"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= e((isset($r['description']) ? $r['description'] : '')) ?></textarea></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e((isset($r['notes']) ? $r['notes'] : '')) ?></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Update Item</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
