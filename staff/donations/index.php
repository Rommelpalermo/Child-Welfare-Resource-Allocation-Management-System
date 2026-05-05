<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('staff');

$db = getDB();

// Staff can only view their own received donations
$uid = (int)$_SESSION['user_id'];
$donations = $db->query("SELECT d.*, p.org_name partner_name
    FROM donations d
    LEFT JOIN partners p ON p.id=d.partner_id
    WHERE d.received_by=$uid
    ORDER BY d.donation_date DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'My Donations';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
    <div class="page-header">
        <div>
            <h4><i class="bi bi-gift me-2 text-success"></i>Donation Management</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Donations</li>
            </ol></nav>
        </div>
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Record Donation</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-gift me-2"></i>Donations I Received</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Donor</th><th>Type</th><th>Amount</th><th>Items</th><th>Date</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($donations as $i => $d): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= e($d['donor_name']) ?></td>
                        <td><?= e($d['donation_type']) ?></td>
                        <td>₱<?= number_format($d['amount'],2) ?></td>
                        <td><?= e(truncate((isset($d['items_desc']) ? $d['items_desc'] : '—'))) ?></td>
                        <td><?= fmtDate($d['donation_date']) ?></td>
                        <td><span class="badge bg-<?= $d['status']==='Received'?'success':($d['status']==='Pending'?'warning':'info') ?>">
                            <?= e($d['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
