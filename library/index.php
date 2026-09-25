<?php
require_once 'includes/functions.php';
if (is_student_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['studentid'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'Please enter your Student ID / Email and password.';
    } else {
        $stmt = $con->prepare('SELECT id, StudentId, FullName, Password, Status FROM tblstudents WHERE StudentId = ? OR EmailId = ? LIMIT 1');
        $stmt->bind_param('ss', $login, $login);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && password_verify($password, $row['Password'])) {
            if ((int)$row['Status'] !== 1) {
                $error = 'Your account has been blocked. Please contact the library admin.';
            } else {
                $_SESSION['student_pk']   = $row['id'];
                $_SESSION['student_id']   = $row['StudentId'];
                $_SESSION['student_name'] = $row['FullName'];
                set_flash('success', 'Welcome back, ' . $row['FullName'] . '!');
                redirect('dashboard.php');
            }
        } else {
            $error = 'Invalid Student ID / Email or password.';
        }
    }
}

// Numbers and books for the home page
$stats = $con->query('SELECT (SELECT COUNT(*) FROM tblbooks) AS books,
                             (SELECT COUNT(*) FROM tblauthors) AS authors,
                             (SELECT COUNT(*) FROM tblcategory WHERE Status = 1) AS cats,
                             (SELECT COUNT(*) FROM tblstudents) AS students')->fetch_assoc();
$books = $con->query('SELECT b.id, b.BookName, b.BookImage, b.BookPrice, b.SellStock, a.AuthorName, c.CategoryName,
                      (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.BookId = b.id AND i.ReturnStatus = 0) AS issued
                      FROM tblbooks b
                      LEFT JOIN tblauthors a ON a.id = b.AuthorId
                      LEFT JOIN tblcategory c ON c.id = b.CatId
                      ORDER BY b.id DESC LIMIT 12')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Home';
$hidePageHead = true;
include 'includes/header.php';
?>
<section class="hero">
    <div>
        <span class="tagline">College Library &middot; Borrow &middot; Buy &middot; Track</span>
        <h1>Your college library, now online.</h1>
        <p>Browse the library collection, borrow books, track your return dates and buy your own copies &mdash; all in one place.</p>
        <p>New here? <a href="signup.php"><strong>Register now</strong></a> and get your Student ID instantly.</p>
        <div class="hero-stats">
            <div><strong><?php echo (int)$stats['books']; ?></strong>Books</div>
            <div><strong><?php echo (int)$stats['authors']; ?></strong>Authors</div>
            <div><strong><?php echo (int)$stats['cats']; ?></strong>Categories</div>
            <div><strong><?php echo (int)$stats['students']; ?></strong>Students</div>
        </div>
    </div>
    <div class="card auth-card" id="login">
        <h2>Student Login</h2>
        <p class="sub">Sign in with your Student ID or Email</p>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
        <form method="post" action="index.php#login" autocomplete="off">
            <div class="form-group">
                <label for="studentid">Student ID / Email <span class="req">*</span></label>
                <input type="text" id="studentid" name="studentid" placeholder="e.g. SID10001" value="<?php echo e($_POST['studentid'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password <span class="req">*</span></label>
                <div class="pw-wrap">
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                    <button type="button" class="pw-toggle">Show</button>
                </div>
            </div>
            <button type="submit" class="btn btn-block">Login</button>
        </form>
        <div class="auth-links">
            <a href="forgot-password.php">Forgot password?</a> &nbsp;|&nbsp; <a href="signup.php">Not registered yet?</a>
        </div>
    </div>
</section>

<h2 class="section-title">Explore Our Collection</h2>
<div class="book-grid">
    <?php foreach ($books as $b): ?>
    <div class="book-card">
        <div class="cover-wrap">
            <?php echo book_cover($b['BookImage'], $b['BookName']); ?>
            <span class="ribbon"><?php echo $b['issued'] ? '<span class="badge badge-danger">Issued</span>' : '<span class="badge badge-success">Available</span>'; ?></span>
        </div>
        <div class="book-body">
            <h3 class="book-title"><?php echo e($b['BookName']); ?></h3>
            <div class="book-meta">by <?php echo e($b['AuthorName']); ?></div>
            <div class="book-meta"><?php echo e($b['CategoryName']); ?></div>
            <div class="book-price"><?php echo money($b['BookPrice']); ?></div>
            <div class="book-meta"><?php echo (int)$b['SellStock'] > 0 ? 'Available to buy' : 'Not for sale right now'; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-center" style="margin-top:24px"><a href="#login" class="btn">Login to borrow or buy books</a></p>
<?php include 'includes/footer.php'; ?>
