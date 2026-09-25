<?php
require_once '../includes/functions.php';
require_admin();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid     = strtoupper(trim($_POST['studentid'] ?? ''));
    $isbn    = trim($_POST['isbn'] ?? '');
    $dueDate = trim($_POST['duedate'] ?? '');

    if ($sid === '' || $isbn === '' || $dueDate === '') {
        $error = 'Student ID, ISBN number and return date are required.';
    } else {
        // Validate student
        $stmt = $con->prepare('SELECT StudentId, FullName, Status FROM tblstudents WHERE StudentId = ?');
        $stmt->bind_param('s', $sid);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Validate book
        $stmt = $con->prepare('SELECT id, BookName FROM tblbooks WHERE ISBNNumber = ?');
        $stmt->bind_param('s', $isbn);
        $stmt->execute();
        $book = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$student) {
            $error = 'No student found with Student ID "' . $sid . '".';
        } elseif ((int)$student['Status'] !== 1) {
            $error = 'This student account is blocked. Unblock the student before issuing a book.';
        } elseif (!$book) {
            $error = 'No book found with ISBN "' . $isbn . '".';
        } elseif (strtotime($dueDate) === false || strtotime($dueDate) < strtotime(date('Y-m-d'))) {
            $error = 'Return date must be today or a future date.';
        } else {
            // Check the book is not already issued
            $stmt = $con->prepare('SELECT StudentId FROM tblissuedbookdetails WHERE BookId = ? AND ReturnStatus = 0');
            $stmt->bind_param('i', $book['id']);
            $stmt->execute();
            $already = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($already) {
                $error = 'This book is already issued to student ' . $already['StudentId'] . ' and has not been returned yet.';
            } else {
                $stmt = $con->prepare('INSERT INTO tblissuedbookdetails (BookId, StudentId, DueDate, ReturnStatus, Fine) VALUES (?, ?, ?, 0, 0)');
                $stmt->bind_param('iss', $book['id'], $sid, $dueDate);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Book "' . $book['BookName'] . '" issued to ' . $student['FullName'] . ' (' . $sid . '). Return by ' . format_d($dueDate) . '.');
                redirect('manage-issued-books.php');
            }
        }
    }
}

$defaultDue = date('Y-m-d', strtotime('+' . ISSUE_DAYS . ' days'));

$pageTitle = 'Issue New Book';
include 'includes/header.php';
?>
<div class="card" style="max-width:720px">
    <div class="card-header"><h3>Issue a Book to Student</h3><a href="manage-issued-books.php" class="btn btn-sm btn-secondary">Manage Issued Books</a></div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>
    <div class="alert alert-info">Type the Student ID and the book ISBN. The student name and book name are fetched automatically (AJAX).</div>
    <form method="post" action="issue-book.php" autocomplete="off">
        <div class="form-row">
            <div class="form-group">
                <label for="studentid">Student ID <span class="req">*</span></label>
                <input type="text" id="studentid" name="studentid" placeholder="e.g. SID10001" value="<?php echo e($_POST['studentid'] ?? ($_GET['sid'] ?? '')); ?>" required>
                <div id="studentHint" class="input-hint"></div>
            </div>
            <div class="form-group">
                <label for="studentName">Student Name</label>
                <input type="text" id="studentName" readonly placeholder="Auto filled">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="isbn">Book ISBN Number <span class="req">*</span></label>
                <input type="text" id="isbn" name="isbn" placeholder="e.g. 9780132350884" value="<?php echo e($_POST['isbn'] ?? ''); ?>" required>
                <div id="bookHint" class="input-hint"></div>
            </div>
            <div class="form-group">
                <label for="bookName">Book Name</label>
                <input type="text" id="bookName" readonly placeholder="Auto filled">
                <input type="hidden" id="bookId">
            </div>
        </div>
        <div class="form-group" style="max-width:300px">
            <label for="duedate">Return Date <span class="req">*</span></label>
            <input type="date" id="duedate" name="duedate" value="<?php echo e($_POST['duedate'] ?? $defaultDue); ?>" min="<?php echo date('Y-m-d'); ?>" required>
            <div class="help">Default is <?php echo ISSUE_DAYS; ?> days from today. Fine after this date: Rs. <?php echo FINE_PER_DAY; ?>/day.</div>
        </div>
        <button type="submit" class="btn">Issue Book</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
