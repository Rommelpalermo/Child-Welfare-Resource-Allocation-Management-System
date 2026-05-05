<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('staff');

$db = getDB();
$id = (int)((isset($_GET['id']) ? $_GET['id'] : 0));
if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM children WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$child = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$child) { header('Location: index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = (isset($_POST['case_status']) ? $_POST['case_status'] : '');
    $remarks    = trim((isset($_POST['remarks']) ? $_POST['remarks'] : ''));
    $uid        = (int)$_SESSION['user_id'];

    if ($new_status === '') {
        $errors[] = 'Please select a status.';
    } else {
        $old_status = $child['case_status'];

        $stmt = $db->prepare("UPDATE children SET case_status=? WHERE id=?");
        $stmt->bind_param('si', $new_status, $id);
        $stmt->execute();
        $stmt->close();

        $hStmt = $db->prepare("INSERT INTO case_status_history (child_id,old_status,new_status,remarks,changed_by) VALUES (?,?,?,?,?)");
        $hStmt->bind_param('isssi', $id, $old_status, $new_status, $remarks, $uid);
        $hStmt->execute();
        $hStmt->close();

        logActivity("Updated case status for child ID $id to $new_status", 'Cases');
        setFlash('success', 'Case status updated successfully.');
        header('Location: index.php'); exit;
    }
}

// Recent history
$history = $db->query("SELECT h.*, u.full_name changer FROM case_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.child_id=$id ORDER BY h.changed_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Update Case Status';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-clipboard2-pulse me-2 text-success"></i>Update Case Status</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Cases</a></li>
                <li class="breadcrumb-item active">Update</li>
            </ol></nav>
        </div>
        <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-person me-2"></i>Child Information</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><th>Name</th><td><?= e($child['first_name'].' '.$child['last_name']) ?></td></tr>
                        <tr><th>Case No.</th><td><?= e((isset($child['case_number']) ? $child['case_number'] : '—')) ?></td></tr>
                        <tr><th>Case Type</th><td><?= e((isset($child['case_type']) ? $child['case_type'] : '—')) ?></td></tr>
                        <tr><th>Current Status</th><td>
                            <span class="badge bg-<?= $child['case_status']==='Active'?'success':($child['case_status']==='Closed'?'danger':'warning') ?>">
                                <?= e($child['case_status']) ?></span></td></tr>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Update Status</div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Status <span class="text-danger">*</span></label>
                            <select name="case_status" class="form-select" required>
                                <option value="">— Select Status —</option>
                                <?php foreach(['Active','Closed','Referred','Reunified'] as $s): ?>
                                <option value="<?= $s ?>" <?= $child['case_status']===$s?'selected':'' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks / Notes</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Briefly describe the reason for the status change…"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>Save Status Update</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i>Recent Status History</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($history as $h): ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <?php if ($h['old_status']): ?>
                                <span class="badge bg-secondary"><?= e($h['old_status']) ?></span>
                                <i class="bi bi-arrow-right mx-1"></i>
                                <?php endif; ?>
                                <span class="badge bg-success"><?= e($h['new_status']) ?></span>
                                <?php if ($h['remarks']): ?>
                                <div class="small text-muted mt-1"><?= e($h['remarks']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="text-end small text-muted">
                                <div><?= e((isset($h['changer']) ? $h['changer'] : 'System')) ?></div>
                                <div><?= fmtDate($h['changed_at'], 'M d, Y H:i') ?></div>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                        <li class="list-group-item text-center text-muted py-3">No history yet.</li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
