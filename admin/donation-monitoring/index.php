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
    $db->query("DELETE FROM donation_monitoring WHERE id=$id");
    setFlash('success', 'Distribution record deleted.');
    header('Location: index.php'); exit;
}

$records = $db->query("
    SELECT dm.*, d.donor_name, d.donation_type,
           c.first_name, c.last_name,
           u.full_name distributor
    FROM donation_monitoring dm
    LEFT JOIN donations d ON d.id=dm.donation_id
    LEFT JOIN children c  ON c.id=dm.child_id
    LEFT JOIN users u     ON u.id=dm.distributed_by
    ORDER BY dm.distribution_date DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Donation Monitoring';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
    <div class="page-header">
        <div>
            <h4><i class="bi bi-graph-up me-2 text-success"></i>Donation Monitoring</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Donation Monitoring</li>
            </ol></nav>
        </div>
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Record Distribution</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-graph-up me-2"></i>Donation Distribution Records</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Donation From</th><th>Type</th><th>Distributed To</th>
                        <th>Child</th><th>Qty</th><th>Amount</th><th>Date</th><th>By</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($records as $i => $r): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= e((isset($r['donor_name']) ? $r['donor_name'] : '—')) ?></td>
                        <td><?= e((isset($r['donation_type']) ? $r['donation_type'] : '—')) ?></td>
                        <td><?= e((isset($r['distributed_to']) ? $r['distributed_to'] : '—')) ?></td>
                        <td><?= $r['first_name'] ? e($r['first_name'].' '.$r['last_name']) : '—' ?></td>
                        <td><?= $r['quantity'] ?></td>
                        <td>₱<?= number_format($r['amount'],2) ?></td>
                        <td><?= fmtDate($r['distribution_date']) ?></td>
                        <td><?= e((isset($r['distributor']) ? $r['distributor'] : '—')) ?></td>
                        <td>
                            <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this distribution record?"><i class="bi bi-trash"></i></a>
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
