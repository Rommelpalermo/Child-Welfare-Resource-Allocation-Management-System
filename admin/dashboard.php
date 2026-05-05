<?php
session_start();
define('BASE_URL', '..');
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireRole('admin');

$db = getDB();

// Summary counts
$children   = $db->query("SELECT COUNT(*) c FROM children WHERE case_status='Active'")->fetch_assoc()['c'];
$donations  = $db->query("SELECT COALESCE(SUM(amount),0) s FROM donations WHERE MONTH(donation_date)=MONTH(NOW()) AND YEAR(donation_date)=YEAR(NOW())")->fetch_assoc()['s'];
$resources  = $db->query("SELECT COUNT(*) c FROM resources")->fetch_assoc()['c'];
$partners   = $db->query("SELECT COUNT(*) c FROM partners WHERE status='Active'")->fetch_assoc()['c'];
$totalDonors = $db->query("SELECT COUNT(DISTINCT donor_name) c FROM donations")->fetch_assoc()['c'];
$pendingDonations = $db->query("SELECT COUNT(*) c FROM donations WHERE status='Pending'")->fetch_assoc()['c'];

// Recent donations
$recentDonations = $db->query("SELECT d.*, u.full_name receiver FROM donations d LEFT JOIN users u ON u.id=d.received_by ORDER BY d.created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent children
$recentChildren = $db->query("SELECT * FROM children ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
include '../includes/header.php';
?>
<?php include '../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../includes/flash.php'; ?>

    <div class="page-header">
        <div>
            <h4><i class="bi bi-speedometer2 me-2 text-success"></i>Admin Dashboard</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Home</li>
                </ol>
            </nav>
        </div>
        <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?= date('F d, Y') ?></span>
    </div>

    <!-- Stat Cards Row 1 -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-success"><?= $children ?></div>
                        <div class="text-muted small">Active Children</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="bi bi-gift"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-primary">₱<?= number_format($donations, 0) ?></div>
                        <div class="text-muted small">Donations This Month</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-warning"><?= $resources ?></div>
                        <div class="text-muted small">Resource Items</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-info"><?= $partners ?></div>
                        <div class="text-muted small">Active Partners</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards Row 2 -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-secondary bg-opacity-10 text-secondary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-secondary"><?= $totalDonors ?></div>
                        <div class="text-muted small">Total Donors</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold text-danger"><?= $pendingDonations ?></div>
                        <div class="text-muted small">Pending Donations</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Tables -->
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-gift me-2"></i>Recent Donations</span>
                    <a href="donations/index.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light"><tr>
                                <th>Donor</th><th>Type</th><th>Amount</th><th>Date</th><th>Status</th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($recentDonations as $d): ?>
                            <tr>
                                <td><?= e($d['donor_name']) ?></td>
                                <td><?= e($d['donation_type']) ?></td>
                                <td>₱<?= number_format($d['amount'], 2) ?></td>
                                <td><?= fmtDate($d['donation_date']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $d['status']==='Received'?'success':($d['status']==='Pending'?'warning':'info') ?>">
                                        <?= e($d['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($recentDonations)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">No donations yet.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-person-heart me-2"></i>Recent Children</span>
                    <a href="children/index.php" class="btn btn-sm btn-light">View All</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                    <?php foreach ($recentChildren as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></div>
                                <small class="text-muted"><?= e((isset($c['case_type']) ? $c['case_type'] : '—')) ?></small>
                            </div>
                            <span class="badge bg-<?= $c['case_status']==='Active'?'success':($c['case_status']==='Closed'?'danger':'warning') ?>">
                                <?= e($c['case_status']) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($recentChildren)): ?>
                        <li class="list-group-item text-center text-muted py-3">No records yet.</li>
                    <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/sidebar_close.php'; ?>
<?php include '../includes/footer.php'; ?>
