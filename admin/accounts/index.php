<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) {
        $db->query("DELETE FROM users WHERE id=$id");
        setFlash('success', 'Account deleted successfully.');
        logActivity("Deleted user ID $id", 'Accounts');
    } else {
        setFlash('warning', 'You cannot delete your own account.');
    }
    header('Location: index.php');
    exit;
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$pageTitle = 'Account Management';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>

    <div class="page-header">
        <div>
            <h4><i class="bi bi-people me-2 text-success"></i>Account Management</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Accounts</li>
                </ol>
            </nav>
        </div>
        <a href="add.php" class="btn btn-success">
            <i class="bi bi-person-plus me-1"></i>Add Account
        </a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-people me-2"></i>All User Accounts</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table datatable table-hover">
                    <thead><tr>
                        <th>#</th><th>Full Name</th><th>Username</th><th>Role</th>
                        <th>Email</th><th>Contact</th><th>Status</th><th>Created</th><th>Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= e($u['full_name']) ?></td>
                        <td><?= e($u['username']) ?></td>
                        <td><span class="badge bg-<?= $u['role']==='admin'?'danger':'primary' ?>"><?= ucfirst(e($u['role'])) ?></span></td>
                        <td><?= e((isset($u['email']) ? $u['email'] : '—')) ?></td>
                        <td><?= e((isset($u['contact']) ? $u['contact'] : '—')) ?></td>
                        <td><span class="badge bg-<?= $u['status']==='active'?'success':'secondary' ?>"><?= ucfirst(e($u['status'])) ?></span></td>
                        <td><?= fmtDate($u['created_at']) ?></td>
                        <td class="text-nowrap">
                            <a href="edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <a href="?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger"
                               data-confirm="Delete account '<?= e($u['username']) ?>'?"
                            ><i class="bi bi-trash"></i></a>
                            <?php endif; ?>
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
