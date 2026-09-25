<?php
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['author'] ?? '');
    if ($name === '') {
        $error = 'Author name is required.';
    } else {
        $stmt = $con->prepare('SELECT id FROM tblauthors WHERE AuthorName = ? AND id <> ?');
        $stmt->bind_param('si', $name, $id);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'Another author with this name already exists.';
        } else {
            $stmt = $con->prepare('UPDATE tblauthors SET AuthorName = ? WHERE id = ?');
            $stmt->bind_param('si', $name, $id);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Author updated successfully.');
            redirect('manage-authors.php');
        }
    }
}

$stmt = $con->prepare('SELECT * FROM tblauthors WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$author = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$author) {
    set_flash('danger', 'Author not found.');
    redirect('manage-authors.php');
}

$pageTitle = 'Edit Author';
include 'includes/header.php';
?>
<div class="card" style="max-width:600px">
    <div class="card-header"><h3>Edit Author</h3><a href="manage-authors.php" class="btn btn-sm btn-secondary">Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="edit-author.php?id=<?php echo $id; ?>" autocomplete="off">
        <div class="form-group">
            <label for="author">Author Name <span class="req">*</span></label>
            <input type="text" id="author" name="author" value="<?php echo e($author['AuthorName']); ?>" required>
        </div>
        <button type="submit" class="btn">Update Author</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
