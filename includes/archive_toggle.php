<?php
// Expects $showArchived (bool). Renders the Active / Archived switch.
?>
<div class="btn-group mb-3" role="group">
    <a href="index.php" class="btn btn-sm <?= $showArchived ? 'btn-outline-success' : 'btn-success' ?>"><i class="bi bi-list-ul me-1"></i>Active</a>
    <a href="index.php?view=archived" class="btn btn-sm <?= $showArchived ? 'btn-secondary' : 'btn-outline-secondary' ?>"><i class="bi bi-archive me-1"></i>Archived</a>
</div>
