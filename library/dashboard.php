<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];

/** Run a COUNT/SUM query with one string parameter (the Student ID) */
function student_count($con, $sql, $sid)
{
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $sid);
    $stmt->execute();
    $v = $stmt->get_result()->fetch_row()[0];
    $stmt->close();
    return $v;
}

$totalBooks  = (int)$con->query('SELECT COUNT(*) FROM tblbooks')->fetch_row()[0];
$notReturned = (int)student_count($con, 'SELECT COUNT(*) FROM tblissuedbookdetails WHERE StudentId = ? AND ReturnStatus = 0', $sid);
$issuedTotal = (int)student_count($con, 'SELECT COUNT(*) FROM tblissuedbookdetails WHERE StudentId = ?', $sid);
$myOrders    = (int)student_count($con, 'SELECT COUNT(*) FROM tblorders WHERE StudentId = ?', $sid);
$inCart      = cart_count($con, $sid);

// Currently issued books
$stmt = $con->prepare('SELECT i.IssueDate, i.DueDate, b.BookName, b.ISBNNumber, b.BookImage, a.AuthorName
                        FROM tblissuedbookdetails i
                        JOIN tblbooks b ON b.id = i.BookId
                        LEFT JOIN tblauthors a ON a.id = b.AuthorId
                        WHERE i.StudentId = ? AND i.ReturnStatus = 0
                        ORDER BY i.IssueDate DESC');
$stmt->bind_param('s', $sid);
$stmt->execute();
$current = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Recently added books
$recent = $con->query('SELECT b.id, b.BookName, b.BookImage, a.AuthorName, c.CategoryName
                       FROM tblbooks b
                       LEFT JOIN tblauthors a ON a.id = b.AuthorId
                       LEFT JOIN tblcategory c ON c.id = b.CatId
                       ORDER BY b.id DESC LIMIT 6')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<div class="welcome">
    <div>
        <h2>Hello, <?php echo e($_SESSION['student_name']); ?>!</h2>
        <p>Student ID <strong><?php echo e($sid); ?></strong> &middot; Borrow books from the library or buy your own copy from the Book Store.</p>
    </div>
    <a href="buy-books.php" class="btn"><?php echo icon('store', 18); ?> Visit Book Store</a>
</div>
<div class="tiles">
    <a class="tile purple" href="listed-books.php"><?php echo icon('book', 58); ?><div class="value"><?php echo $totalBooks; ?></div><div class="label">Library Books</div></a>
    <a class="tile brown" href="issued-books.php"><?php echo icon('recycle', 58); ?><div class="value"><?php echo $notReturned; ?></div><div class="label">Not Returned</div></a>
    <a class="tile green" href="issued-books.php"><?php echo icon('check', 58); ?><div class="value"><?php echo $issuedTotal; ?></div><div class="label">Total Issued to Me</div></a>
    <a class="tile blue" href="my-orders.php"><?php echo icon('box', 58); ?><div class="value"><?php echo $myOrders; ?></div><div class="label">My Orders</div></a>
    <a class="tile red" href="cart.php"><?php echo icon('cart', 58); ?><div class="value"><?php echo $inCart; ?></div><div class="label">In My Cart</div></a>
</div>

<div class="card">
    <div class="card-header"><h3>Books Currently With You</h3><a href="issued-books.php" class="btn btn-sm">View All</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Cover</th><th>Book</th><th>Author</th><th>ISBN</th><th>Issued On</th><th>Return By</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$current): ?>
                <tr><td colspan="8" class="empty">No books are currently issued to you.</td></tr>
            <?php else: foreach ($current as $i => $r): $od = overdue_days($r['DueDate']); ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo book_cover($r['BookImage'], $r['BookName'], '', 'thumb'); ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['AuthorName']); ?></td>
                    <td><?php echo e($r['ISBNNumber']); ?></td>
                    <td><?php echo format_dt($r['IssueDate']); ?></td>
                    <td><?php echo format_d($r['DueDate']); ?></td>
                    <td><?php echo $od > 0 ? '<span class="badge badge-danger">Overdue by ' . $od . ' day(s)</span>' : '<span class="badge badge-warning">Not Returned</span>'; ?></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<h2 class="section-title">Recently Added Books <a href="listed-books.php" class="btn btn-sm">View All Books</a></h2>
<div class="book-grid">
    <?php foreach ($recent as $b): ?>
    <div class="book-card">
        <div class="cover-wrap"><?php echo book_cover($b['BookImage'], $b['BookName']); ?></div>
        <div class="book-body">
            <h3 class="book-title"><?php echo e($b['BookName']); ?></h3>
            <div class="book-meta">by <?php echo e($b['AuthorName']); ?></div>
            <div class="book-meta"><?php echo e($b['CategoryName']); ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
