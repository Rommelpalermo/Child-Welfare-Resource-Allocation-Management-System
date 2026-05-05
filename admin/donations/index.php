<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM donations WHERE id=$id");
    setFlash('success', 'Donation record deleted.');
    logActivity("Deleted donation ID $id", 'Donations');
    header('Location: index.php'); exit;
}

$donations = $db->query("SELECT d.*, u.full_name receiver, p.org_name partner_name
    FROM donations d
    LEFT JOIN users u ON u.id=d.received_by
    LEFT JOIN partners p ON p.id=d.partner_id
    ORDER BY d.donation_date DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Donation Management';
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
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Add Donation</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-gift me-2"></i>All Donations</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Donor</th><th>Type</th><th>Amount</th><th>Items</th>
                        <th>Date</th><th>Received By</th><th>Status</th><th>Actions</th>
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
                        <td><?= e((isset($d['receiver']) ? $d['receiver'] : '—')) ?></td>
                        <td><span class="badge bg-<?= $d['status']==='Received'?'success':($d['status']==='Pending'?'warning':'info') ?>">
                            <?= e($d['status']) ?></span></td>
                        <td class="text-nowrap">
                            <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="?delete=<?= $d['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this donation record?"><i class="bi bi-trash"></i></a>
                        </td>
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
