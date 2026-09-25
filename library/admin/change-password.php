<?php
require_once '../includes/functions.php';
require_admin();

$adminId = (int)$_SESSION['admin_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['currentpassword'] ?? '';
    $new     = $_POST['password'] ?? '';
    $confirm = $_POST['confirmpassword'] ?? '';

    $stmt = $con->prepare('SELECT Password FROM admin WHERE id = ?');
    $stmt->bind_param('i', $adminId);
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
        $stmt = $con->prepare('UPDATE admin SET Password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $adminId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Admin password changed successfully.');
        redirect('change-password.php');
    }
}

$pageTitle = 'Change Password';
include 'includes/header.php';
?>
<div class="card" style="max-width:520px">
    <div class="card-header"><h3>Change Admin Password</h3></div>
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
        <button type="submit" class="btn">Change Password</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
