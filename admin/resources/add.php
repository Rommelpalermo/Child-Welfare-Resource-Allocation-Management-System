<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();
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
    if ($quantity < 0)     $errors[] = 'Quantity cannot be negative.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO resources (item_name,category,description,quantity,unit,condition_status,location,acquired_date,notes) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssisssss', $item_name,$category,$description,$quantity,$unit,$condition,$location,$acquired,$notes);
        $stmt->execute();
        $stmt->close();
        logActivity("Added resource: $item_name", 'Resources');
        setFlash('success', 'Resource item added successfully.');
        header('Location: index.php'); exit;
    }
}

$pageTitle = 'Add Resource';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-plus-circle me-2 text-success"></i>Add Resource Item</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Resources</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-box-seam me-2"></i>Resource / Inventory Details</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Item Name <span class="text-danger">*</span></label>
                        <input type="text" name="item_name" class="form-control" value="<?= e((isset($_POST['item_name']) ? $_POST['item_name'] : '')) ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" placeholder="e.g. Clothing, Food…" value="<?= e((isset($_POST['category']) ? $_POST['category'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Unit</label>
                        <input type="text" name="unit" class="form-control" placeholder="pcs, kg, box…" value="<?= e((isset($_POST['unit']) ? $_POST['unit'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="0" value="<?= (int)((isset($_POST['quantity']) ? $_POST['quantity'] : 0)) ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Condition</label>
                        <select name="condition_status" class="form-select">
                            <?php foreach(['Good','Fair','Poor','For Disposal'] as $c): ?>
                            <option value="<?= $c ?>"><?= $c ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Location / Storage</label>
                        <input type="text" name="location" class="form-control" value="<?= e((isset($_POST['location']) ? $_POST['location'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Date Acquired</label>
                        <input type="date" name="acquired_date" class="form-control" value="<?= e((isset($_POST['acquired_date']) ? $_POST['acquired_date'] : '')) ?>"></div>
                    <div class="col-12"><label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= e((isset($_POST['description']) ? $_POST['description'] : '')) ?></textarea></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e((isset($_POST['notes']) ? $_POST['notes'] : '')) ?></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Item</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
