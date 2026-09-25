<?php
require_once '../includes/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$error = '';

$stmt = $con->prepare('SELECT i.*, b.BookName, b.ISBNNumber, b.BookPrice, a.AuthorName, s.FullName, s.EmailId, s.MobileNumber
                        FROM tblissuedbookdetails i
                        JOIN tblbooks b ON b.id = i.BookId
                        LEFT JOIN tblauthors a ON a.id = b.AuthorId
                        LEFT JOIN tblstudents s ON s.StudentId = i.StudentId
                        WHERE i.id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$rec) {
    set_flash('danger', 'Issue record not found.');
    redirect('manage-issued-books.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$rec['ReturnStatus']) {
    $fine = (float)($_POST['fine'] ?? 0);
    if ($fine < 0) {
        $error = 'Fine cannot be negative.';
    } else {
        $stmt = $con->prepare('UPDATE tblissuedbookdetails SET ReturnStatus = 1, ReturnDate = NOW(), Fine = ? WHERE id = ?');
        $stmt->bind_param('di', $fine, $id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Book "' . $rec['BookName'] . '" marked as returned by ' . $rec['StudentId'] . '. Fine collected: Rs. ' . number_format($fine, 2));
        redirect('manage-issued-books.php');
    }
}

$overdue = $rec['ReturnStatus'] ? 0 : overdue_days($rec['DueDate']);

$pageTitle = $rec['ReturnStatus'] ? 'Issue Details' : 'Return Book';
include 'includes/header.php';
?>
<div class="card" style="max-width:760px">
    <div class="card-header"><h3><?php echo $rec['ReturnStatus'] ? 'Issued Book Details' : 'Update Return Details'; ?></h3><a href="manage-issued-books.php" class="btn btn-sm btn-secondary">Back</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <dl class="detail">
        <dt>Book Name</dt><dd><?php echo e($rec['BookName']); ?></dd>
        <dt>Author</dt><dd><?php echo e($rec['AuthorName']); ?></dd>
        <dt>ISBN</dt><dd><?php echo e($rec['ISBNNumber']); ?></dd>
        <dt>Student</dt><dd><?php echo e($rec['FullName'] ?? '-'); ?> (<a href="student-details.php?sid=<?php echo e($rec['StudentId']); ?>"><?php echo e($rec['StudentId']); ?></a>)</dd>
        <dt>Email / Mobile</dt><dd><?php echo e($rec['EmailId'] ?? '-'); ?> / <?php echo e($rec['MobileNumber'] ?? '-'); ?></dd>
        <dt>Issued On</dt><dd><?php echo format_dt($rec['IssueDate']); ?></dd>
        <dt>Due Date</dt><dd><?php echo format_d($rec['DueDate']); ?></dd>
        <dt>Status</dt>
        <dd>
            <?php if ($rec['ReturnStatus']): ?><span class="badge badge-success">Returned on <?php echo format_dt($rec['ReturnDate']); ?></span>
            <?php elseif ($overdue > 0): ?><span class="badge badge-danger">Overdue by <?php echo $overdue; ?> day(s)</span>
            <?php else: ?><span class="badge badge-warning">Not Returned</span><?php endif; ?>
        </dd>
        <?php if ($rec['ReturnStatus']): ?>
        <dt>Fine Collected</dt><dd>Rs. <?php echo number_format((float)$rec['Fine'], 2); ?></dd>
        <?php endif; ?>
    </dl>

    <?php if (!$rec['ReturnStatus']): ?>
    <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">
    <form method="post" action="update-issue.php?id=<?php echo $id; ?>">
        <input type="hidden" id="overdueDays" value="<?php echo $overdue; ?>">
        <input type="hidden" id="finePerDay" value="<?php echo FINE_PER_DAY; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>Return Date &amp; Time</label>
                <input type="text" value="<?php echo date('d M Y, h:i A'); ?> (now)" readonly>
            </div>
            <div class="form-group">
                <label for="fine">Fine (Rs.)</label>
                <input type="number" id="fine" name="fine" step="0.01" min="0" value="<?php echo number_format($overdue * FINE_PER_DAY, 2, '.', ''); ?>">
                <div class="help">Auto calculated: <?php echo $overdue; ?> overdue day(s) x Rs. <?php echo FINE_PER_DAY; ?>. You can change it.</div>
            </div>
        </div>
        <button type="submit" class="btn btn-success">Mark as Returned</button>
    </form>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
