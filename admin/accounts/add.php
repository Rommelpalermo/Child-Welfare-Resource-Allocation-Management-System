<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim((isset($_POST['full_name']) ? $_POST['full_name'] : ''));
    $username  = trim((isset($_POST['username']) ? $_POST['username'] : ''));
    $email     = trim((isset($_POST['email']) ? $_POST['email'] : ''));
    $contact   = trim((isset($_POST['contact']) ? $_POST['contact'] : ''));
    $role      = (isset($_POST['role']) ? $_POST['role'] : 'staff');
    $status    = (isset($_POST['status']) ? $_POST['status'] : 'active');
    $password  = (isset($_POST['password']) ? $_POST['password'] : '');
    $confirm   = (isset($_POST['confirm']) ? $_POST['confirm'] : '');

    if ($full_name === '') $errors[] = 'Full name is required.';
    if ($username  === '') $errors[] = 'Username is required.';
    if ($password  === '') $errors[] = 'Password is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check duplicate username
    $stmt = $db->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) $errors[] = 'Username already exists.';
    $stmt->close();

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (full_name,username,password,role,email,contact,status) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssss', $full_name, $username, $hash, $role, $email, $contact, $status);
        $stmt->execute();
        $stmt->close();
        logActivity("Created user: $username", 'Accounts');
        setFlash('success', 'Account created successfully.');
        header('Location: index.php');
        exit;
    }
}

$pageTitle = 'Add Account';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-person-plus me-2 text-success"></i>Add Account</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="index.php">Accounts</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>".e($e)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-person-plus me-2"></i>New User Account</div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?= e((isset($_POST['full_name']) ? $_POST['full_name'] : '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= e((isset($_POST['username']) ? $_POST['username'] : '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= e((isset($_POST['email']) ? $_POST['email'] : '')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact No.</label>
                        <input type="text" name="contact" class="form-control" value="<?= e((isset($_POST['contact']) ? $_POST['contact'] : '')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select">
                            <option value="staff" <?= ((isset($_POST['role']) ? $_POST['role'] : ''))==='staff'?'selected':'' ?>>Staff</option>
                            <option value="admin" <?= ((isset($_POST['role']) ? $_POST['role'] : ''))==='admin'?'selected':'' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="6">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Create Account</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
