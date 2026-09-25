<?php
/**
 * Common helper functions used by both Student and Admin modules.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';

/** Fine per day for late return (in Rupees) */
define('FINE_PER_DAY', 5);
/** Default number of days a book can be kept */
define('ISSUE_DAYS', 14);

/** Escape output for HTML */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Redirect and stop the script */
function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

/** Store a one-time message (success / danger / warning / info) */
function set_flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
}

/** Print the one-time message if there is one */
function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
    }
}

/* ---------- Student session helpers ---------- */
function is_student_logged_in()
{
    return !empty($_SESSION['student_id']);
}

function require_student()
{
    if (!is_student_logged_in()) {
        set_flash('warning', 'Please login to continue.');
        redirect('index.php');
    }
}

/* ---------- Admin session helpers ---------- */
function is_admin_logged_in()
{
    return !empty($_SESSION['admin_id']);
}

function require_admin()
{
    if (!is_admin_logged_in()) {
        set_flash('warning', 'Please login as admin to continue.');
        redirect('index.php');
    }
}

/** Generate a unique Student ID like SID10234 */
function generate_student_id($con)
{
    do {
        $sid = 'SID' . mt_rand(10000, 99999);
        $stmt = $con->prepare('SELECT id FROM tblstudents WHERE StudentId = ?');
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);
    return $sid;
}

/** Date helpers */
function format_dt($dt)
{
    return $dt ? date('d M Y, h:i A', strtotime($dt)) : '-';
}

function format_d($dt)
{
    return $dt ? date('d M Y', strtotime($dt)) : '-';
}

/* =====================================================================
   ICONS (inline SVG, works offline)
   ===================================================================== */
function icon($name, $size = 64)
{
    $paths = [
        'logo'     => 'M12 6C10 4.5 7 4 2 4v16c5 0 8 .5 10 2 2-1.5 5-2 10-2V4c-5 0-8 .5-10 2zm-1 12.6C9 17.6 6.6 17.1 4 17V6.1c2.9.1 5.2.6 7 1.7v10.8zm9-1.6c-2.6.1-5 .6-7 1.6V7.8c1.8-1.1 4.1-1.6 7-1.7V17z',
        'book'     => 'M6 2h12a2 2 0 0 1 2 2v14H6a1 1 0 0 0 0 2h14v2H6a3 3 0 0 1-3-3V5a3 3 0 0 1 3-3zm2 4v2h8V6H8zm0 4v2h8v-2H8z',
        'recycle'  => 'M12 4V1L7.5 5.5 12 10V6.5a5.5 5.5 0 1 1-5.5 5.5H4a8 8 0 1 0 8-8z',
        'users'    => 'M16 11a3.2 3.2 0 1 0 0-6.4 3.2 3.2 0 0 0 0 6.4zm-8 0a3.2 3.2 0 1 0 0-6.4A3.2 3.2 0 0 0 8 11zm0 2c-2.7 0-8 1.3-8 4v3h16v-3c0-2.7-5.3-4-8-4zm8 0c-.3 0-.7 0-1.1.1 1.2.9 2.1 2.1 2.1 3.9v3h7v-3c0-2.7-5.3-4-8-4z',
        'user'     => 'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z',
        'folder'   => 'M10 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-8l-2-2z',
        'check'    => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm-2 14.4-4.2-4.2 1.4-1.4 2.8 2.8 6.8-6.8 1.4 1.4-8.2 8.2z',
        'cart'     => 'M7 18a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm10 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM1 2v2h2l3.6 7.6-1.4 2.4c-.7 1.3.3 3 1.8 3h12v-2H7.4l1.1-2h7.5c.8 0 1.4-.4 1.8-1l3.6-6.5c.4-.7-.1-1.5-.9-1.5H5.2l-.9-2H1z',
        'box'      => 'M21 7.5 12 2.5 3 7.5v9l9 5 9-5v-9zM12 4.8l6.1 3.4L12 11.6 5.9 8.2 12 4.8zM5 9.9l6 3.4v6.4l-6-3.3V9.9zm8 9.8v-6.4l6-3.4v6.5l-6 3.3z',
        'clock'    => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.4V6h-2v7.4l5.2 3.1 1-1.7-4.2-2.4z',
        'grid'     => 'M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 0h8v8h-8v-8z',
        'lock'     => 'M17 9V7A5 5 0 0 0 7 7v2H5v13h14V9h-2zM9 7a3 3 0 0 1 6 0v2H9V7zm3 11a2 2 0 1 1 0-4 2 2 0 0 1 0 4z',
        'logout'   => 'M10 17l1.4-1.4L8.8 13H20v-2H8.8l2.6-2.6L10 7l-5 5 5 5zM4 5h8V3H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8v-2H4V5z',
        'search'   => 'M15.5 14h-.8l-.3-.3A6.5 6.5 0 1 0 14 15.5l.3.3v.8l5 5 1.5-1.5-5-5zm-6 0a4.5 4.5 0 1 1 0-9 4.5 4.5 0 0 1 0 9z',
        'plus'     => 'M11 5h2v6h6v2h-6v6h-2v-6H5v-2h6V5z',
        'list'     => 'M4 6h2v2H4V6zm4 0h12v2H8V6zm-4 5h2v2H4v-2zm4 0h12v2H8v-2zm-4 5h2v2H4v-2zm4 0h12v2H8v-2z',
        'swap'     => 'M7 7h11l-3-3 1.4-1.4L22 8l-5.6 5.4L15 12l3-3H7V7zm10 10H6l3 3-1.4 1.4L2 16l5.6-5.4L9 12l-3 3h11v2z',
        'store'    => 'M4 3h16l1.5 6a3.2 3.2 0 0 1-5.5 2.2 3.3 3.3 0 0 1-4 .9 3.3 3.3 0 0 1-4-.9A3.2 3.2 0 0 1 2.5 9L4 3zm1 10.5V21h14v-7.5c-1 .3-2.1.2-3-.3-1.2.6-2.7.7-4 .2-1.3.5-2.8.4-4-.2-.9.5-2 .6-3 .3z',
        'home'     => 'M12 3 2 12h3v9h6v-6h2v6h6v-9h3L12 3z',
        'menu'     => 'M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z',
        'money'    =>'M6 3h12v2h-3.3c.6.6 1 1.3 1.2 2H18v2h-2.1c-.4 2.3-2.5 4-5 4h-.4l6.1 8h-2.5l-6.1-8V11h3c1.4 0 2.6-.8 2.9-2H6V7h7.8c-.4-1.2-1.6-2-3-2H6V3z',
    ];
    $d = $paths[$name] ?? $paths['book'];
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="currentColor" fill-rule="evenodd" aria-hidden="true"><path d="' . $d . '"/></svg>';
}

