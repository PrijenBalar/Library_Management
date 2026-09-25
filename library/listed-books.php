<?php
require_once 'includes/functions.php';
require_student();

$books = $con->query('SELECT b.id, b.BookName, b.ISBNNumber, b.BookPrice, b.SellStock, b.BookImage, c.CategoryName, a.AuthorName,
                      (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.BookId = b.id AND i.ReturnStatus = 0) AS issued
                      FROM tblbooks b
                      LEFT JOIN tblcategory c ON c.id = b.CatId
                      LEFT JOIN tblauthors a ON a.id = b.AuthorId
                      ORDER BY b.BookName ASC')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Listed Books';
include 'includes/header.php';
?>
<div class="search-bar">
    <input type="text" id="bookSearch" placeholder="Search by book name, author, category or ISBN (results update as you type)..." autocomplete="off">
    <span class="text-muted" id="bookCount"><?php echo count($books); ?> book(s)</span>
</div>
<div class="book-grid" id="booksGrid">
    <?php if (!$books): ?>
        <p class="empty">No books listed yet.</p>
    <?php else: foreach ($books as $b): include 'includes/book-card.php'; endforeach; endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
