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

$stmt = $db->prepare("SELECT * FROM partners WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$p) { header('Location: index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_name         = trim((isset($_POST['org_name']) ? $_POST['org_name'] : ''));
    $contact_person   = trim((isset($_POST['contact_person']) ? $_POST['contact_person'] : ''));
    $contact_email    = trim((isset($_POST['contact_email']) ? $_POST['contact_email'] : ''));
    $contact_phone    = trim((isset($_POST['contact_phone']) ? $_POST['contact_phone'] : ''));
    $address          = trim((isset($_POST['address']) ? $_POST['address'] : ''));
    $partnership_type = trim((isset($_POST['partnership_type']) ? $_POST['partnership_type'] : ''));
    $status           = (isset($_POST['status']) ? $_POST['status'] : 'Active');
    $notes            = trim((isset($_POST['notes']) ? $_POST['notes'] : ''));

    if ($org_name === '') $errors[] = 'Organization name is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE partners SET org_name=?,contact_person=?,contact_email=?,contact_phone=?,address=?,partnership_type=?,status=?,notes=? WHERE id=?");
        $stmt->bind_param('ssssssssi', $org_name,$contact_person,$contact_email,$contact_phone,$address,$partnership_type,$status,$notes,$id);
        $stmt->execute();
        $stmt->close();
        logActivity("Updated partner ID $id", 'Partners');
        setFlash('success', 'Partner updated successfully.');
        header('Location: index.php'); exit;
    }
    $p = array_merge($p, $_POST);
}

$pageTitle = 'Edit Partner';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil me-2 text-success"></i>Edit Partner</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Partners</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-building me-2"></i>Edit: <?= e($p['org_name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Organization Name <span class="text-danger">*</span></label>
                        <input type="text" name="org_name" class="form-control" value="<?= e($p['org_name']) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Partnership Type</label>
                        <input type="text" name="partnership_type" class="form-control" value="<?= e((isset($p['partnership_type']) ? $p['partnership_type'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" value="<?= e((isset($p['contact_person']) ? $p['contact_person'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= e((isset($p['contact_email']) ? $p['contact_email'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Phone</label>
                        <input type="text" name="contact_phone" class="form-control" value="<?= e((isset($p['contact_phone']) ? $p['contact_phone'] : '')) ?>"></div>
                    <div class="col-md-8"><label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control" value="<?= e((isset($p['address']) ? $p['address'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Active"   <?= ($p['status']==='Active'?'selected':'') ?>>Active</option>
                            <option value="Inactive" <?= ($p['status']==='Inactive'?'selected':'') ?>>Inactive</option>
                        </select></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e((isset($p['notes']) ? $p['notes'] : '')) ?></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Update Partner</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
