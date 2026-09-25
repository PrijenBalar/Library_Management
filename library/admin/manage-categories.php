<?php
require_once '../includes/functions.php';
require_admin();

// Delete category
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $stmt = $con->prepare('SELECT COUNT(*) AS c FROM tblbooks WHERE CatId = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $used = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();

    if ($used > 0) {
        set_flash('danger', 'Cannot delete: ' . $used . ' book(s) belong to this category. Delete or move those books first.');
    } else {
        $stmt = $con->prepare('DELETE FROM tblcategory WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Category deleted successfully.');
    }
    redirect('manage-categories.php');
}

$rows = $con->query('SELECT c.*, (SELECT COUNT(*) FROM tblbooks b WHERE b.CatId = c.id) AS books FROM tblcategory c ORDER BY c.CategoryName')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Categories';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>All Categories (<?php echo count($rows); ?>)</h3><a href="add-category.php" class="btn btn-sm">+ Add Category</a></div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter categories..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Category Name</th><th>Books</th><th>Status</th><th>Created On</th><th>Updated On</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7" class="empty">No categories added yet.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($r['CategoryName']); ?></td>
                    <td><?php echo (int)$r['books']; ?></td>
                    <td><?php echo $r['Status'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>'; ?></td>
                    <td><?php echo format_dt($r['CreationDate']); ?></td>
                    <td><?php echo format_dt($r['UpdationDate']); ?></td>
                    <td class="actions">
                        <a href="edit-category.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="manage-categories.php?del=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-danger confirm-delete">Delete</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
