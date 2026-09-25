<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];
$id  = (int)($_GET['id'] ?? 0);

/** Load this student's order (a student can only see own orders) */
function load_order($con, $id, $sid)
{
    $stmt = $con->prepare('SELECT * FROM tblorders WHERE id = ? AND StudentId = ?');
    $stmt->bind_param('is', $id, $sid);
    $stmt->execute();
    $o = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $o;
}

$order = load_order($con, $id, $sid);
if (!$order) {
    set_flash('danger', 'Order not found.');
    redirect('my-orders.php');
}

// Student cancels the order (only before it is dispatched)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $con->begin_transaction();
    try {
        $stmt = $con->prepare('SELECT OrderStatus FROM tblorders WHERE id = ? AND StudentId = ? FOR UPDATE');
        $stmt->bind_param('is', $id, $sid);
        $stmt->execute();
        $status = $stmt->get_result()->fetch_assoc()['OrderStatus'] ?? '';
        $stmt->close();

        if (!in_array($status, ['Pending', 'Confirmed'], true)) {
            throw new RuntimeException('This order can no longer be cancelled.');
        }
        $remark = 'Cancelled by student.';
        $stmt = $con->prepare("UPDATE tblorders SET OrderStatus = 'Cancelled', AdminRemark = ? WHERE id = ?");
        $stmt->bind_param('si', $remark, $id);
        $stmt->execute();
        $stmt->close();
        restore_order_stock($con, $id);
        $con->commit();
        set_flash('success', 'Order ' . $order['OrderNumber'] . ' has been cancelled.');
    } catch (Throwable $ex) {
        $con->rollback();
        set_flash('danger', $ex instanceof RuntimeException ? $ex->getMessage() : 'Could not cancel the order.');
    }
    redirect('order-details.php?id=' . $id);
}

$stmt = $con->prepare('SELECT * FROM tblorderitems WHERE OrderId = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$steps = ['Pending', 'Confirmed', 'Dispatched', 'Delivered'];
$reached = array_search($order['OrderStatus'], $steps, true);

$pageTitle = 'Order ' . $order['OrderNumber'];
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Order <?php echo e($order['OrderNumber']); ?></h2>
        <a href="my-orders.php" class="btn btn-sm btn-secondary">&larr; My Orders</a>
    </div>

    <?php if ($order['OrderStatus'] === 'Cancelled'): ?>
        <div class="alert alert-danger">This order was cancelled.</div>
    <?php else: ?>
        <div class="order-steps">
            <?php foreach ($steps as $n => $s): ?>
                <div class="order-step<?php echo ($reached !== false && $n <= $reached) ? ' done' : ''; ?>"><?php echo $s; ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <dl class="detail">
        <dt>Order Date</dt><dd><?php echo format_dt($order['OrderDate']); ?></dd>
        <dt>Order Status</dt><dd><?php echo order_badge($order['OrderStatus']); ?></dd>
        <dt>Payment Method</dt><dd><?php echo e($order['PaymentMethod']); ?></dd>
        <dt>Payment Status</dt><dd><?php echo payment_badge($order['PaymentStatus']); ?></dd>
        <dt>Delivery</dt><dd><?php echo $order['ShippingAddress'] ? nl2br(e($order['ShippingAddress'])) : 'Collect from library counter'; ?></dd>
        <dt>Contact Number</dt><dd><?php echo e($order['ContactNumber']); ?></dd>
        <?php if ($order['AdminRemark']): ?><dt>Library Remark</dt><dd><?php echo e($order['AdminRemark']); ?></dd><?php endif; ?>
        <dt>Last Updated</dt><dd><?php echo format_dt($order['UpdationDate'] ?: $order['OrderDate']); ?></dd>
    </dl>
</div>

<div class="card">
    <div class="card-header"><h3>Books in this Order</h3></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Book</th><th>ISBN</th><th>Price</th><th>Qty</th><th class="text-right">Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i => $it): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($it['BookName']); ?></td>
                    <td><?php echo e($it['ISBNNumber']); ?></td>
                    <td><?php echo money($it['Price']); ?></td>
                    <td><?php echo (int)$it['Quantity']; ?></td>
                    <td class="text-right"><?php echo money($it['Price'] * $it['Quantity']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="cart-summary">
        <div class="cart-total">Grand Total: <?php echo money($order['TotalAmount']); ?></div>
        <?php if (in_array($order['OrderStatus'], ['Pending', 'Confirmed'], true)): ?>
            <form method="post" action="order-details.php?id=<?php echo $id; ?>" onsubmit="return confirm('Cancel this order?');">
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="btn btn-danger">Cancel Order</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
