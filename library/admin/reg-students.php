<?php
require_once '../includes/functions.php';
require_admin();

// Block / unblock student
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $con->prepare('UPDATE tblstudents SET Status = IF(Status = 1, 0, 1) WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Student status updated.');
    redirect('reg-students.php');
}

$rows = $con->query('SELECT s.*, (SELECT COUNT(*) FROM tblissuedbookdetails i WHERE i.StudentId = s.StudentId AND i.ReturnStatus = 0) AS pending
                     FROM tblstudents s ORDER BY s.id DESC')->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Registered Students';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>Registered Students (<?php echo count($rows); ?>)</h3><a href="student-search.php" class="btn btn-sm">Search by Student ID</a></div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter by ID, name, email, mobile..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Student ID</th><th>Full Name</th><th>Email</th><th>Mobile</th><th>Books Pending</th><th>Status</th><th>Registered On</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="9" class="empty">No students registered yet.</td></tr>
            <?php else: foreach ($rows as $i => $s): ?>
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
                        <a href="student-details.php?sid=<?php echo e($s['StudentId']); ?>" class="btn btn-sm">View</a>
                        <a href="reg-students.php?toggle=<?php echo (int)$s['id']; ?>" class="btn btn-sm <?php echo $s['Status'] ? 'btn-danger' : 'btn-success'; ?>"><?php echo $s['Status'] ? 'Block' : 'Unblock'; ?></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
