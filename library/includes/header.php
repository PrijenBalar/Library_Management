<?php
if (!isset($pageTitle)) { $pageTitle = 'Home'; }
$currentPage = basename($_SERVER['PHP_SELF']);
$loggedIn = is_student_logged_in();

/** Sidebar link: adds the "active" class when the current page is one of $files */
function side_link($href, $label, $iconName, $files, $current, $extra = '')
{
    $active = in_array($current, (array)$files, true) ? ' class="active"' : '';
    return '<a href="' . $href . '"' . $active . '>' . icon($iconName, 19) . '<span>' . $label . '</span>' . $extra . '</a>';
}
/** Initials for the avatar circle */
function initials($name)
{
    $parts = preg_split('/\s+/', trim((string)$name));
    $out = strtoupper(substr($parts[0] ?? '', 0, 1) . substr($parts[1] ?? '', 0, 1));
    return $out !== '' ? $out : 'U';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> | Online Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php if ($loggedIn): $cartN = cart_count($con, $_SESSION['student_id']); ?>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="side-brand">
            <span class="logo-badge"><?php echo icon('logo', 26); ?></span>
            <span>Online Library<small>Student Portal</small></span>
        </a>
        <div class="side-user">
            <div class="avatar"><?php echo e(initials($_SESSION['student_name'])); ?></div>
            <div><div class="name"><?php echo e($_SESSION['student_name']); ?></div><div class="role"><?php echo e($_SESSION['student_id']); ?></div></div>
        </div>
        <nav class="side-nav">
            <?php echo side_link('dashboard.php', 'Dashboard', 'grid', 'dashboard.php', $currentPage); ?>
            <div class="side-label">Library</div>
            <?php echo side_link('listed-books.php', 'Listed Books', 'book', 'listed-books.php', $currentPage); ?>
            <?php echo side_link('issued-books.php', 'Issued Books', 'recycle', 'issued-books.php', $currentPage); ?>
            <div class="side-label">Book Store</div>
            <?php echo side_link('buy-books.php', 'Buy Books', 'store', 'buy-books.php', $currentPage); ?>
            <?php echo side_link('cart.php', 'My Cart', 'cart', ['cart.php', 'checkout.php'], $currentPage, '<span class="pill" id="cartCount">' . $cartN . '</span>'); ?>
            <?php echo side_link('my-orders.php', 'My Orders', 'box', ['my-orders.php', 'order-details.php'], $currentPage); ?>
            <div class="side-label">Account</div>
            <?php echo side_link('my-profile.php', 'My Profile', 'user', 'my-profile.php', $currentPage); ?>
            <?php echo side_link('change-password.php', 'Change Password', 'lock', 'change-password.php', $currentPage); ?>
            <a href="logout.php" class="logout-link"><?php echo icon('logout', 19); ?><span>Logout</span></a>
        </nav>
    </aside>
    <div class="main">
        <header class="topbar">
            <button class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="Menu"><?php echo icon('menu', 20); ?></button>
            <h1 class="page-title"><span class="crumb">Student Portal</span><?php echo e($pageTitle); ?></h1>
            <div class="top-actions">
                <a href="cart.php" class="icon-btn" title="My Cart"><?php echo icon('cart', 20); ?><span class="dot" id="cartCountTop"><?php echo $cartN; ?></span></a>
                <div class="top-user">
                    <div class="avatar"><?php echo e(initials($_SESSION['student_name'])); ?></div>
                    <div class="who"><strong><?php echo e($_SESSION['student_name']); ?></strong><small><?php echo e($_SESSION['student_id']); ?></small></div>
                </div>
                <a href="logout.php" class="btn btn-sm btn-logout"><?php echo icon('logout', 16); ?> Logout</a>
            </div>
        </header>
        <main class="content">
<?php else: ?>
<header class="guest-nav">
    <div class="container">
        <a href="index.php" class="brand">
            <span class="logo-badge"><?php echo icon('logo', 24); ?></span>
            <span>Online Library<small>Management System</small></span>
        </a>
        <nav class="guest-links">
            <a href="index.php"<?php echo $currentPage === 'index.php' ? ' class="active"' : ''; ?>>Home</a>
            <a href="index.php#login">Login</a>
            <a href="signup.php"<?php echo $currentPage === 'signup.php' ? ' class="active"' : ''; ?>>Register</a>
            <a href="admin/index.php" class="btn btn-sm">Admin Panel</a>
        </nav>
    </div>
</header>
<main class="container guest-main">
<?php endif; ?>
<?php show_flash(); ?>
