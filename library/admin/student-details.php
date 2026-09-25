<?php
require_once '../includes/functions.php';
require_admin();

$sid = strtoupper(trim($_GET['sid'] ?? ''));

$stmt = $con->prepare('SELECT * FROM tblstudents WHERE StudentId = ?');
$stmt->bind_param('s', $sid);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    set_flash('danger', 'Student not found.');
    redirect('reg-students.php');
}

$stmt = $con->prepare('SELECT i.*, b.BookName, b.ISBNNumber, a.AuthorName
                        FROM tblissuedbookdetails i
                        JOIN tblbooks b ON b.id = i.BookId
                        LEFT JOIN tblauthors a ON a.id = b.AuthorId
                        WHERE i.StudentId = ? ORDER BY i.IssueDate DESC');
$stmt->bind_param('s', $sid);
$stmt->execute();
$issues = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalFine = 0;
$pending = 0;
foreach ($issues as $r) {
    $totalFine += (float)$r['Fine'];
    if (!$r['ReturnStatus']) $pending++;
}

$pageTitle = 'Student Details';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h3>Student Profile</h3>
        <div>
            <a href="issue-book.php?sid=<?php echo e($sid); ?>" class="btn btn-sm btn-success">Issue Book to this Student</a>
            <a href="reg-students.php" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>
    <dl class="detail">
        <dt>Student ID</dt><dd><strong><?php echo e($student['StudentId']); ?></strong></dd>
        <dt>Full Name</dt><dd><?php echo e($student['FullName']); ?></dd>
        <dt>Email</dt><dd><?php echo e($student['EmailId']); ?></dd>
        <dt>Mobile</dt><dd><?php echo e($student['MobileNumber']); ?></dd>
        <dt>Status</dt><dd><?php echo $student['Status'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Blocked</span>'; ?></dd>
        <dt>Registered On</dt><dd><?php echo format_dt($student['RegDate']); ?></dd>
        <dt>Last Updated</dt><dd><?php echo format_dt($student['UpdationDate']); ?></dd>
        <dt>Books Pending</dt><dd><?php echo $pending; ?></dd>
        <dt>Total Fine Paid</dt><dd>Rs. <?php echo number_format($totalFine, 2); ?></dd>
    </dl>
</div>

<div class="card">
    <div class="card-header"><h3>Book Issue History (<?php echo count($issues); ?>)</h3></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Book</th><th>Author</th><th>ISBN</th><th>Issued On</th><th>Due Date</th><th>Returned On</th><th>Fine (Rs.)</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$issues): ?>
                <tr><td colspan="10" class="empty">No books issued to this student yet.</td></tr>
            <?php else: foreach ($issues as $i => $r): $od = overdue_days($r['DueDate']); ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['AuthorName']); ?></td>
                    <td><?php echo e($r['ISBNNumber']); ?></td>
                    <td><?php echo format_dt($r['IssueDate']); ?></td>
                    <td><?php echo format_d($r['DueDate']); ?></td>
                    <td><?php echo $r['ReturnStatus'] ? format_dt($r['ReturnDate']) : '-'; ?></td>
                    <td><?php echo number_format((float)$r['Fine'], 2); ?></td>
                    <td>
                        <?php if ($r['ReturnStatus']): ?><span class="badge badge-success">Returned</span>
                        <?php elseif ($od > 0): ?><span class="badge badge-danger">Overdue <?php echo $od; ?>d</span>
                        <?php else: ?><span class="badge badge-warning">Not Returned</span><?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if (!$r['ReturnStatus']): ?><a href="update-issue.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-success">Return</a>
                        <?php else: ?><a href="update-issue.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-secondary">View</a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
