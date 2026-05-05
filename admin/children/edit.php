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

$stmt = $db->prepare("SELECT * FROM children WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$child = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$child) { header('Location: index.php'); exit; }

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
        $stmt = $db->prepare("UPDATE children SET case_number=?,first_name=?,last_name=?,middle_name=?,
            birth_date=?,gender=?,address=?,guardian_name=?,guardian_contact=?,
            admission_date=?,case_type=?,case_status=?,notes=? WHERE id=?");
        $stmt->bind_param('sssssssssssssi',
            $data['case_number'],$data['first_name'],$data['last_name'],$data['middle_name'],
            $data['birth_date'],$data['gender'],$data['address'],$data['guardian_name'],
            $data['guardian_contact'],$data['admission_date'],$data['case_type'],
            $data['case_status'],$data['notes'],$id);
        $stmt->execute();
        $stmt->close();

        // Log status change if changed
        if ($data['case_status'] !== $child['case_status']) {
            $uid = $_SESSION['user_id'];
            $remarks = 'Status updated via edit form.';
            $hStmt = $db->prepare("INSERT INTO case_status_history (child_id,old_status,new_status,remarks,changed_by) VALUES (?,?,?,?,?)");
            $hStmt->bind_param('isssi', $id, $child['case_status'], $data['case_status'], $remarks, $uid);
            $hStmt->execute();
            $hStmt->close();
        }

        logActivity("Updated child ID $id", 'Children');
        setFlash('success', 'Child record updated successfully.');
        header('Location: index.php'); exit;
    }
}

$d = array_merge($child, $_POST);
$pageTitle = 'Edit Child Record';
include '../../includes/header.php';
?>
<?php include '../../includes/sidebar.php'; ?>

<div class="container-fluid px-4 py-3">
    <div class="page-header">
        <div>
            <h4><i class="bi bi-pencil me-2 text-success"></i>Edit Child Record</h4>
            <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="index.php">Children</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol></nav>
        </div>
    </div>

    <?php if ($errors): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err) echo "<li>".e($err)."</li>"; ?></ul></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header"><i class="bi bi-clipboard2-check me-2"></i>Edit: <?= e($child['first_name'].' '.$child['last_name']) ?></div>
        <div class="card-body">
            <form method="POST">
                <h6 class="text-muted fw-semibold mb-3 border-bottom pb-2">Personal Information</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Case Number</label>
                        <input type="text" name="case_number" class="form-control" value="<?= e((isset($d['case_number']) ? $d['case_number'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control" value="<?= e((isset($d['first_name']) ? $d['first_name'] : '')) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control" value="<?= e((isset($d['middle_name']) ? $d['middle_name'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control" value="<?= e((isset($d['last_name']) ? $d['last_name'] : '')) ?>" required></div>
                    <div class="col-md-4"><label class="form-label">Gender</label>
                        <select name="gender" class="form-select">
                            <?php foreach(['Male','Female','Other'] as $g): ?>
                            <option value="<?= $g ?>" <?= ((isset($d['gender']) ? $d['gender'] : ''))===$g?'selected':'' ?>><?= $g ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-md-4"><label class="form-label">Birth Date</label>
                        <input type="date" name="birth_date" class="form-control" value="<?= e((isset($d['birth_date']) ? $d['birth_date'] : '')) ?>"></div>
                    <div class="col-12"><label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= e((isset($d['address']) ? $d['address'] : '')) ?></textarea></div>
                </div>

                <h6 class="text-muted fw-semibold mb-3 mt-4 border-bottom pb-2">Guardian / Case Information</h6>
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label">Guardian Name</label>
                        <input type="text" name="guardian_name" class="form-control" value="<?= e((isset($d['guardian_name']) ? $d['guardian_name'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Guardian Contact</label>
                        <input type="text" name="guardian_contact" class="form-control" value="<?= e((isset($d['guardian_contact']) ? $d['guardian_contact'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Admission Date</label>
                        <input type="date" name="admission_date" class="form-control" value="<?= e((isset($d['admission_date']) ? $d['admission_date'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Case Type</label>
                        <input type="text" name="case_type" class="form-control" value="<?= e((isset($d['case_type']) ? $d['case_type'] : '')) ?>"></div>
                    <div class="col-md-4"><label class="form-label">Case Status</label>
                        <select name="case_status" class="form-select">
                            <?php foreach(['Active','Closed','Referred','Reunified'] as $s): ?>
                            <option value="<?= $s ?>" <?= ((isset($d['case_status']) ? $d['case_status'] : ''))===$s?'selected':'' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="col-12"><label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= e((isset($d['notes']) ? $d['notes'] : '')) ?></textarea></div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Update Record</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/sidebar_close.php'; ?>
<?php include '../../includes/footer.php'; ?>
