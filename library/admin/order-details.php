<?php
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';

function load_order($con, $id)
{
    $stmt = $con->prepare('SELECT o.*, s.FullName, s.EmailId, s.MobileNumber FROM tblorders o
                           LEFT JOIN tblstudents s ON s.StudentId = o.StudentId WHERE o.id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $o = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $o;
}

$order = load_order($con, $id);
if (!$order) {
    set_flash('danger', 'Order not found.');
    redirect('manage-orders.php');
}
$final = in_array($order['OrderStatus'], ['Delivered', 'Cancelled'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus  = $_POST['status'] ?? '';
    $newPayment = $_POST['payment'] ?? '';
    $remark     = trim($_POST['remark'] ?? '');

    if ($final) {
        $error = 'This order is already ' . $order['OrderStatus'] . ' and cannot be changed.';
    } elseif (!in_array($newStatus, order_statuses(), true) || !in_array($newPayment, ['Unpaid', 'Paid'], true)) {
        $error = 'Please choose a valid order status and payment status.';
    } else {
        if ($newStatus === 'Delivered') {
            $newPayment = 'Paid';   // books handed over = money collected
        }
        $con->begin_transaction();
        try {
            $stmt = $con->prepare('UPDATE tblorders SET OrderStatus = ?, PaymentStatus = ?, AdminRemark = ? WHERE id = ?');
            $stmt->bind_param('sssi', $newStatus, $newPayment, $remark, $id);
            $stmt->execute();
            $stmt->close();
            if ($newStatus === 'Cancelled') {
                restore_order_stock($con, $id);   // copies go back to the store
            }
            $con->commit();
            set_flash('success', 'Order ' . $order['OrderNumber'] . ' updated to "' . $newStatus . '".');
            redirect('order-details.php?id=' . $id);
        } catch (Throwable $ex) {
            $con->rollback();
            $error = 'Could not update the order. Please try again.';
        }
    }
}

$stmt = $con->prepare('SELECT i.*, b.BookImage FROM tblorderitems i LEFT JOIN tblbooks b ON b.id = i.BookId WHERE i.OrderId = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Order ' . $order['OrderNumber'];
include 'includes/header.php';
?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
<div class="card">
    <div class="card-header"><h3>Order Information</h3><a href="manage-orders.php" class="btn btn-sm btn-secondary">&larr; All Orders</a></div>
    <dl class="detail">
        <dt>Order Number</dt><dd><strong><?php echo e($order['OrderNumber']); ?></strong></dd>
        <dt>Order Date</dt><dd><?php echo format_dt($order['OrderDate']); ?></dd>
        <dt>Student</dt><dd><?php echo e($order['FullName'] ?? '-'); ?> (<a href="student-details.php?sid=<?php echo e($order['StudentId']); ?>"><?php echo e($order['StudentId']); ?></a>)</dd>
        <dt>Email / Mobile</dt><dd><?php echo e($order['EmailId'] ?? '-'); ?> / <?php echo e($order['MobileNumber'] ?? '-'); ?></dd>
        <dt>Contact Number</dt><dd><?php echo e($order['ContactNumber']); ?></dd>
        <dt>Delivery</dt><dd><?php echo $order['ShippingAddress'] ? nl2br(e($order['ShippingAddress'])) : 'Pickup at library counter'; ?></dd>
        <dt>Payment Method</dt><dd><?php echo e($order['PaymentMethod']); ?></dd>
        <dt>Payment Status</dt><dd><?php echo payment_badge($order['PaymentStatus']); ?></dd>
        <dt>Order Status</dt><dd><?php echo order_badge($order['OrderStatus']); ?></dd>
        <dt>Last Updated</dt><dd><?php echo format_dt($order['UpdationDate'] ?: $order['OrderDate']); ?></dd>
    </dl>
</div>

<div class="card">
    <div class="card-header"><h3>Books Ordered</h3></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Cover</th><th>Book</th><th>ISBN</th><th>Price</th><th>Qty</th><th class="text-right">Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i => $it): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo book_cover($it['BookImage'], $it['BookName'], '../', 'thumb'); ?></td>
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
    <div class="cart-summary"><div class="cart-total">Grand Total: <?php echo money($order['TotalAmount']); ?></div></div>
</div>

<div class="card" style="max-width:640px">
    <div class="card-header"><h3>Update Order</h3></div>
    <?php if ($final): ?>
        <div class="alert alert-info">This order is <?php echo e($order['OrderStatus']); ?>. No further changes are allowed.</div>
        <?php if ($order['AdminRemark']): ?><p><strong>Remark:</strong> <?php echo e($order['AdminRemark']); ?></p><?php endif; ?>
    <?php else: ?>
    <form method="post" action="order-details.php?id=<?php echo $id; ?>">
        <div class="form-row">
            <div class="form-group">
                <label for="status">Order Status</label>
                <select id="status" name="status">
                    <?php foreach (order_statuses() as $st): ?>
                        <option value="<?php echo $st; ?>" <?php echo $order['OrderStatus'] === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="payment">Payment Status</label>
                <select id="payment" name="payment">
                    <option value="Unpaid" <?php echo $order['PaymentStatus'] === 'Unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                    <option value="Paid" <?php echo $order['PaymentStatus'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="remark">Remark for Student</label>
            <textarea id="remark" name="remark" rows="3" placeholder="e.g. Your books are ready at the counter."><?php echo e($order['AdminRemark']); ?></textarea>
        </div>
        <p class="help">Setting status to <strong>Delivered</strong> marks payment as Paid. Setting <strong>Cancelled</strong> puts the copies back into stock. Both are final.</p>
        <button type="submit" class="btn btn-success">Update Order</button>
    </form>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
