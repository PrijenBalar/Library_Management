<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];
$pk  = (int)$_SESSION['student_pk'];

/** Load the cart with current book details */
function load_cart($con, $sid)
{
    $stmt = $con->prepare('SELECT c.BookId, c.Quantity, b.BookName, b.ISBNNumber, b.BookPrice, b.SellStock
                            FROM tblcart c JOIN tblbooks b ON b.id = c.BookId
                            WHERE c.StudentId = ? ORDER BY c.AddedDate');
    $stmt->bind_param('s', $sid);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

$items = load_cart($con, $sid);
if (!$items) {
    set_flash('warning', 'Your cart is empty. Add some books first.');
    redirect('buy-books.php');
}

$stmt = $con->prepare('SELECT FullName, MobileNumber FROM tblstudents WHERE id = ?');
$stmt->bind_param('i', $pk);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment = $_POST['payment'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if (!in_array($payment, [PAY_COD, PAY_COUNTER], true)) {
        $error = 'Please choose a payment method.';
    } elseif ($payment === PAY_COD && strlen($address) < 10) {
        $error = 'Please enter your full delivery address for Cash on Delivery.';
    } elseif (!preg_match('/^[0-9]{10}$/', $contact)) {
        $error = 'Contact number must be exactly 10 digits.';
    } else {
        if ($payment === PAY_COUNTER) { $address = null; }

        // Place the order inside a transaction so stock is never oversold
        $con->begin_transaction();
        try {
            $total = 0;
            $lines = [];
            foreach ($items as $it) {
                $stmt = $con->prepare('SELECT BookName, ISBNNumber, BookPrice, SellStock FROM tblbooks WHERE id = ? FOR UPDATE');
                $stmt->bind_param('i', $it['BookId']);
                $stmt->execute();
                $book = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$book || (int)$book['SellStock'] < (int)$it['Quantity']) {
                    throw new RuntimeException('Sorry, "' . $it['BookName'] . '" has only ' . (int)($book['SellStock'] ?? 0) . ' copies left. Please update your cart.');
                }
                $total  += $book['BookPrice'] * $it['Quantity'];
                $lines[] = [$it['BookId'], $book['BookName'], $book['ISBNNumber'], $book['BookPrice'], (int)$it['Quantity']];
            }

            $orderNo = generate_order_number($con);
            $stmt = $con->prepare('INSERT INTO tblorders (OrderNumber, StudentId, TotalAmount, PaymentMethod, PaymentStatus, OrderStatus, ShippingAddress, ContactNumber)
                                   VALUES (?, ?, ?, ?, \'Unpaid\', \'Pending\', ?, ?)');
            $stmt->bind_param('ssdsss', $orderNo, $sid, $total, $payment, $address, $contact);
            $stmt->execute();
            $orderId = $stmt->insert_id;
            $stmt->close();

            foreach ($lines as $l) {
                [$bookId, $name, $isbn, $price, $qty] = $l;
                $stmt = $con->prepare('INSERT INTO tblorderitems (OrderId, BookId, BookName, ISBNNumber, Price, Quantity) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('iissdi', $orderId, $bookId, $name, $isbn, $price, $qty);
                $stmt->execute();
                $stmt->close();

                $stmt = $con->prepare('UPDATE tblbooks SET SellStock = SellStock - ? WHERE id = ?');
                $stmt->bind_param('ii', $qty, $bookId);
                $stmt->execute();
                $stmt->close();
            }

            $stmt = $con->prepare('DELETE FROM tblcart WHERE StudentId = ?');
            $stmt->bind_param('s', $sid);
            $stmt->execute();
            $stmt->close();

            $con->commit();
            set_flash('success', 'Order placed successfully! Your order number is ' . $orderNo . '.');
            redirect('order-details.php?id=' . $orderId);
        } catch (RuntimeException $ex) {
            $con->rollback();
            $error = $ex->getMessage();
            $items = load_cart($con, $sid);
        } catch (Throwable $ex) {
            $con->rollback();
            $error = 'Something went wrong while placing the order. Please try again.';
        }
    }
}

$total = 0;
foreach ($items as $it) { $total += $it['Quantity'] * $it['BookPrice']; }
$selPay = $_POST['payment'] ?? PAY_COD;

$pageTitle = 'Checkout';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h2>Checkout</h2><a href="cart.php" class="btn btn-sm btn-secondary">&larr; Back to Cart</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <h3>Order Summary</h3>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Book</th><th>ISBN</th><th>Price</th><th>Qty</th><th class="text-right">Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i => $it): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($it['BookName']); ?></td>
                    <td><?php echo e($it['ISBNNumber']); ?></td>
                    <td><?php echo money($it['BookPrice']); ?></td>
                    <td><?php echo (int)$it['Quantity']; ?></td>
                    <td class="text-right"><?php echo money($it['Quantity'] * $it['BookPrice']); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="cart-summary"><div class="cart-total">Grand Total: <?php echo money($total); ?></div></div>
</div>

<div class="card" style="max-width:720px">
    <h3>Payment &amp; Delivery</h3>
    <form method="post" action="checkout.php" id="checkoutForm">
        <label class="pay-option">
            <input type="radio" name="payment" value="<?php echo PAY_COD; ?>" <?php echo $selPay === PAY_COD ? 'checked' : ''; ?>>
            <span><?php echo PAY_COD; ?><small>Books are delivered to your address. Pay cash when you receive them.</small></span>
        </label>
        <label class="pay-option">
            <input type="radio" name="payment" value="<?php echo PAY_COUNTER; ?>" <?php echo $selPay === PAY_COUNTER ? 'checked' : ''; ?>>
            <span><?php echo PAY_COUNTER; ?><small>Collect the books from the library counter and pay there.</small></span>
        </label>
        <div class="form-group" id="addressGroup">
            <label for="address">Delivery Address <span class="req">*</span></label>
            <textarea id="address" name="address" rows="3" placeholder="House no, street, city, PIN code"><?php echo e($_POST['address'] ?? ''); ?></textarea>
        </div>
        <div class="form-group" style="max-width:300px">
            <label for="contact">Contact Number <span class="req">*</span></label>
            <input type="tel" id="contact" name="contact" pattern="[0-9]{10}" maxlength="10" value="<?php echo e($_POST['contact'] ?? $student['MobileNumber']); ?>" required>
        </div>
        <button type="submit" class="btn btn-success">Place Order (<?php echo money($total); ?>)</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