/* =====================================================================
   BOOK COVER IMAGES
   ===================================================================== */
define('BOOK_IMG_DIR', __DIR__ . '/../uploads/books/');
define('BOOK_IMG_MAX', 2 * 1024 * 1024); // 2 MB

/**
 * HTML for a book cover. $prefix is '' on student pages and '../' on admin pages.
 * Shows a coloured placeholder with the first letter when there is no image.
 */
function book_cover($image, $name, $prefix = '', $class = 'cover')
{
    if ($image && is_file(BOOK_IMG_DIR . basename($image))) {
        return '<img class="' . $class . '" src="' . $prefix . 'uploads/books/' . rawurlencode(basename($image)) . '" alt="' . e($name) . '" loading="lazy">';
    }
    return '<div class="' . $class . ' cover-placeholder">' . e(strtoupper(substr((string)$name, 0, 1))) . '</div>';
}

/**
 * Save an uploaded cover image from $_FILES[$field].
 * Returns [filename, null] on success, [null, null] if no file was chosen,
 * or [null, 'error message'] if the file is not valid.
 */
function save_book_image($field)
{
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Image upload failed. Please try again.'];
    }
    if ($f['size'] > BOOK_IMG_MAX) {
        return [null, 'Image is too large. Maximum size is 2 MB.'];
    }
    $info = @getimagesize($f['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
    if (!$info || !isset($types[$info['mime']])) {
        return [null, 'Please upload a valid image (JPG, PNG, WEBP or GIF).'];
    }
    $name = 'book_' . bin2hex(random_bytes(8)) . '.' . $types[$info['mime']];
    if (!is_dir(BOOK_IMG_DIR)) {
        @mkdir(BOOK_IMG_DIR, 0755, true);
    }
    if (!move_uploaded_file($f['tmp_name'], BOOK_IMG_DIR . $name)) {
        return [null, 'Could not save the image. Check that the uploads/books folder is writable.'];
    }
    return [$name, null];
}

/** Delete a cover image file */
function delete_book_image($image)
{
    if ($image) {
        $path = BOOK_IMG_DIR . basename($image);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/* =====================================================================
   BOOK SELLING MODULE helpers
   ===================================================================== */

/** Payment methods a student can choose at checkout */
define('PAY_COD', 'Cash on Delivery');
define('PAY_COUNTER', 'Pay at Library Counter');
/** Maximum copies of one book in a single cart / order */
define('MAX_QTY_PER_BOOK', 5);

/** All order statuses in the order they happen */
function order_statuses()
{
    return ['Pending', 'Confirmed', 'Dispatched', 'Delivered', 'Cancelled'];
}

/** Coloured badge for an order status */
function order_badge($status)
{
    $map = ['Pending' => 'warning', 'Confirmed' => 'info', 'Dispatched' => 'info', 'Delivered' => 'success', 'Cancelled' => 'danger'];
    $cls = $map[$status] ?? 'secondary';
    return '<span class="badge badge-' . $cls . '">' . e($status) . '</span>';
}

/** Coloured badge for a payment status */
function payment_badge($status)
{
    return '<span class="badge badge-' . ($status === 'Paid' ? 'success' : 'warning') . '">' . e($status) . '</span>';
}

/** Rupee amount with 2 decimals */
function money($amount)
{
    return 'Rs. ' . number_format((float)$amount, 2);
}

/** Generate a unique order number like ORD2609251234 */
function generate_order_number($con)
{
    do {
        $no = 'ORD' . date('ymd') . mt_rand(1000, 9999);
        $stmt = $con->prepare('SELECT id FROM tblorders WHERE OrderNumber = ?');
        $stmt->bind_param('s', $no);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
    } while ($exists);
    return $no;
}

/** Total number of copies in a student's cart */
function cart_count($con, $studentId)
{
    $stmt = $con->prepare('SELECT COALESCE(SUM(Quantity), 0) AS c FROM tblcart WHERE StudentId = ?');
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $c = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $c;
}

/** Put the copies of a cancelled order back into stock */
function restore_order_stock($con, $orderId)
{
    $stmt = $con->prepare('UPDATE tblbooks b JOIN tblorderitems i ON i.BookId = b.id
                           SET b.SellStock = b.SellStock + i.Quantity WHERE i.OrderId = ?');
    $stmt->bind_param('i', $orderId);
    $stmt->execute();
    $stmt->close();
}

/** Calculate overdue days between due date and today (0 if not overdue) */
function overdue_days($dueDate)
{
    $due = strtotime(date('Y-m-d', strtotime($dueDate)));
    $today = strtotime(date('Y-m-d'));
    $diff = (int)floor(($today - $due) / 86400);
    return $diff > 0 ? $diff : 0;
}
