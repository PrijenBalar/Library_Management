<?php
/**
 * One book card for the "Listed Books" grid.
 * Used by listed-books.php and ajax/search-books.php.
 * Expects $b = row with BookName, BookImage, AuthorName, CategoryName, ISBNNumber, BookPrice, SellStock, issued.
 * $imgPrefix = '' (page in root) or '../' (page in a sub folder).
 */
?>
<div class="book-card">
    <div class="cover-wrap">
        <?php echo book_cover($b['BookImage'], $b['BookName'], $imgPrefix ?? ''); ?>
        <span class="ribbon"><?php echo $b['issued'] ? '<span class="badge badge-danger">Issued</span>' : '<span class="badge badge-success">Available</span>'; ?></span>
    </div>
    <div class="book-body">
        <h3 class="book-title"><?php echo e($b['BookName']); ?></h3>
        <div class="book-meta">by <?php echo e($b['AuthorName']); ?></div>
        <div class="book-meta"><?php echo e($b['CategoryName']); ?> &middot; ISBN <?php echo e($b['ISBNNumber']); ?></div>
        <div class="book-price"><?php echo money($b['BookPrice']); ?></div>
        <div class="book-actions">
            <?php if ((int)$b['SellStock'] > 0): ?>
                <a href="buy-books.php?q=<?php echo urlencode($b['ISBNNumber']); ?>" class="btn btn-sm btn-success">Buy a Copy</a>
            <?php else: ?>
                <span class="stock-out">Not for sale</span>
            <?php endif; ?>
        </div>
    </div>
</div>
