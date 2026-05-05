<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('staff');

$db = getDB();
$uid = (int)$_SESSION['user_id'];

$report_type = (isset($_GET['report_type']) ? $_GET['report_type'] : 'children');
$date_from   = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to     = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$data = [];
$title = '';

switch ($report_type) {
    case 'children':
        $title = 'Child Records Report';
        $data  = $db->query("SELECT case_number, CONCAT(first_name,' ',last_name) full_name,
            gender, case_type, case_status, admission_date
            FROM children ORDER BY case_status, last_name")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'donations':
        $title = 'My Donations Report';
        $data  = $db->query("SELECT donor_name, donation_type, amount, donation_date, status
            FROM donations WHERE received_by=$uid
            AND donation_date BETWEEN '$date_from' AND '$date_to'
            ORDER BY donation_date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'cases':
        $title = 'Case Status Updates Report';
        $data  = $db->query("SELECT CONCAT(c.first_name,' ',c.last_name) child_name,
            h.old_status, h.new_status, h.remarks, h.changed_at
            FROM case_status_history h
            LEFT JOIN children c ON c.id=h.child_id
            WHERE h.changed_by=$uid
            ORDER BY h.changed_at DESC")->fetch_all(MYSQLI_ASSOC);
        break;
}

$pageTitle = 'Generate Reports';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-file-earmark-bar-graph me-2 text-success"></i>Generate Reports</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol></nav>
        </div>
        <button class="btn btn-outline-success btn-print"><i class="bi bi-printer me-1"></i>Print</button>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select">
                        <option value="children" <?= $report_type==='children'?'selected':'' ?>>Child Records</option>
                        <option value="donations" <?= $report_type==='donations'?'selected':'' ?>>My Donations</option>
                        <option value="cases" <?= $report_type==='cases'?'selected':'' ?>>Case Updates</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="<?= e($date_from) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="<?= e($date_to) ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-search me-1"></i>Generate</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2"></i><?= e($title) ?></span>
            <small><?= count($data) ?> record(s)</small>
        </div>
        <div class="card-body">
            <?php if (empty($data)): ?>
            <p class="text-center text-muted py-4">No records found.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead class="table-success">
                        <tr><?php foreach (array_keys($data[0]) as $col): ?>
                            <th><?= ucwords(str_replace('_',' ',$col)) ?></th>
                        <?php endforeach; ?></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr><?php foreach ($row as $val): ?>
                        <td><?= e((string)((isset($val) ? $val : '—'))) ?></td>
                    <?php endforeach; ?></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
