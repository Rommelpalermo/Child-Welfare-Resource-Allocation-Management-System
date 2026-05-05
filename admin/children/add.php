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
    $fields = ['first_name','last_name','middle_name','gender','birth_date','address',
               'guardian_name','guardian_contact','admission_date','case_number',
               'case_type','case_status','notes'];
    $data = [];
    foreach ($fields as $f) $data[$f] = trim(isset($_POST[$f]) ? $_POST[$f] : '');

    if ($data['first_name'] === '') $errors[] = 'First name is required.';
    if ($data['last_name']  === '') $errors[] = 'Last name is required.';

    if (empty($errors)) {
        $uid = $_SESSION['user_id'];
        $stmt = $db->prepare("INSERT INTO children (case_number,first_name,last_name,middle_name,birth_date,gender,
            address,guardian_name,guardian_contact,admission_date,case_type,case_status,notes,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssssssssi',
            $data['case_number'],$data['first_name'],$data['last_name'],$data['middle_name'],
            $data['birth_date'],$data['gender'],$data['address'],$data['guardian_name'],
            $data['guardian_contact'],$data['admission_date'],$data['case_type'],
            $data['case_status'],$data['notes'],$uid);
        $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        // Initial status history
        $statusStmt = $db->prepare("INSERT INTO case_status_history (child_id,new_status,remarks,changed_by) VALUES (?,?,?,?)");
        $remarks = 'Initial record creation.';
        $statusStmt->bind_param('issi', $newId, $data['case_status'], $remarks, $uid);
        $statusStmt->execute();
        $statusStmt->close();

        logActivity("Added child: {$data['first_name']} {$data['last_name']}", 'Children');
        setFlash('success', 'Child record added successfully.');
        header('Location: index.php'); exit;
    }
}

$pageTitle = 'Add Child Record';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-person-plus me-2 text-success"></i>Add Child Record</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Children</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-clipboard2-plus me-2"></i>Child Information</div>
        <div class="card-body">
            <form method="POST">
                <h6 class="text-muted fw-semibold mb-3 border-bottom pb-2">Personal Information</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Case Number</label>
                        <input type="text" name="case_number" class="form-control" value="<?= e((isset($_POST['case_number']) ? $_POST['case_number'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="<?= e((isset($_POST['first_name']) ? $_POST['first_name'] : '')) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="<?= e((isset($_POST['middle_name']) ? $_POST['middle_name'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="<?= e((isset($_POST['last_name']) ? $_POST['last_name'] : '')) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" value="<?= e((isset($_POST['birth_date']) ? $_POST['birth_date'] : '')) ?>"></div>
                    <div class="col-12"><label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e((isset($_POST['address']) ? $_POST['address'] : '')) ?></textarea></div>
                </div>

                <h6 class="text-muted fw-semibold mb-3 mt-4 border-bottom pb-2">Guardian / Case Information</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= e((isset($_POST['guardian_name']) ? $_POST['guardian_name'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Guardian Contact</label>
                        <input type="text" name="guardian_contact" class="form-control" value="<?= e((isset($_POST['guardian_contact']) ? $_POST['guardian_contact'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Admission Date</label>
                        <input type="date" name="admission_date" class="form-control" value="<?= e(isset($_POST['admission_date']) ? $_POST['admission_date'] : date('Y-m-d')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Case Type</label>
                        <input type="text" name="case_type" class="form-control" placeholder="e.g. Neglect, Abuse…" value="<?= e((isset($_POST['case_type']) ? $_POST['case_type'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Case Status</label>
                        <select name="case_status" class="form-select">
                            <option value="Active">Active</option>
                            <option value="Closed">Closed</option>
                            <option value="Referred">Referred</option>
                            <option value="Reunified">Reunified</option>
                        </select></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= e((isset($_POST['notes']) ? $_POST['notes'] : '')) ?></textarea></div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Save Record</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
