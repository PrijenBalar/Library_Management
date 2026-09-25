<?php
require_once '../includes/functions.php';
require_admin();

function one_value($con, $sql) { return $con->query($sql)->fetch_row()[0]; }

$s = [
    'books'      => (int)one_value($con, 'SELECT COUNT(*) FROM tblbooks'),
    'notret'     => (int)one_value($con, 'SELECT COUNT(*) FROM tblissuedbookdetails WHERE ReturnStatus = 0'),
    'returned'   => (int)one_value($con, 'SELECT COUNT(*) FROM tblissuedbookdetails WHERE ReturnStatus = 1'),
    'students'   => (int)one_value($con, 'SELECT COUNT(*) FROM tblstudents'),
    'authors'    => (int)one_value($con, 'SELECT COUNT(*) FROM tblauthors'),
    'categories' => (int)one_value($con, 'SELECT COUNT(*) FROM tblcategory'),
    'orders'     => (int)one_value($con, 'SELECT COUNT(*) FROM tblorders'),
    'pending'    => (int)one_value($con, "SELECT COUNT(*) FROM tblorders WHERE OrderStatus = 'Pending'"),
    'sales'      => (float)one_value($con, "SELECT COALESCE(SUM(TotalAmount), 0) FROM tblorders WHERE PaymentStatus = 'Paid' AND OrderStatus <> 'Cancelled'"),
];

$recentIssues = $con->query('SELECT i.id, i.IssueDate, i.DueDate, i.ReturnStatus, i.StudentId, s.FullName, b.BookName, b.BookImage
                             FROM tblissuedbookdetails i
                             JOIN tblbooks b ON b.id = i.BookId
                             LEFT JOIN tblstudents s ON s.StudentId = i.StudentId
                             ORDER BY i.id DESC LIMIT 6')->fetch_all(MYSQLI_ASSOC);

$recentOrders = $con->query('SELECT o.id, o.OrderNumber, o.StudentId, o.TotalAmount, o.OrderStatus, o.PaymentStatus, o.OrderDate, s.FullName
                             FROM tblorders o LEFT JOIN tblstudents s ON s.StudentId = o.StudentId
                             ORDER BY o.id DESC LIMIT 6')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Dashboard';
include 'includes/header.php';
?>
<div class="welcome">
    <div>
        <h2>Welcome back, <?php echo e($_SESSION['admin_name']); ?></h2>
        <p>Today is <?php echo date('l, d F Y'); ?> &middot; <?php echo $s['pending']; ?> order(s) waiting and <?php echo $s['notret']; ?> book(s) out on loan.</p>
    </div>
    <a href="issue-book.php" class="btn"><?php echo icon('swap', 18); ?> Issue a Book</a>
</div>
<div class="tiles">
    <a class="tile purple" href="manage-books.php"><?php echo icon('book', 58); ?><div class="value"><?php echo $s['books']; ?></div><div class="label">Total Books</div></a>
    <a class="tile brown" href="manage-issued-books.php?filter=issued"><?php echo icon('recycle', 58); ?><div class="value"><?php echo $s['notret']; ?></div><div class="label">Books on Loan</div></a>
    <a class="tile green" href="manage-issued-books.php?filter=returned"><?php echo icon('check', 58); ?><div class="value"><?php echo $s['returned']; ?></div><div class="label">Books Returned</div></a>
    <a class="tile red" href="reg-students.php"><?php echo icon('users', 58); ?><div class="value"><?php echo $s['students']; ?></div><div class="label">Students</div></a>
    <a class="tile blue" href="manage-authors.php"><?php echo icon('user', 58); ?><div class="value"><?php echo $s['authors']; ?></div><div class="label">Authors</div></a>
    <a class="tile purple" href="manage-categories.php"><?php echo icon('folder', 58); ?><div class="value"><?php echo $s['categories']; ?></div><div class="label">Categories</div></a>
    <a class="tile blue" href="manage-orders.php"><?php echo icon('box', 58); ?><div class="value"><?php echo $s['orders']; ?></div><div class="label">Book Orders</div></a>
    <a class="tile brown" href="manage-orders.php?status=Pending"><?php echo icon('clock', 58); ?><div class="value"><?php echo $s['pending']; ?></div><div class="label">Pending Orders</div></a>
    <a class="tile green" href="manage-orders.php?status=Delivered"><?php echo icon('money', 58); ?><div class="value"><?php echo money($s['sales']); ?></div><div class="label">Sales Received</div></a>
</div>

<div class="card">
    <div class="card-header"><h3>Recent Book Issues</h3><a href="issue-book.php" class="btn btn-sm">Issue New Book</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Cover</th><th>Book</th><th>Student</th><th>Student ID</th><th>Issued On</th><th>Due Date</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$recentIssues): ?>
                <tr><td colspan="9" class="empty">No books issued yet.</td></tr>
            <?php else: foreach ($recentIssues as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo book_cover($r['BookImage'], $r['BookName'], '../', 'thumb'); ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['FullName'] ?? '-'); ?></td>
                    <td><?php echo e($r['StudentId']); ?></td>
                    <td><?php echo format_dt($r['IssueDate']); ?></td>
                    <td><?php echo format_d($r['DueDate']); ?></td>
                    <td><?php if ($r['ReturnStatus']): ?><span class="badge badge-success">Returned</span><?php elseif (overdue_days($r['DueDate']) > 0): ?><span class="badge badge-danger">Overdue</span><?php else: ?><span class="badge badge-warning">Not Returned</span><?php endif; ?></td>
                    <td class="actions"><a href="update-issue.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm <?php echo $r['ReturnStatus'] ? 'btn-secondary' : 'btn-success'; ?>"><?php echo $r['ReturnStatus'] ? 'View' : 'Return'; ?></a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3>Recent Book Orders</h3><a href="manage-orders.php" class="btn btn-sm">All Orders</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Order No.</th><th>Student</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$recentOrders): ?>
                <tr><td colspan="8" class="empty">No orders yet.</td></tr>
            <?php else: foreach ($recentOrders as $i => $o): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo e($o['OrderNumber']); ?></strong></td>
                    <td><?php echo e($o['FullName'] ?? '-'); ?> (<?php echo e($o['StudentId']); ?>)</td>
                    <td><?php echo format_dt($o['OrderDate']); ?></td>
                    <td><?php echo money($o['TotalAmount']); ?></td>
                    <td><?php echo payment_badge($o['PaymentStatus']); ?></td>
                    <td><?php echo order_badge($o['OrderStatus']); ?></td>
                    <td><a href="order-details.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm">Manage</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
