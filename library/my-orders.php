<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];
$stmt = $con->prepare('SELECT o.*, (SELECT COALESCE(SUM(i.Quantity), 0) FROM tblorderitems i WHERE i.OrderId = o.id) AS copies
                        FROM tblorders o WHERE o.StudentId = ? ORDER BY o.id DESC');
$stmt->bind_param('s', $sid);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'My Orders';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h2>&#128230; My Orders</h2><a href="buy-books.php" class="btn btn-sm">Buy More Books</a></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Order No.</th><th>Order Date</th><th>Books</th><th>Total</th><th>Payment</th><th>Payment Status</th><th>Order Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="9" class="empty">You have not placed any orders yet. <a href="buy-books.php">Buy books</a></td></tr>
            <?php else: foreach ($orders as $i => $o): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo e($o['OrderNumber']); ?></strong></td>
                    <td><?php echo format_dt($o['OrderDate']); ?></td>
                    <td><?php echo (int)$o['copies']; ?></td>
                    <td><?php echo money($o['TotalAmount']); ?></td>
                    <td><?php echo e($o['PaymentMethod']); ?></td>
                    <td><?php echo payment_badge($o['PaymentStatus']); ?></td>
                    <td><?php echo order_badge($o['OrderStatus']); ?></td>
                    <td><a href="order-details.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm">View</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
