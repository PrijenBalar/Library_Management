<?php
require_once '../includes/functions.php';
require_admin();

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $con->prepare('SELECT COUNT(*) AS c FROM tblissuedbookdetails WHERE BookId = ? AND ReturnStatus = 0');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $issued = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    if ($issued > 0) {
        set_flash('danger', 'Cannot delete: this book is currently issued to a student.');
    } else {
        $stmt = $con->prepare('DELETE FROM tblissuedbookdetails WHERE BookId = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        $stmt = $con->prepare('SELECT BookImage FROM tblbooks WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $img = $stmt->get_result()->fetch_assoc()['BookImage'] ?? null;
        $stmt->close();
        $stmt = $con->prepare('DELETE FROM tblbooks WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        delete_book_image($img);
        set_flash('success', 'Book deleted successfully.');
    }
    redirect('manage-books.php');
}

$rows = $con->query('SELECT b.*, c.CategoryName, a.AuthorName,
                     (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.BookId = b.id AND i.ReturnStatus = 0) AS issued
                     FROM tblbooks b
                     LEFT JOIN tblcategory c ON c.id = b.CatId
                     LEFT JOIN tblauthors a ON a.id = b.AuthorId
                     ORDER BY b.id DESC')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Books';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>All Books (<?php echo count($rows); ?>)</h3><a href="add-book.php" class="btn btn-sm">+ Add Book</a></div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter by book, author, category, ISBN..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Cover</th><th>Book Name</th><th>Category</th><th>Author</th><th>ISBN</th><th>Price (Rs.)</th><th>Copies for Sale</th><th>Library Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="10" class="empty">No books added yet.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo book_cover($r['BookImage'], $r['BookName'], '../', 'thumb'); ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['CategoryName']); ?></td>
                    <td><?php echo e($r['AuthorName']); ?></td>
                    <td><?php echo e($r['ISBNNumber']); ?></td>
                    <td><?php echo number_format((float)$r['BookPrice'], 2); ?></td>
                    <td><?php echo (int)$r['SellStock'] > 0 ? (int)$r['SellStock'] : '<span class="badge badge-secondary">Not for sale</span>'; ?></td>
                    <td><?php echo $r['issued'] ? '<span class="badge badge-danger">Issued</span>' : '<span class="badge badge-success">Available</span>'; ?></td>
                    <td class="actions">
                        <a href="edit-book.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="manage-books.php?del=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
