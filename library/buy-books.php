<?php
require_once 'includes/functions.php';
require_student();

$q     = trim($_GET['q'] ?? '');
$catId = (int)($_GET['cat'] ?? 0);

$sql = 'SELECT b.id, b.BookName, b.ISBNNumber, b.BookPrice, b.SellStock, b.BookImage, c.CategoryName, a.AuthorName
        FROM tblbooks b
        LEFT JOIN tblcategory c ON c.id = b.CatId
        LEFT JOIN tblauthors a ON a.id = b.AuthorId
        WHERE (b.BookName LIKE ? OR a.AuthorName LIKE ? OR b.ISBNNumber LIKE ?)';
if ($catId > 0) {
    $sql .= ' AND b.CatId = ' . $catId;
}
$sql .= ' ORDER BY (b.SellStock > 0) DESC, b.BookName ASC';

$like = '%' . $q . '%';
$stmt = $con->prepare($sql);
$stmt->bind_param('sss', $like, $like, $like);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $con->query('SELECT id, CategoryName FROM tblcategory WHERE Status = 1 ORDER BY CategoryName')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Buy Books';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h2>Book Store</h2>
        <a href="cart.php" class="btn btn-sm btn-success">Go to Cart</a>
    </div>
    <form method="get" action="buy-books.php" class="search-bar">
        <input type="text" name="q" placeholder="Search by book name, author or ISBN..." value="<?php echo e($q); ?>">
        <select name="cat" style="max-width:240px">
            <option value="0">All Categories</option>
            <?php foreach ($categories as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>" <?php echo $catId === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['CategoryName']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn">Search</button>
        <?php if ($q !== '' || $catId): ?><a href="buy-books.php" class="btn btn-secondary">Clear</a><?php endif; ?>
    </form>
    <p class="text-muted">Payment options: <?php echo PAY_COD; ?> or <?php echo PAY_COUNTER; ?>. Maximum <?php echo MAX_QTY_PER_BOOK; ?> copies of one book per order.</p>
</div>

<?php if (!$books): ?>
    <div class="card"><p class="empty">No books matched your search.</p></div>
<?php else: ?>
<div class="book-grid">
    <?php foreach ($books as $b): $stock = (int)$b['SellStock']; $maxQty = min($stock, MAX_QTY_PER_BOOK); ?>
    <div class="book-card">
        <div class="cover-wrap"><?php echo book_cover($b['BookImage'], $b['BookName']); ?></div>
        <div class="book-body">
            <h3 class="book-title"><?php echo e($b['BookName']); ?></h3>
            <div class="book-meta">by <?php echo e($b['AuthorName']); ?></div>
            <div class="book-meta"><?php echo e($b['CategoryName']); ?> &middot; ISBN <?php echo e($b['ISBNNumber']); ?></div>
            <div class="book-price"><?php echo money($b['BookPrice']); ?></div>
            <?php if ($stock <= 0): ?>
                <div class="stock-out">Out of stock</div>
            <?php elseif ($stock <= 3): ?>
                <div class="stock-low">Only <?php echo $stock; ?> left</div>
            <?php else: ?>
                <div class="stock-ok">In stock (<?php echo $stock; ?>)</div>
            <?php endif; ?>
            <div class="book-actions">
                <?php if ($stock > 0): ?>
                    <input type="number" class="qty-input" id="qty-<?php echo (int)$b['id']; ?>" value="1" min="1" max="<?php echo $maxQty; ?>">
                    <button type="button" class="btn btn-sm add-to-cart" data-id="<?php echo (int)$b['id']; ?>">Add to Cart</button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-secondary" disabled>Not Available</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
