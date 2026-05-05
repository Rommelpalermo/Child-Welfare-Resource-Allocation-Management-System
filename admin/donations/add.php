<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();
$errors = [];
$partners = $db->query("SELECT id, org_name FROM partners WHERE status='Active' ORDER BY org_name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_name  = trim((isset($_POST['donor_name']) ? $_POST['donor_name'] : ''));
    $donor_type  = (isset($_POST['donor_type']) ? $_POST['donor_type'] : 'Individual');
    $partner_id  = (int)((isset($_POST['partner_id']) ? $_POST['partner_id'] : 0)) ?: null;
    $don_type    = (isset($_POST['donation_type']) ? $_POST['donation_type'] : 'Cash');
    $amount      = (float)((isset($_POST['amount']) ? $_POST['amount'] : 0));
    $items_desc  = trim((isset($_POST['items_desc']) ? $_POST['items_desc'] : ''));
    $don_date    = (isset($_POST['donation_date']) ? $_POST['donation_date'] : '');
    $received_by = (int)(isset($_POST['received_by']) ? $_POST['received_by'] : $_SESSION['user_id']);
    $status      = (isset($_POST['status']) ? $_POST['status'] : 'Received');
    $ack_no      = trim((isset($_POST['acknowledgement_no']) ? $_POST['acknowledgement_no'] : ''));
    $notes       = trim((isset($_POST['notes']) ? $_POST['notes'] : ''));

    if ($donor_name === '') $errors[] = 'Donor name is required.';
    if ($don_date   === '') $errors[] = 'Donation date is required.';

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO donations (donor_name,donor_type,partner_id,donation_type,amount,items_desc,donation_date,received_by,status,acknowledgement_no,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssissssisis', $donor_name,$donor_type,$partner_id,$don_type,$amount,$items_desc,$don_date,$received_by,$status,$ack_no,$notes);
        $stmt->execute();
        $stmt->close();
        logActivity("Added donation from: $donor_name", 'Donations');
        setFlash('success', 'Donation recorded successfully.');
        header('Location: index.php'); exit;
    }
}

$users = $db->query("SELECT id, full_name FROM users WHERE status='active' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Add Donation';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-gift me-2 text-success"></i>Add Donation</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Donations</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-gift me-2"></i>Donation Details</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Donor Name <span class="text-danger">*</span></label>
                        <input type="text" name="donor_name" class="form-control" value="<?= e((isset($_POST['donor_name']) ? $_POST['donor_name'] : '')) ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Donor Type</label>
                        <select name="donor_type" class="form-select">
                            <?php foreach(['Individual','Organization','Government'] as $t): ?>
                            <option value="<?= $t ?>" <?= ((isset($_POST['donor_type']) ? $_POST['donor_type'] : ''))===$t?'selected':'' ?>><?= $t ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Partner / Org</label>
                        <select name="partner_id" class="form-select">
                            <option value="">— None —</option>
                            <?php foreach ($partners as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= e($p['org_name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Donation Type</label>
                        <select name="donation_type" class="form-select">
                            <?php foreach(['Cash','In-Kind','Food','Clothing','Medical','Other'] as $t): ?>
                            <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-3"><label class="form-label">Amount (₱)</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" value="<?= e((isset($_POST['amount']) ? $_POST['amount'] : '0')) ?>"></div>
                    <div class="col-md-3"><label class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" name="donation_date" class="form-control" value="<?= e(isset($_POST['donation_date']) ? $_POST['donation_date'] : date('Y-m-d')) ?>" required></div>
                    <div class="col-md-3"><label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Received">Received</option>
                            <option value="Pending">Pending</option>
                            <option value="Acknowledged">Acknowledged</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Acknowledgement No.</label>
                        <input type="text" name="acknowledgement_no" class="form-control" value="<?= e((isset($_POST['acknowledgement_no']) ? $_POST['acknowledgement_no'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Received By</label>
                        <select name="received_by" class="form-select">
                            <?php foreach ($users as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ((int)(isset($_POST['received_by']) ? $_POST['received_by'] : $_SESSION['user_id'])===$u['id']?'selected':'') ?>><?= e($u['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-12"><label class="form-label">Items Description</label>
                        <textarea name="items_desc" class="form-control" rows="2"><?= e((isset($_POST['items_desc']) ? $_POST['items_desc'] : '')) ?></textarea></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"><?= e((isset($_POST['notes']) ? $_POST['notes'] : '')) ?></textarea></div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Record Donation</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
