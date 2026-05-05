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
    $db->query("DELETE FROM partners WHERE id=$id");
    setFlash('success', 'Partner deleted.');
    logActivity("Deleted partner ID $id", 'Partners');
    header('Location: index.php'); exit;
}

$partners = $db->query("SELECT * FROM partners ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Partner Information';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
    <div class="page-header">
        <div>
            <h4><i class="bi bi-building me-2 text-success"></i>Partner Information Management</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Partners</li>
            </ol></nav>
        </div>
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Add Partner</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-building me-2"></i>Partner Organizations</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Organization</th><th>Contact Person</th><th>Email</th>
                        <th>Phone</th><th>Type</th><th>Status</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($partners as $i => $p): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td class="fw-semibold"><?= e($p['org_name']) ?></td>
                        <td><?= e((isset($p['contact_person']) ? $p['contact_person'] : '—')) ?></td>
                        <td><?= e((isset($p['contact_email']) ? $p['contact_email'] : '—')) ?></td>
                        <td><?= e((isset($p['contact_phone']) ? $p['contact_phone'] : '—')) ?></td>
                        <td><?= e((isset($p['partnership_type']) ? $p['partnership_type'] : '—')) ?></td>
                        <td><span class="badge bg-<?= $p['status']==='Active'?'success':'secondary' ?>"><?= e($p['status']) ?></span></td>
                        <td class="text-nowrap">
                            <a href="edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this partner?"><i class="bi bi-trash"></i></a>
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
