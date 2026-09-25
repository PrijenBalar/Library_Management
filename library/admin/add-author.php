<?php
require_once '../includes/functions.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['author'] ?? '');

    if ($name === '') {
        $error = 'Author name is required.';
    } else {
        $stmt = $con->prepare('SELECT id FROM tblauthors WHERE AuthorName = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'This author already exists.';
        } else {
            $stmt = $con->prepare('INSERT INTO tblauthors (AuthorName) VALUES (?)');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Author "' . $name . '" added successfully.');
            redirect('manage-authors.php');
        }
    }
}

$pageTitle = 'Add Author';
include 'includes/header.php';
?>
<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Add New Author</h3><a href="manage-authors.php" class="btn btn-sm btn-secondary">Manage Authors</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="add-author.php" autocomplete="off">
        <div class="form-group">
            <label for="author">Author Name <span class="req">*</span></label>
            <input type="text" id="author" name="author" value="<?php echo e($_POST['author'] ?? ''); ?>" required>
        </div>
        <button type="submit" class="btn">Add Author</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
