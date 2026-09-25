<?php
require_once '../includes/functions.php';
if (is_admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $con->prepare('SELECT id, FullName, UserName, Password FROM admin WHERE UserName = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['Password'])) {
            $_SESSION['admin_id']   = $row['id'];
            $_SESSION['admin_name'] = $row['FullName'];
            set_flash('success', 'Welcome, ' . $row['FullName'] . '!');
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}

$pageTitle = 'Admin Login';
include 'includes/header.php';
?>
<div class="card auth-card" style="margin-top:60px">
    <h2>&#128274; Admin Login</h2>
    <p class="sub">Library Management System - Admin Panel</p>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="index.php" autocomplete="off">
        <div class="form-group">
            <label for="username">Username <span class="req">*</span></label>
            <input type="text" id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password <span class="req">*</span></label>
            <div class="pw-wrap">
                <input type="password" id="password" name="password" required>
                <button type="button" class="pw-toggle">Show</button>
            </div>
        </div>
        <button type="submit" class="btn btn-block">Login</button>
    </form>
    <div class="auth-links"><a href="../index.php">&larr; Back to Student Website</a></div>
</div>
<?php include 'includes/footer.php'; ?>
