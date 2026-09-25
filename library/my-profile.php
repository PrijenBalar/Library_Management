<?php
require_once 'includes/functions.php';
require_student();

$pk = (int)$_SESSION['student_pk'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $mobile   = trim($_POST['mobile'] ?? '');

    if ($fullname === '' || $mobile === '') {
        $error = 'Full name and mobile number are required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = 'Mobile number must be exactly 10 digits.';
    } else {
        $stmt = $con->prepare('UPDATE tblstudents SET FullName = ?, MobileNumber = ? WHERE id = ?');
        $stmt->bind_param('ssi', $fullname, $mobile, $pk);
        $stmt->execute();
        $stmt->close();
        $_SESSION['student_name'] = $fullname;
        set_flash('success', 'Profile updated successfully.');
        redirect('my-profile.php');
    }
}

$stmt = $con->prepare('SELECT * FROM tblstudents WHERE id = ?');
$stmt->bind_param('i', $pk);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'My Profile';
include 'includes/header.php';
?>
<div class="card" style="max-width:640px;margin:0 auto">
    <div class="card-header"><h2>My Profile</h2><span class="badge badge-info">Registered on <?php echo format_d($student['RegDate']); ?></span></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="my-profile.php">
        <div class="form-row">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" value="<?php echo e($student['StudentId']); ?>" readonly>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?php echo e($student['EmailId']); ?>" readonly>
            </div>
        </div>
        <div class="form-group">
            <label for="fullname">Full Name <span class="req">*</span></label>
            <input type="text" id="fullname" name="fullname" value="<?php echo e($student['FullName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="mobile">Mobile Number <span class="req">*</span></label>
            <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" value="<?php echo e($student['MobileNumber']); ?>" required>
        </div>
        <div class="form-group">
            <label>Account Status</label>
            <div><?php echo (int)$student['Status'] === 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Blocked</span>'; ?></div>
        </div>
        <button type="submit" class="btn">Update Profile</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
