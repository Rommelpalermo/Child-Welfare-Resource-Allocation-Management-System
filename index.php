<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'staff/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((isset($_POST['username']) ? $_POST['username'] : ''));
    $password = (isset($_POST['password']) ? $_POST['password'] : '');

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, full_name, username, password, role, status FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            logActivity('User logged in', 'Auth');

            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'staff/dashboard.php'));
            exit;
        } else {
            $error = 'Invalid credentials or account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — Bahay Pag-asa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #1a6b3a 0%, #0d4a2a 100%); min-height: 100vh; }
        .login-card { border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,.35); }
        .login-header { background: linear-gradient(135deg, #28a745, #1a6b3a); border-radius: 16px 16px 0 0; padding: 2rem; color: #fff; }
        .login-header img { width: 72px; height: 72px; border-radius: 50%; border: 3px solid rgba(255,255,255,.5); }
        .btn-login { background: linear-gradient(135deg, #28a745, #1a6b3a); border: none; font-weight: 600; letter-spacing: .5px; }
        .btn-login:hover { background: linear-gradient(135deg, #218838, #145228); }
        .form-control:focus { border-color: #28a745; box-shadow: 0 0 0 .2rem rgba(40,167,69,.25); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">
<div class="container" style="max-width:440px">
    <div class="card login-card">
        <div class="login-header text-center">
            <div class="mb-3">
                <i class="bi bi-shield-heart" style="font-size:3.5rem;opacity:.9"></i>
            </div>
            <h4 class="fw-bold mb-0">Bahay Pag-asa</h4>
            <small class="opacity-75">Child Welfare &amp; Resource Allocation System</small><br>
            <small class="opacity-60">San Antonio, Biñan City Laguna</small>
        </div>
        <div class="card-body p-4">
            <h5 class="text-center text-muted mb-4">Sign In to Your Account</h5>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= e($error) ?>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
            <div class="alert alert-warning">You are not authorized to access that page.</div>
            <?php endif; ?>

            <form method="POST" novalidate>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username"
                               value="<?= e((isset($_POST['username']) ? $_POST['username'] : '')) ?>" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="passwordField" class="form-control" placeholder="Enter password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePw()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-login btn-success w-100 py-2 text-white">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted small mb-0">
                <strong>Demo credentials:</strong><br>
                Admin: <code>admin</code> / <code>password</code><br>
                Staff:&nbsp; <code>staff</code> / <code>password</code>
            </p>
        </div>
    </div>
    <p class="text-center text-white opacity-50 mt-3 small">&copy; <?= date('Y') ?> Bahay Pag-asa. All rights reserved.</p>
</div>
<script>
function togglePw() {
    const f = document.getElementById('passwordField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
