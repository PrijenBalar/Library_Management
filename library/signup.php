<?php
require_once 'includes/functions.php';
if (is_student_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';
$newStudentId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = strtolower(trim($_POST['email'] ?? ''));
    $mobile   = trim($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirmpassword'] ?? '';

    if ($fullname === '' || $email === '' || $mobile === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $error = 'Mobile number must be exactly 10 digits.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Password and Confirm Password do not match.';
    } else {
        // Check duplicate email
        $stmt = $con->prepare('SELECT id FROM tblstudents WHERE EmailId = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'This email is already registered. Please login.';
        } else {
            $studentId = generate_student_id($con);
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $con->prepare('INSERT INTO tblstudents (StudentId, FullName, EmailId, MobileNumber, Password, Status) VALUES (?, ?, ?, ?, ?, 1)');
            $stmt->bind_param('sssss', $studentId, $fullname, $email, $mobile, $hash);
            if ($stmt->execute()) {
                $newStudentId = $studentId;
                $success = 'Registration successful! Your Student ID is <strong>' . e($studentId) . '</strong>. Please note it down, you will need it to login.';
                $_POST = [];
            } else {
                $error = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
    }
}

$pageTitle = 'Student Registration';
include 'includes/header.php';
?>
<div class="card auth-card" style="max-width:560px">
    <h2>Student Registration</h2>
    <p class="sub">Create your library account. A Student ID will be generated for you.</p>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <a href="index.php" class="btn btn-block">Go to Login</a>
    <?php else: ?>
    <form method="post" action="signup.php" class="check-password" autocomplete="off">
        <div class="form-group">
            <label for="fullname">Full Name <span class="req">*</span></label>
            <input type="text" id="fullname" name="fullname" value="<?php echo e($_POST['fullname'] ?? ''); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email <span class="req">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="mobile">Mobile Number <span class="req">*</span></label>
                <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" placeholder="10 digit number" value="<?php echo e($_POST['mobile'] ?? ''); ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password <span class="req">*</span></label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" minlength="6" required>
                    <button type="button" class="pw-toggle">Show</button>
                </div>
                <div class="help">Minimum 6 characters</div>
            </div>
            <div class="form-group">
                <label for="confirmpassword">Confirm Password <span class="req">*</span></label>
                <input type="password" id="confirmpassword" name="confirmpassword" minlength="6" required>
            </div>
        </div>
        <button type="submit" class="btn btn-block">Register</button>
    </form>
    <div class="auth-links">Already registered? <a href="index.php">Login here</a></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
