<?php
require_once 'includes/functions.php';
require_student();

$pk = (int)$_SESSION['student_pk'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['currentpassword'] ?? '';
    $new     = $_POST['password'] ?? '';
    $confirm = $_POST['confirmpassword'] ?? '';

    $stmt = $con->prepare('SELECT Password FROM tblstudents WHERE id = ?');
    $stmt->bind_param('i', $pk);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($current, $row['Password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($new !== $confirm) {
        $error = 'New Password and Confirm Password do not match.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $con->prepare('UPDATE tblstudents SET Password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $pk);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Password changed successfully.');
        redirect('change-password.php');
    }
}

$pageTitle = 'Change Password';
include 'includes/header.php';
?>
<div class="card auth-card">
    <h2>Change Password</h2>
    <p class="sub">Choose a strong password that you have not used before</p>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="change-password.php" class="check-password" autocomplete="off">
        <div class="form-group">
            <label for="currentpassword">Current Password <span class="req">*</span></label>
            <div class="pw-wrap">
                <input type="password" id="currentpassword" name="currentpassword" required>
                <button type="button" class="pw-toggle">Show</button>
            </div>
        </div>
        <div class="form-group">
            <label for="password">New Password <span class="req">*</span></label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" minlength="6" required>
                <button type="button" class="pw-toggle">Show</button>
            </div>
        </div>
        <div class="form-group">
            <label for="confirmpassword">Confirm New Password <span class="req">*</span></label>
            <input type="password" id="confirmpassword" name="confirmpassword" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-block">Change Password</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
