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
    $db->query("DELETE FROM children WHERE id=$id");
    setFlash('success', 'Child record deleted.');
    logActivity("Deleted child ID $id", 'Children');
    header('Location: index.php'); exit;
}

$children = $db->query("SELECT c.*, u.full_name staff_name FROM children c LEFT JOIN users u ON u.id=c.created_by ORDER BY c.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Child Records';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
    <div class="page-header">
        <div>
            <h4><i class="bi bi-person-heart me-2 text-success"></i>Child Records</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Children</li>
            </ol></nav>
        </div>
        <a href="add.php" class="btn btn-success"><i class="bi bi-plus-lg me-1"></i>Add Child Record</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-person-heart me-2"></i>All Child Records</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Case No.</th><th>Name</th><th>Gender</th><th>Admission</th>
                        <th>Case Type</th><th>Status</th><th>Guardian</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($children as $i => $c): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= e((isset($c['case_number']) ? $c['case_number'] : '—')) ?></td>
                        <td class="fw-semibold"><?= e($c['first_name'].' '.$c['last_name']) ?></td>
                        <td><?= e((isset($c['gender']) ? $c['gender'] : '—')) ?></td>
                        <td><?= fmtDate($c['admission_date']) ?></td>
                        <td><?= e((isset($c['case_type']) ? $c['case_type'] : '—')) ?></td>
                        <td><span class="badge bg-<?= $c['case_status']==='Active'?'success':($c['case_status']==='Closed'?'danger':'warning') ?>">
                            <?= e($c['case_status']) ?></span></td>
                        <td><?= e((isset($c['guardian_name']) ? $c['guardian_name'] : '—')) ?></td>
                        <td class="text-nowrap">
                            <a href="view.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete this child record?"><i class="bi bi-trash"></i></a>
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
