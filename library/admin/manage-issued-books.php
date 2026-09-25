<?php
require_once '../includes/functions.php';
require_admin();

$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'issued') {
    $where = 'WHERE i.ReturnStatus = 0';
} elseif ($filter === 'returned') {
    $where = 'WHERE i.ReturnStatus = 1';
} else {
    $filter = 'all';
}

$rows = $con->query("SELECT i.*, b.BookName, b.ISBNNumber, s.FullName
                     FROM tblissuedbookdetails i
                     JOIN tblbooks b ON b.id = i.BookId
                     LEFT JOIN tblstudents s ON s.StudentId = i.StudentId
                     $where
                     ORDER BY i.ReturnStatus ASC, i.IssueDate DESC")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Issued Books';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h3>Issued Books (<?php echo count($rows); ?>)</h3>
        <div>
            <a href="manage-issued-books.php" class="btn btn-sm <?php echo $filter === 'all' ? '' : 'btn-secondary'; ?>">All</a>
            <a href="manage-issued-books.php?filter=issued" class="btn btn-sm <?php echo $filter === 'issued' ? '' : 'btn-secondary'; ?>">Not Returned</a>
            <a href="manage-issued-books.php?filter=returned" class="btn btn-sm <?php echo $filter === 'returned' ? '' : 'btn-secondary'; ?>">Returned</a>
            <a href="issue-book.php" class="btn btn-sm btn-success">+ Issue Book</a>
        </div>
    </div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter by book, student, ID, ISBN..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Book</th><th>ISBN</th><th>Student</th><th>Student ID</th><th>Issued On</th><th>Due Date</th><th>Returned On</th><th>Fine (Rs.)</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="11" class="empty">No records found.</td></tr>
            <?php else: foreach ($rows as $i => $r): $od = overdue_days($r['DueDate']); ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo e($r['BookName']); ?></td>
                    <td><?php echo e($r['ISBNNumber']); ?></td>
                    <td><?php echo e($r['FullName'] ?? '-'); ?></td>
                    <td><a href="student-details.php?sid=<?php echo e($r['StudentId']); ?>"><?php echo e($r['StudentId']); ?></a></td>
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
                        <?php if (!$r['ReturnStatus']): ?>
                            <a href="update-issue.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-success">Return Book</a>
                        <?php else: ?>
                            <a href="update-issue.php?id=<?php echo (int)$r['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
