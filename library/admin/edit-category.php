<?php
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['category'] ?? '');
    $status = (int)($_POST['status'] ?? 1);

    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $stmt = $con->prepare('SELECT id FROM tblcategory WHERE CategoryName = ? AND id <> ?');
        $stmt->bind_param('si', $name, $id);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'Another category with this name already exists.';
        } else {
            $stmt = $con->prepare('UPDATE tblcategory SET CategoryName = ?, Status = ? WHERE id = ?');
            $stmt->bind_param('sii', $name, $status, $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Category updated successfully.');
            redirect('manage-categories.php');
        }
    }
}

$stmt = $con->prepare('SELECT * FROM tblcategory WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$cat = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$cat) {
    set_flash('danger', 'Category not found.');
    redirect('manage-categories.php');
}

$pageTitle = 'Edit Category';
include 'includes/header.php';
?>
<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Edit Category</h3><a href="manage-categories.php" class="btn btn-sm btn-secondary">Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="edit-category.php?id=<?php echo $id; ?>" autocomplete="off">
        <div class="form-group">
            <label for="category">Category Name <span class="req">*</span></label>
            <input type="text" id="category" name="category" value="<?php echo e($cat['CategoryName']); ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status <span class="req">*</span></label>
            <select id="status" name="status">
                <option value="1" <?php echo $cat['Status'] ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo !$cat['Status'] ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn">Update Category</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
