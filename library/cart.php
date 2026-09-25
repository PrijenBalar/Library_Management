<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];
$stmt = $con->prepare('SELECT c.BookId, c.Quantity, b.BookName, b.ISBNNumber, b.BookPrice, b.SellStock, a.AuthorName
                        FROM tblcart c
                        JOIN tblbooks b ON b.id = c.BookId
                        LEFT JOIN tblauthors a ON a.id = b.AuthorId
                        WHERE c.StudentId = ?
                        ORDER BY c.AddedDate');
$stmt->bind_param('s', $sid);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
$stockProblem = false;
foreach ($items as $it) {
    $total += $it['Quantity'] * $it['BookPrice'];
    if ($it['Quantity'] > $it['SellStock']) { $stockProblem = true; }
}

$pageTitle = 'My Cart';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h2>&#128722; My Cart</h2><a href="buy-books.php" class="btn btn-sm btn-secondary">Continue Shopping</a></div>
    <?php if (!$items): ?>
        <p class="empty">Your cart is empty. <a href="buy-books.php">Browse books to buy</a>.</p>
    <?php else: ?>
        <?php if ($stockProblem): ?>
            <div class="alert alert-warning">Some books in your cart have less stock now. Please reduce the quantity before checkout.</div>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>#</th><th>Book</th><th>Author</th><th>ISBN</th><th>Price</th><th>Quantity</th><th>Available</th><th class="text-right">Total</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($items as $i => $it): $max = max(1, min((int)$it['SellStock'], MAX_QTY_PER_BOOK)); ?>
                    <tr id="row-<?php echo (int)$it['BookId']; ?>">
                        <td><?php echo $i + 1; ?></td>
                        <td><?php echo e($it['BookName']); ?></td>
                        <td><?php echo e($it['AuthorName']); ?></td>
                        <td><?php echo e($it['ISBNNumber']); ?></td>
                        <td><?php echo money($it['BookPrice']); ?></td>
                        <td><input type="number" class="qty-input cart-qty" data-id="<?php echo (int)$it['BookId']; ?>" data-prev="<?php echo (int)$it['Quantity']; ?>" value="<?php echo (int)$it['Quantity']; ?>" min="1" max="<?php echo $max; ?>"></td>
                        <td><?php echo (int)$it['SellStock'] > 0 ? (int)$it['SellStock'] : '<span class="stock-out">Out of stock</span>'; ?></td>
                        <td class="text-right">Rs. <span id="line-<?php echo (int)$it['BookId']; ?>"><?php echo number_format($it['Quantity'] * $it['BookPrice'], 2); ?></span></td>
                        <td><button type="button" class="btn btn-sm btn-danger cart-remove" data-id="<?php echo (int)$it['BookId']; ?>">Remove</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <div class="cart-total">Grand Total: Rs. <span id="cartTotal"><?php echo number_format($total, 2); ?></span></div>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout &rarr;</a>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
