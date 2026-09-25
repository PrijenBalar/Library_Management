<?php
require_once '../includes/functions.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['category'] ?? '');
    $status = (int)($_POST['status'] ?? 1);

    if ($name === '') {
        $error = 'Category name is required.';
    } else {
        $stmt = $con->prepare('SELECT id FROM tblcategory WHERE CategoryName = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'This category already exists.';
        } else {
            $stmt = $con->prepare('INSERT INTO tblcategory (CategoryName, Status) VALUES (?, ?)');
            $stmt->bind_param('si', $name, $status);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Category "' . $name . '" added successfully.');
            redirect('manage-categories.php');
        }
    }
}

$pageTitle = 'Add Category';
include 'includes/header.php';
?>
<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Add New Category</h3><a href="manage-categories.php" class="btn btn-sm btn-secondary">Manage Categories</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="add-category.php" autocomplete="off">
        <div class="form-group">
            <label for="category">Category Name <span class="req">*</span></label>
            <input type="text" id="category" name="category" value="<?php echo e($_POST['category'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status <span class="req">*</span></label>
            <select id="status" name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn">Add Category</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
