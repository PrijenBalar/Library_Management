<?php
/**
 * AJAX: search students by ID / name / email (used on Search Student page)
 * Returns an HTML table.
 */
require_once '../../includes/functions.php';

if (!is_admin_logged_in()) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Not authorised.</div>';
    exit;
}

$q = trim($_GET['q'] ?? '');
$like = '%' . $q . '%';
$stmt = $con->prepare('SELECT s.*, (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.StudentId = s.StudentId AND i.ReturnStatus = 0) AS pending
                       FROM tblstudents s
                       WHERE s.StudentId LIKE ? OR s.FullName LIKE ? OR s.EmailId LIKE ? OR s.MobileNumber LIKE ?
                       ORDER BY s.FullName LIMIT 50');
$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (!$rows) {
    echo '<div class="alert alert-warning">No student found for "' . e($q) . '".</div>';
    exit;
}
?>
<p class="text-muted"><?php echo count($rows); ?> result(s) for "<?php echo e($q); ?>"</p>
<div class="table-wrap">
    <table class="table">
        <thead><tr><th>#</th><th>Student ID</th><th>Full Name</th><th>Email</th><th>Mobile</th><th>Books Pending</th><th>Status</th><th>Registered On</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $i => $s): ?>
            <tr>
                <td><?php echo $i + 1; ?></td>
                <td><?php echo e($s['StudentId']); ?></td>
                <td><?php echo e($s['FullName']); ?></td>
                <td><?php echo e($s['EmailId']); ?></td>
                <td><?php echo e($s['MobileNumber']); ?></td>
                <td><?php echo (int)$s['pending']; ?></td>
                <td><?php echo $s['Status'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Blocked</span>'; ?></td>
                <td><?php echo format_dt($s['RegDate']); ?></td>
                <td class="actions">
                    <a href="student-details.php?sid=<?php echo e($s['StudentId']); ?>" class="btn btn-sm">View Details</a>
                    <a href="issue-book.php?sid=<?php echo e($s['StudentId']); ?>" class="btn btn-sm btn-success">Issue Book</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
