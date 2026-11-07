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
    $db->query("DELETE FROM resources WHERE id=$id");
    setFlash('success', 'Resource item deleted.');
    logActivity("Deleted resource ID $id", 'Resources');
    header('Location: index.php'); exit;
}

if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    $id = (int)$_GET['archive'];
    $db->query("UPDATE resources SET is_archived=1 WHERE id=$id");
    setFlash('success', 'Resource item archived.');
    logActivity("Archived resource ID $id", 'Resources');
    header('Location: index.php'); exit;
}
if (isset($_GET['restore']) && is_numeric($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $db->query("UPDATE resources SET is_archived=0 WHERE id=$id");
    setFlash('success', 'Resource item restored.');
    logActivity("Restored resource ID $id", 'Resources');
    header('Location: index.php?view=archived'); exit;
}

$showArchived = (isset($_GET['view']) ? $_GET['view'] : '') === 'archived';
$arch = $showArchived ? 1 : 0;
$resources = $db->query("SELECT * FROM resources WHERE is_archived=$arch ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Resource & Inventory';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
    <div class="page-header">
        <div>
            <h4><i class="bi bi-box-seam me-2 text-success"></i>Resource &amp; Inventory</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Resources</li>
            </ol></nav>
        </div>
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Add Item</a>
    </div>

    <?php include '../../includes/archive_toggle.php'; ?>
    <div class="card">
        <div class="card-header"><i class="bi bi-box-seam me-2"></i><?= $showArchived ? 'Archived Items' : 'Inventory List' ?></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Item Name</th><th>Category</th><th>Qty</th><th>Unit</th>
                        <th>Condition</th><th>Location</th><th>Acquired</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($resources as $i => $r): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= e($r['item_name']) ?></td>
                        <td><?= e((isset($r['category']) ? $r['category'] : '—')) ?></td>
                        <td><?= $r['quantity'] ?></td>
                        <td><?= e((isset($r['unit']) ? $r['unit'] : '—')) ?></td>
                        <td><span class="badge bg-<?= $r['condition_status']==='Good'?'success':($r['condition_status']==='Fair'?'warning':'danger') ?>">
                            <?= e($r['condition_status']) ?></span></td>
                        <td><?= e((isset($r['location']) ? $r['location'] : '—')) ?></td>
                        <td><?= fmtDate($r['acquired_date']) ?></td>
                        <td class="text-nowrap">
                            <?php if ($showArchived): ?>
                            <a href="?restore=<?= $r['id'] ?>" class="btn btn-sm btn-outline-success" title="Restore"
                               data-confirm="Restore this resource item?"><i class="bi bi-arrow-counterclockwise"></i></a>
                            <?php else: ?>
                            <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="?archive=<?= $r['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Archive"
                               data-confirm="Archive this resource item?"><i class="bi bi-archive"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this resource item?"><i class="bi bi-trash"></i></a>
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
