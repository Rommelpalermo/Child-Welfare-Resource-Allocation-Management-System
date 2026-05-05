<?php
session_start();
define('BASE_URL', '../..');
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';
requireRole('admin');

$db = getDB();

// Filter params
$report_type = (isset($_GET['report_type']) ? $_GET['report_type'] : 'children');
$date_from   = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to     = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

$data = [];
$title = '';

switch ($report_type) {
    case 'children':
        $title = 'Child Records Report';
        $data  = $db->query("SELECT c.case_number, CONCAT(c.first_name,' ',c.last_name) full_name,
            c.gender, c.case_type, c.case_status, c.admission_date, c.guardian_name
            FROM children c ORDER BY c.case_status, c.last_name")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'donations':
        $title = 'Donations Report';
        $data  = $db->query("SELECT d.donor_name, d.donation_type, d.amount, d.donation_date, d.status,
            u.full_name receiver
            FROM donations d LEFT JOIN users u ON u.id=d.received_by
            WHERE d.donation_date BETWEEN '$date_from' AND '$date_to'
            ORDER BY d.donation_date DESC")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'resources':
        $title = 'Resource & Inventory Report';
        $data  = $db->query("SELECT item_name, category, quantity, unit, condition_status, location
            FROM resources ORDER BY category, item_name")->fetch_all(MYSQLI_ASSOC);
        break;
    case 'partners':
        $title = 'Partner Organizations Report';
        $data  = $db->query("SELECT org_name, contact_person, contact_email, contact_phone, partnership_type, status
            FROM partners ORDER BY status, org_name")->fetch_all(MYSQLI_ASSOC);
        break;
}

$pageTitle = 'Generate Reports';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <?php include '../../includes/flash.php'; ?>
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

    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Report Type</label>
                    <select name="report_type" class="form-select">
                        <?php foreach(['children'=>'Child Records','donations'=>'Donations','resources'=>'Resource & Inventory','partners'=>'Partner Organizations'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= $report_type===$k?'selected':'' ?>><?= $v ?></option>
                        <?php endforeach; ?>
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

    <!-- Report Output -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-table me-2"></i><?= e($title) ?></span>
            <small><?= fmtDate($date_from) ?> — <?= fmtDate($date_to) ?> &nbsp;|&nbsp; <?= count($data) ?> record(s)</small>
        </div>
        <div class="card-body">
            <?php if (empty($data)): ?>
            <p class="text-center text-muted py-4">No records found for the selected filters.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-bordered table-sm">
                    <thead class="table-success">
                        <tr><?php foreach (array_keys($data[0]) as $col): ?>
                            <th><?= ucwords(str_replace('_',' ', $col)) ?></th>
                        <?php endforeach; ?></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($row as $val): ?>
                        <td><?= e((string)((isset($val) ? $val : '—'))) ?></td>
                        <?php endforeach; ?>
                    </tr>
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
