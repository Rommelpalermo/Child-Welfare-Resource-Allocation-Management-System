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

$stmt = $db->prepare("SELECT * FROM children WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$child = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$child) { header('Location: index.php'); exit; }

// Status history
$history = $db->query("SELECT h.*, u.full_name changer FROM case_status_history h LEFT JOIN users u ON u.id=h.changed_by WHERE h.child_id=$id ORDER BY h.changed_at DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'View Child Record';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-person-heart me-2 text-success"></i><?= e($child['first_name'].' '.$child['last_name']) ?></h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Children</a></li>
                <li class="breadcrumb-item active">View</li>
            </ol></nav>
        </div>
        <div class="d-flex gap-2">
            <a href="edit.php?id=<?= $id ?>" class="btn btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-person me-2"></i>Personal Information</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr><th width="40%">Case Number</th><td><?= e((isset($child['case_number']) ? $child['case_number'] : '—')) ?></td></tr>
                        <tr><th>Full Name</th><td><?= e($child['first_name'].' '.((isset($child['middle_name']) ? $child['middle_name'] : '')).' '.$child['last_name']) ?></td></tr>
                        <tr><th>Gender</th><td><?= e((isset($child['gender']) ? $child['gender'] : '—')) ?></td></tr>
                        <tr><th>Birth Date</th><td><?= fmtDate($child['birth_date']) ?></td></tr>
                        <tr><th>Address</th><td><?= e((isset($child['address']) ? $child['address'] : '—')) ?></td></tr>
                        <tr><th>Guardian</th><td><?= e((isset($child['guardian_name']) ? $child['guardian_name'] : '—')) ?></td></tr>
                        <tr><th>Guardian Contact</th><td><?= e((isset($child['guardian_contact']) ? $child['guardian_contact'] : '—')) ?></td></tr>
                        <tr><th>Admission Date</th><td><?= fmtDate($child['admission_date']) ?></td></tr>
                        <tr><th>Case Type</th><td><?= e((isset($child['case_type']) ? $child['case_type'] : '—')) ?></td></tr>
                        <tr><th>Case Status</th><td>
                            <span class="badge bg-<?= $child['case_status']==='Active'?'success':($child['case_status']==='Closed'?'danger':'warning') ?>">
                                <?= e($child['case_status']) ?>
                            </span>
                        </td></tr>
                        <tr><th>Notes</th><td><?= nl2br(e((isset($child['notes']) ? $child['notes'] : '—'))) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><i class="bi bi-clock-history me-2"></i>Case Status History</div>
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
                                    <div class="small text-muted mt-1"><?= e((isset($h['remarks']) ? $h['remarks'] : '')) ?></div>
                                </div>
                                <div class="text-end text-muted small">
                                    <div><?= e((isset($h['changer']) ? $h['changer'] : 'System')) ?></div>
                                    <div><?= fmtDate($h['changed_at'], 'M d, Y H:i') ?></div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($history)): ?>
                        <li class="list-group-item text-center text-muted py-3">No history available.</li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
