<?php
if (!isset($pageTitle)) { $pageTitle = 'Admin'; }
$currentPage = basename($_SERVER['PHP_SELF']);
$loggedIn = is_admin_logged_in();

/** Sidebar link: adds the "active" class when the current page is one of $files */
function side_link($href, $label, $iconName, $files, $current, $extra = '')
{
    $active = in_array($current, (array)$files, true) ? ' class="active"' : '';
    return '<a href="' . $href . '"' . $active . '>' . icon($iconName, 19) . '<span>' . $label . '</span>' . $extra . '</a>';
}

$pendingOrders = 0;
if ($loggedIn) {
    $pendingOrders = (int)$con->query("SELECT COUNT(*) AS c FROM tblorders WHERE OrderStatus = 'Pending'")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> | Admin - Online Library Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php if ($loggedIn): ?>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="side-brand">
            <span class="logo-badge"><?php echo icon('logo', 26); ?></span>
            <span>Online Library<small>Admin Panel</small></span>
        </a>
        <div class="side-user">
            <div class="avatar">AD</div>
            <div><div class="name"><?php echo e($_SESSION['admin_name']); ?></div><div class="role">Librarian / Admin</div></div>
        </div>
        <nav class="side-nav">
            <?php echo side_link('dashboard.php', 'Dashboard', 'grid', 'dashboard.php', $currentPage); ?>
            <div class="side-label">Catalogue</div>
            <?php echo side_link('add-category.php', 'Add Category', 'plus', 'add-category.php', $currentPage); ?>
            <?php echo side_link('manage-categories.php', 'Manage Categories', 'folder', ['manage-categories.php', 'edit-category.php'], $currentPage); ?>
            <?php echo side_link('add-author.php', 'Add Author', 'plus', 'add-author.php', $currentPage); ?>
            <?php echo side_link('manage-authors.php', 'Manage Authors', 'user', ['manage-authors.php', 'edit-author.php'], $currentPage); ?>
            <?php echo side_link('add-book.php', 'Add Book', 'plus', 'add-book.php', $currentPage); ?>
            <?php echo side_link('manage-books.php', 'Manage Books', 'book', ['manage-books.php', 'edit-book.php'], $currentPage); ?>
            <div class="side-label">Circulation</div>
            <?php echo side_link('issue-book.php', 'Issue New Book', 'swap', 'issue-book.php', $currentPage); ?>
            <?php echo side_link('manage-issued-books.php', 'Manage Issued Books', 'recycle', ['manage-issued-books.php', 'update-issue.php'], $currentPage); ?>
            <div class="side-label">Book Store</div>
            <?php echo side_link('manage-orders.php', 'Book Orders', 'box', ['manage-orders.php', 'order-details.php'], $currentPage, $pendingOrders ? '<span class="pill">' . $pendingOrders . '</span>' : ''); ?>
            <div class="side-label">Students</div>
            <?php echo side_link('reg-students.php', 'Registered Students', 'users', ['reg-students.php', 'student-details.php'], $currentPage); ?>
            <?php echo side_link('student-search.php', 'Search Student', 'search', 'student-search.php', $currentPage); ?>
            <div class="side-label">Account</div>
            <?php echo side_link('change-password.php', 'Change Password', 'lock', 'change-password.php', $currentPage); ?>
            <a href="logout.php" class="logout-link"><?php echo icon('logout', 19); ?><span>Logout</span></a>
        </nav>
    </aside>
    <div class="main">
        <header class="topbar">
            <button class="icon-btn sidebar-toggle" id="sidebarToggle" aria-label="Menu"><?php echo icon('menu', 20); ?></button>
            <h1 class="page-title"><span class="crumb">Admin Panel</span><?php echo e($pageTitle); ?></h1>
            <div class="top-actions">
                <a href="manage-orders.php?status=Pending" class="icon-btn" title="Pending orders"><?php echo icon('box', 20); ?><?php if ($pendingOrders): ?><span class="dot"><?php echo $pendingOrders; ?></span><?php endif; ?></a>
                <a href="../index.php" target="_blank" class="icon-btn" title="Open student website"><?php echo icon('home', 20); ?></a>
                <div class="top-user">
                    <div class="avatar">AD</div>
                    <div class="who"><strong><?php echo e($_SESSION['admin_name']); ?></strong><small>Administrator</small></div>
                </div>
                <a href="logout.php" class="btn btn-sm btn-logout"><?php echo icon('logout', 16); ?> Logout</a>
            </div>
        </header>
        <main class="content">
<?php else: ?>
<header class="guest-nav">
    <div class="container">
        <a href="../index.php" class="brand">
            <span class="logo-badge"><?php echo icon('logo', 24); ?></span>
            <span>Online Library<small>Admin Panel</small></span>
        </a>
        <nav class="guest-links">
            <a href="../index.php">Home</a>
            <a href="../index.php#login">Student Login</a>
            <a href="../signup.php">Register</a>
            <a href="index.php" class="btn btn-sm">Admin Panel</a>
        </nav>
    </div>
</header>
<main class="container guest-main">
<?php endif; ?>
<?php show_flash(); ?>
