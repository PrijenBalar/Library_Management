<?php
require_once '../includes/functions.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['bookname'] ?? '');
    $catId  = (int)($_POST['category'] ?? 0);
    $authId = (int)($_POST['author'] ?? 0);
    $isbn   = trim($_POST['isbn'] ?? '');
    $price  = (float)($_POST['price'] ?? 0);
    $stock  = (int)($_POST['sellstock'] ?? 0);

    if ($name === '' || $isbn === '' || $catId <= 0 || $authId <= 0) {
        $error = 'Book name, category, author and ISBN are required.';
    } elseif ($price < 0 || $stock < 0) {
        $error = 'Price and copies for sale cannot be negative.';
    } else {
        $stmt = $con->prepare('SELECT id FROM tblbooks WHERE ISBNNumber = ?');
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'A book with this ISBN number already exists.';
        } else {
            [$image, $imgError] = save_book_image('bookimage');
            if ($imgError) {
                $error = $imgError;
            } else {
                $stmt = $con->prepare('INSERT INTO tblbooks (BookName, CatId, AuthorId, ISBNNumber, BookPrice, SellStock, BookImage) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('siisdis', $name, $catId, $authId, $isbn, $price, $stock, $image);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Book "' . $name . '" added successfully.');
                redirect('manage-books.php');
            }
        }
    }
}

$categories = $con->query('SELECT id, CategoryName FROM tblcategory WHERE Status = 1 ORDER BY CategoryName')->fetch_all(MYSQLI_ASSOC);
$authors    = $con->query('SELECT id, AuthorName FROM tblauthors ORDER BY AuthorName')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Add Book';
include 'includes/header.php';
?>
<div class="card" style="max-width:760px">
    <div class="card-header"><h3>Book Info</h3><a href="manage-books.php" class="btn btn-sm btn-secondary">Manage Books</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <?php if (!$categories || !$authors): ?>
        <div class="alert alert-warning">Please add at least one <a href="add-category.php">category</a> and one <a href="add-author.php">author</a> before adding a book.</div>
    <?php endif; ?>
    <form method="post" action="add-book.php" enctype="multipart/form-data" autocomplete="off">
        <div class="form-group">
            <label for="bookname">Book Name <span class="req">*</span></label>
            <input type="text" id="bookname" name="bookname" value="<?php echo e($_POST['bookname'] ?? ''); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="req">*</span></label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)($_POST['category'] ?? 0) === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['CategoryName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="author">Author <span class="req">*</span></label>
                <select id="author" name="author" required>
                    <option value="">-- Select Author --</option>
                    <?php foreach ($authors as $a): ?>
                        <option value="<?php echo (int)$a['id']; ?>" <?php echo (int)($_POST['author'] ?? 0) === (int)$a['id'] ? 'selected' : ''; ?>><?php echo e($a['AuthorName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="isbn">ISBN Number <span class="req">*</span></label>
                <input type="text" id="isbn" name="isbn" value="<?php echo e($_POST['isbn'] ?? ''); ?>" required>
                <div class="help">Unique number used to identify the book when issuing.</div>
            </div>
            <div class="form-group">
                <label for="price">Price (Rs.) <span class="req">*</span></label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo e($_POST['price'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="sellstock">Copies for Sale</label>
                <input type="number" id="sellstock" name="sellstock" min="0" value="<?php echo e($_POST['sellstock'] ?? '0'); ?>">
                <div class="help">0 = not sold in the Book Store.</div>
            </div>
        </div>
        <div class="form-group">
            <label for="bookimage">Book Picture</label>
            <input type="file" id="bookimage" name="bookimage" accept="image/jpeg,image/png,image/webp,image/gif" class="img-input">
            <div class="help">JPG, PNG, WEBP or GIF. Maximum 2 MB. Optional.</div>
            <img id="imgPreview" class="img-preview" alt="" hidden>
        </div>
        <button type="submit" class="btn">Add Book</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
