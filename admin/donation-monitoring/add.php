<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();
$errors = [];

$donations = $db->query("SELECT id, donor_name, donation_date, donation_type FROM donations ORDER BY donation_date DESC")->fetch_all(MYSQLI_ASSOC);
$children  = $db->query("SELECT id, first_name, last_name FROM children WHERE case_status='Active' ORDER BY first_name")->fetch_all(MYSQLI_ASSOC);
$users     = $db->query("SELECT id, full_name FROM users WHERE status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donation_id    = (int)((isset($_POST['donation_id']) ? $_POST['donation_id'] : 0));
    $child_id       = (int)((isset($_POST['child_id']) ? $_POST['child_id'] : 0)) ?: null;
    $distributed_to = trim((isset($_POST['distributed_to']) ? $_POST['distributed_to'] : ''));
    $quantity       = (int)((isset($_POST['quantity']) ? $_POST['quantity'] : 1));
    $amount         = (float)((isset($_POST['amount']) ? $_POST['amount'] : 0));
    $distributed_by = (int)(isset($_POST['distributed_by']) ? $_POST['distributed_by'] : $_SESSION['user_id']);
    $dist_date      = (isset($_POST['distribution_date']) ? $_POST['distribution_date'] : '');
    $remarks        = trim((isset($_POST['remarks']) ? $_POST['remarks'] : ''));

    if (!$donation_id)    $errors[] = 'Please select a donation.';
    if ($dist_date === '') $errors[] = 'Distribution date is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO donation_monitoring (donation_id,child_id,distributed_to,quantity,amount,distributed_by,distribution_date,remarks) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param('iisidiss', $donation_id,$child_id,$distributed_to,$quantity,$amount,$distributed_by,$dist_date,$remarks);
        $stmt->execute();
        $stmt->close();
        logActivity("Recorded donation distribution for donation ID $donation_id", 'DonationMonitoring');
        setFlash('success', 'Distribution recorded.');
        header('Location: index.php'); exit;
    }
}

$pageTitle = 'Record Distribution';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-graph-up me-2 text-success"></i>Record Distribution</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Donation Monitoring</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-gift me-2"></i>Distribution Details</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Donation <span class="text-danger">*</span></label>
                        <select name="donation_id" class="form-select" required>
                            <option value="">— Select Donation —</option>
                            <?php foreach ($donations as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= e($d['donor_name'].' — '.fmtDate($d['donation_date']).' ('.$d['donation_type'].')') ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Child (if applicable)</label>
                        <select name="child_id" class="form-select">
                            <option value="">— None / General —</option>
                            <?php foreach ($children as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['first_name'].' '.$c['last_name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Distributed To</label>
                        <input type="text" name="distributed_to" class="form-control" placeholder="Name or group…" value="<?= e((isset($_POST['distributed_to']) ? $_POST['distributed_to'] : '')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" value="<?= (int)((isset($_POST['quantity']) ? $_POST['quantity'] : 1)) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Amount (₱)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?= e((isset($_POST['amount']) ? $_POST['amount'] : '0')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Distribution Date <span class="text-danger">*</span></label>
                        <input type="date" name="distribution_date" class="form-control" value="<?= e(isset($_POST['distribution_date']) ? $_POST['distribution_date'] : date('Y-m-d')) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Distributed By</label>
                        <select name="distributed_by" class="form-select">
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ((int)(isset($_POST['distributed_by']) ? $_POST['distributed_by'] : $_SESSION['user_id'])===$u['id']?'selected':'') ?>><?= e($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-12"><label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2"><?= e((isset($_POST['remarks']) ? $_POST['remarks'] : '')) ?></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
