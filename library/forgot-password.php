<?php
require_once 'includes/functions.php';
if (is_student_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $mobile   = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirmpassword'] ?? '';

    if ($email === '' || $mobile === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'New Password and Confirm Password do not match.';
    } else {
        // Verify identity using registered email + mobile number
        $stmt = $con->prepare('SELECT id, StudentId FROM tblstudents WHERE EmailId = ? AND MobileNumber = ? LIMIT 1');
        $stmt->bind_param('ss', $email, $mobile);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $error = 'No account found with this email and mobile number combination.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare('UPDATE tblstudents SET Password = ? WHERE id = ?');
            $stmt->bind_param('si', $hash, $row['id']);
            $stmt->execute();
            $stmt->close();
            $success = 'Password reset successfully for Student ID <strong>' . e($row['StudentId']) . '</strong>. You can now login with your new password.';
        }
    }
}

$pageTitle = 'Recover Password';
include 'includes/header.php';
?>
<div class="card auth-card">
    <h2>Recover Password</h2>
    <p class="sub">Enter your registered email and mobile number to set a new password</p>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <a href="index.php" class="btn btn-block">Go to Login</a>
    <?php else: ?>
    <form method="post" action="forgot-password.php" class="check-password" autocomplete="off">
        <div class="form-group">
            <label for="email">Registered Email <span class="req">*</span></label>
            <input type="email" id="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="mobile">Registered Mobile Number <span class="req">*</span></label>
            <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" value="<?php echo e($_POST['mobile'] ?? ''); ?>" required>
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
        <button type="submit" class="btn btn-block">Reset Password</button>
    </form>
    <div class="auth-links"><a href="index.php">Back to Login</a></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
