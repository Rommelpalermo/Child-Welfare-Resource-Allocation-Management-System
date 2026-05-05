<?php
// Determine active page for nav highlighting
$currentFile = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
$role        = (isset($_SESSION['role']) ? $_SESSION['role'] : '');
$base        = ($role === 'admin') ? BASE_URL . '/admin' : BASE_URL . '/staff';

function navLink($href, $icon, $label, $currentFile, $checkFile) {
    $active = ($currentFile === $checkFile) ? 'active' : '';
    return "<a href=\"{$href}\" class=\"nav-link {$active}\"><i class=\"bi bi-{$icon} me-2\"></i>{$label}</a>";
}
?>
<!-- Topbar -->
<nav class="navbar navbar-expand-lg topbar px-3 py-2">
    <button class="btn btn-sm btn-outline-light me-3" id="sidebarToggle">
        <i class="bi bi-list fs-5"></i>
    </button>
    <a class="navbar-brand text-white fw-bold d-flex align-items-center gap-2" href="#">
        <i class="bi bi-shield-heart text-warning"></i>
        <span>Bahay Pag-asa</span>
        <span class="badge bg-warning text-dark small"><?= strtoupper(e($role)) ?></span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-3">
        <span class="text-white-50 small d-none d-md-inline">
            <i class="bi bi-person-circle me-1"></i><?= e((isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '')) ?>
        </span>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-light">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</nav>

<div class="wrapper d-flex">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header text-center py-4">
            <i class="bi bi-shield-heart text-warning" style="font-size:2.5rem"></i>
            <div class="text-white fw-bold mt-2">Bahay Pag-asa</div>
            <div class="text-white-50 small">San Antonio, Biñan</div>
        </div>

        <ul class="nav flex-column px-2 pb-4">
        <?php if ($role === 'admin'): ?>
            <li class="nav-item">
                <?= navLink($base . '/dashboard.php', 'speedometer2', 'Dashboard', $currentFile, 'dashboard.php') ?>
            </li>
            <li class="nav-section-title">MANAGEMENT</li>
            <li class="nav-item">
                <?= navLink($base . '/accounts/index.php', 'people', 'Account Management', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/children/index.php', 'person-heart', 'Child Records', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/resources/index.php', 'box-seam', 'Resource & Inventory', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/partners/index.php', 'building', 'Partner Information', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/donations/index.php', 'gift', 'Donation Management', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/donation-monitoring/index.php', 'graph-up', 'Donation Monitoring', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-section-title">REPORTS</li>
            <li class="nav-item">
                <?= navLink($base . '/reports/index.php', 'file-earmark-bar-graph', 'Generate Reports', $currentFile, 'index.php') ?>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <?= navLink($base . '/dashboard.php', 'speedometer2', 'Dashboard', $currentFile, 'dashboard.php') ?>
            </li>
            <li class="nav-section-title">MY TASKS</li>
            <li class="nav-item">
                <?= navLink($base . '/children/index.php', 'person-heart', 'View Child Records', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/donations/index.php', 'gift', 'Donation Management', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-item">
                <?= navLink($base . '/cases/index.php', 'clipboard2-pulse', 'Update Case Status', $currentFile, 'index.php') ?>
            </li>
            <li class="nav-section-title">REPORTS</li>
            <li class="nav-item">
                <?= navLink($base . '/reports/index.php', 'file-earmark-bar-graph', 'Generate Reports', $currentFile, 'index.php') ?>
            </li>
        <?php endif; ?>
        </ul>
    </nav>

    <!-- Main content wrapper -->
    <div class="main-content flex-grow-1">
