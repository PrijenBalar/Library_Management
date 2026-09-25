<?php
require_once '../includes/functions.php';
require_admin();

if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $con->prepare('SELECT COUNT(*) AS c FROM tblbooks WHERE AuthorId = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $used = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    if ($used > 0) {
        set_flash('danger', 'Cannot delete: ' . $used . ' book(s) are written by this author. Delete or update those books first.');
    } else {
        $stmt = $con->prepare('DELETE FROM tblauthors WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Author deleted successfully.');
    }
    redirect('manage-authors.php');
}

$rows = $con->query('SELECT a.*, (SELECT COUNT(*) FROM tblbooks b WHERE b.AuthorId = a.id) AS books FROM tblauthors a ORDER BY a.AuthorName')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Authors';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>All Authors (<?php echo count($rows); ?>)</h3><a href="add-author.php" class="btn btn-sm">+ Add Author</a></div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter authors..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Author Name</th><th>Books</th><th>Created On</th><th>Updated On</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6" class="empty">No authors added yet.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($r['AuthorName']); ?></td>
                    <td><?php echo (int)$r['books']; ?></td>
                    <td><?php echo format_dt($r['CreationDate']); ?></td>
                    <td><?php echo format_dt($r['UpdationDate']); ?></td>
                    <td class="actions">
                        <a href="edit-author.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="manage-authors.php?del=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
