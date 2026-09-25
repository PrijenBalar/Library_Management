<?php
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';

$stmt = $con->prepare('SELECT * FROM tblbooks WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$book = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$book) {
    set_flash('danger', 'Book not found.');
    redirect('manage-books.php');
}

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
        $stmt = $con->prepare('SELECT id FROM tblbooks WHERE ISBNNumber = ? AND id <> ?');
        $stmt->bind_param('si', $isbn, $id);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $error = 'Another book with this ISBN number already exists.';
        } else {
            [$newImage, $imgError] = save_book_image('bookimage');
            if ($imgError) {
                $error = $imgError;
            } else {
                $image = $book['BookImage'];
                if ($newImage) {                       // new picture uploaded: replace old one
                    delete_book_image($image);
                    $image = $newImage;
                } elseif (!empty($_POST['removeimage'])) { // admin ticked "remove picture"
                    delete_book_image($image);
                    $image = null;
                }
                $stmt = $con->prepare('UPDATE tblbooks SET BookName = ?, CatId = ?, AuthorId = ?, ISBNNumber = ?, BookPrice = ?, SellStock = ?, BookImage = ? WHERE id = ?');
                $stmt->bind_param('siisdisi', $name, $catId, $authId, $isbn, $price, $stock, $image, $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Book updated successfully.');
                redirect('manage-books.php');
            }
        }
    }
    // keep typed values after an error
    $book = array_merge($book, ['BookName' => $name, 'CatId' => $catId, 'AuthorId' => $authId, 'ISBNNumber' => $isbn, 'BookPrice' => $price, 'SellStock' => $stock]);
}

$categories = $con->query('SELECT id, CategoryName FROM tblcategory ORDER BY CategoryName')->fetch_all(MYSQLI_ASSOC);
$authors    = $con->query('SELECT id, AuthorName FROM tblauthors ORDER BY AuthorName')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Edit Book';
include 'includes/header.php';
?>
<div class="card" style="max-width:760px">
    <div class="card-header"><h3>Book Info</h3><a href="manage-books.php" class="btn btn-sm btn-secondary">Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <form method="post" action="edit-book.php?id=<?php echo $id; ?>" enctype="multipart/form-data" autocomplete="off">
        <div class="form-group">
            <label for="bookname">Book Name <span class="req">*</span></label>
            <input type="text" id="bookname" name="bookname" value="<?php echo e($book['BookName']); ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="category">Category <span class="req">*</span></label>
                <select id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int)$c['id']; ?>" <?php echo (int)$book['CatId'] === (int)$c['id'] ? 'selected' : ''; ?>><?php echo e($c['CategoryName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="author">Author <span class="req">*</span></label>
                <select id="author" name="author" required>
                    <option value="">-- Select Author --</option>
                    <?php foreach ($authors as $a): ?>
                        <option value="<?php echo (int)$a['id']; ?>" <?php echo (int)$book['AuthorId'] === (int)$a['id'] ? 'selected' : ''; ?>><?php echo e($a['AuthorName']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="isbn">ISBN Number <span class="req">*</span></label>
                <input type="text" id="isbn" name="isbn" value="<?php echo e($book['ISBNNumber']); ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price (Rs.) <span class="req">*</span></label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo e($book['BookPrice']); ?>" required>
            </div>
            <div class="form-group">
                <label for="sellstock">Copies for Sale</label>
                <input type="number" id="sellstock" name="sellstock" min="0" value="<?php echo (int)$book['SellStock']; ?>">
                <div class="help">0 = not sold in the Book Store.</div>
            </div>
        </div>
        <div class="form-group">
            <label>Book Picture</label>
            <div class="book-detail">
                <?php echo book_cover($book['BookImage'], $book['BookName'], '../'); ?>
                <div style="flex:1">
                    <input type="file" id="bookimage" name="bookimage" accept="image/jpeg,image/png,image/webp,image/gif" class="img-input">
                    <div class="help">Choose a new picture to replace the current one (JPG, PNG, WEBP or GIF, max 2 MB).</div>
                    <?php if ($book['BookImage']): ?>
                        <label style="font-weight:400;margin-top:8px"><input type="checkbox" name="removeimage" value="1"> Remove current picture</label>
                    <?php endif; ?>
                    <img id="imgPreview" class="img-preview" alt="" hidden>
                </div>
            </div>
        </div>
        <button type="submit" class="btn">Update Book</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
