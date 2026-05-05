<?php
session_start();
define('BASE_URL', '..');
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireRole('staff');

$db  = getDB();
$uid = (int)$_SESSION['user_id'];

$activeChildren  = $db->query("SELECT COUNT(*) c FROM children WHERE case_status='Active'")->fetch_assoc()['c'];
$myDonations     = $db->query("SELECT COUNT(*) c FROM donations WHERE received_by=$uid")->fetch_assoc()['c'];
$pendingCases    = $db->query("SELECT COUNT(*) c FROM children WHERE case_status='Active'")->fetch_assoc()['c'];
$recentChildren  = $db->query("SELECT * FROM children ORDER BY created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$recentDonations = $db->query("SELECT * FROM donations WHERE received_by=$uid ORDER BY donation_date DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Staff Dashboard';
include '../includes/header.php';
?>
<?php include '../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../includes/flash.php'; ?>

    <div class="page-header">
        <div>
            <h4><i class="bi bi-speedometer2 me-2 text-success"></i>Staff Dashboard</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active">Home</li>
            </ol></nav>
        </div>
        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('F d, Y') ?></span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success"><i class="bi bi-person-heart"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-success"><?= $activeChildren ?></div>
                        <div class="text-muted small">Active Children</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-gift"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-primary"><?= $myDonations ?></div>
                        <div class="text-muted small">My Received Donations</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning"><i class="bi bi-clipboard2-pulse"></i></div>
                    <div>
                        <div class="fs-2 fw-bold text-warning"><?= $pendingCases ?></div>
                        <div class="text-muted small">Cases to Monitor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-person-heart me-2"></i>Recent Child Records</span>
                    <a href="children/index.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr><th>Name</th><th>Case Type</th><th>Admission</th><th>Status</th></tr></thead>
                            <tbody>
                            <?php foreach ($recentChildren as $c): ?>
                            <tr>
                                <td><?= e($c['first_name'].' '.$c['last_name']) ?></td>
                                <td><?= e((isset($c['case_type']) ? $c['case_type'] : '—')) ?></td>
                                <td><?= fmtDate($c['admission_date']) ?></td>
                                <td><span class="badge bg-<?= $c['case_status']==='Active'?'success':($c['case_status']==='Closed'?'danger':'warning') ?>">
                                    <?= e($c['case_status']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-gift me-2"></i>My Recent Donations</span>
                    <a href="donations/index.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($recentDonations as $d): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($d['donor_name']) ?></div>
                                <small class="text-muted"><?= e($d['donation_type']) ?> — <?= fmtDate($d['donation_date']) ?></small>
                            </div>
                            <span class="text-success fw-semibold">₱<?= number_format($d['amount'],0) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($recentDonations)): ?>
                        <li class="list-group-item text-center text-muted py-3">No donations recorded yet.</li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/sidebar_close.php'; ?>
<?php include '../includes/footer.php'; ?>
