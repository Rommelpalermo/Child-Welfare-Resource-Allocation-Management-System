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

$stmt = $db->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) { header('Location: index.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim((isset($_POST['full_name']) ? $_POST['full_name'] : ''));
    $email     = trim((isset($_POST['email']) ? $_POST['email'] : ''));
    $contact   = trim((isset($_POST['contact']) ? $_POST['contact'] : ''));
    $role      = (isset($_POST['role']) ? $_POST['role'] : 'staff');
    $status    = (isset($_POST['status']) ? $_POST['status'] : 'active');
    $password  = (isset($_POST['password']) ? $_POST['password'] : '');
    $confirm   = (isset($_POST['confirm']) ? $_POST['confirm'] : '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== '' && $password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET full_name=?,role=?,email=?,contact=?,status=?,password=? WHERE id=?");
            $stmt->bind_param('ssssssi', $full_name, $role, $email, $contact, $status, $hash, $id);
        } else {
            $stmt = $db->prepare("UPDATE users SET full_name=?,role=?,email=?,contact=?,status=? WHERE id=?");
            $stmt->bind_param('sssssi', $full_name, $role, $email, $contact, $status, $id);
        }
        $stmt->execute();
        $stmt->close();
        logActivity("Updated user ID $id", 'Accounts');
        setFlash('success', 'Account updated successfully.');
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Edit Account';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil me-2 text-success"></i>Edit Account</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Accounts</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".e($e)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-person-gear me-2"></i>Edit: <?= e($user['username']) ?></div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control"
                               value="<?= e(isset($_POST['full_name']) ? $_POST['full_name'] : $user['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="<?= e($user['username']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= e(isset($_POST['email']) ? $_POST['email'] : $user['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact No.</label>
                        <input type="text" name="contact" class="form-control"
                               value="<?= e(isset($_POST['contact']) ? $_POST['contact'] : $user['contact']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="staff" <?= ($user['role']==='staff'?'selected':'') ?>>Staff</option>
                            <option value="admin" <?= ($user['role']==='admin'?'selected':'') ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">New Password <small class="text-muted">(leave blank to keep)</small></label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active"   <?= ($user['status']==='active'?'selected':'') ?>>Active</option>
                            <option value="inactive" <?= ($user['status']==='inactive'?'selected':'') ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Update Account</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
