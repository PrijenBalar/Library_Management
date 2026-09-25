<?php
/**
 * AJAX: live search of books (student side)
 * Returns the book cards as HTML.
 */
require_once '../includes/functions.php';
if (!is_student_logged_in()) {
    http_response_code(403);
    echo '<p class="empty">Please login first.</p>';
    exit;
}

$q = '%' . trim($_GET['q'] ?? '') . '%';
$stmt = $con->prepare('SELECT b.id, b.BookName, b.ISBNNumber, b.BookPrice, b.SellStock, b.BookImage, c.CategoryName, a.AuthorName,
                       (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.BookId = b.id AND i.ReturnStatus = 0) AS issued
                       FROM tblbooks b
                       LEFT JOIN tblcategory c ON c.id = b.CatId
                       LEFT JOIN tblauthors a ON a.id = b.AuthorId
                       WHERE b.BookName LIKE ? OR a.AuthorName LIKE ? OR c.CategoryName LIKE ? OR b.ISBNNumber LIKE ?
                       ORDER BY b.BookName ASC');
$stmt->bind_param('ssss', $q, $q, $q, $q);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('X-Book-Count: ' . count($books));
if (!$books) {
    echo '<p class="empty">No books matched your search.</p>';
    exit;
}
$imgPrefix = '';   // images are loaded by the page in the root folder
foreach ($books as $b) {
    include '../includes/book-card.php';
}
