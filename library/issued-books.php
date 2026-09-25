<?php
require_once 'includes/functions.php';
require_student();

$sid = $_SESSION['student_id'];
$stmt = $con->prepare('SELECT i.*, b.BookName, b.ISBNNumber, b.BookImage, a.AuthorName
                        FROM tblissuedbookdetails i
                        JOIN tblbooks b ON b.id = i.BookId
                        LEFT JOIN tblauthors a ON a.id = b.AuthorId
                        WHERE i.StudentId = ?
                        ORDER BY i.IssueDate DESC');
$stmt->bind_param('s', $sid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = 'Issued Books';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h2>Books Issued to You</h2><span class="text-muted">Fine: Rs. <?php echo FINE_PER_DAY; ?> per day after due date</span></div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Cover</th><th>Book</th><th>Author</th><th>ISBN</th><th>Issued Date &amp; Time</th><th>Due Date</th><th>Returned Date &amp; Time</th><th>Fine (Rs.)</th><th>Status</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="10" class="empty">No books have been issued to you yet.</td></tr>
            <?php else: foreach ($rows as $i => $r): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo book_cover($r['BookImage'], $r['BookName'], '', 'thumb'); ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['AuthorName']); ?></td>
                    <td><?php echo e($r['ISBNNumber']); ?></td>
                    <td><?php echo format_dt($r['IssueDate']); ?></td>
                    <td><?php echo format_d($r['DueDate']); ?></td>
                    <td><?php echo $r['ReturnStatus'] ? format_dt($r['ReturnDate']) : '-'; ?></td>
                    <td><?php echo number_format((float)$r['Fine'], 2); ?></td>
                    <td>
                        <?php if ($r['ReturnStatus']): ?>
                            <span class="badge badge-success">Returned</span>
                        <?php elseif (overdue_days($r['DueDate']) > 0): ?>
                            <span class="badge badge-danger">Overdue (<?php echo overdue_days($r['DueDate']); ?> days)</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Not Returned</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
