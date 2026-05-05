<?php
$flash = getFlash();
if ($flash):
    $cls = $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'danger');
?>
<div class="flash-container">
    <div class="alert alert-<?= $cls ?> alert-dismissible fade show shadow" role="alert">
        <i class="bi bi-<?= $cls === 'success' ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
        <?= e($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>
