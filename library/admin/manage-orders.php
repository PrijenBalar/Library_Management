<?php
require_once '../includes/functions.php';
require_admin();

$status = $_GET['status'] ?? 'All';
if (!in_array($status, order_statuses(), true)) {
    $status = 'All';
}

// Count per status for the filter buttons
$counts = ['All' => 0];
foreach ($con->query('SELECT OrderStatus, COUNT(*) AS c FROM tblorders GROUP BY OrderStatus') as $r) {
    $counts[$r['OrderStatus']] = (int)$r['c'];
    $counts['All'] += (int)$r['c'];
}

$sql = 'SELECT o.*, s.FullName, (SELECT COALESCE(SUM(i.Quantity), 0) FROM tblorderitems i WHERE i.OrderId = o.id) AS copies
        FROM tblorders o LEFT JOIN tblstudents s ON s.StudentId = o.StudentId';
if ($status !== 'All') {
    $stmt = $con->prepare($sql . ' WHERE o.OrderStatus = ? ORDER BY o.id DESC');
    $stmt->bind_param('s', $status);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $orders = $con->query($sql . ' ORDER BY o.id DESC')->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = 'Manage Book Orders';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header">
        <h3><?php echo e($status); ?> Orders (<?php echo count($orders); ?>)</h3>
        <div class="filter-tabs">
            <?php foreach (array_merge(['All'], order_statuses()) as $st): ?>
                <a href="manage-orders.php<?php echo $st === 'All' ? '' : '?status=' . urlencode($st); ?>" class="btn btn-sm <?php echo $status === $st ? '' : 'btn-outline'; ?>"><?php echo $st; ?> (<?php echo $counts[$st] ?? 0; ?>)</a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="search-bar"><input type="text" id="tableFilter" placeholder="Filter by order number, student, ID..."></div>
    <div class="table-wrap">
        <table class="table filter-table">
            <thead><tr><th>#</th><th>Order No.</th><th>Student</th><th>Student ID</th><th>Order Date</th><th>Books</th><th>Total</th><th>Payment Method</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php if (!$orders): ?>
                <tr><td colspan="11" class="empty">No orders found.</td></tr>
            <?php else: foreach ($orders as $i => $o): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo e($o['OrderNumber']); ?></strong></td>
                    <td><?php echo e($o['FullName'] ?? '-'); ?></td>
                    <td><a href="student-details.php?sid=<?php echo e($o['StudentId']); ?>"><?php echo e($o['StudentId']); ?></a></td>
                    <td><?php echo format_dt($o['OrderDate']); ?></td>
                    <td><?php echo (int)$o['copies']; ?></td>
                    <td><?php echo money($o['TotalAmount']); ?></td>
                    <td><?php echo e($o['PaymentMethod']); ?></td>
                    <td><?php echo payment_badge($o['PaymentStatus']); ?></td>
                    <td><?php echo order_badge($o['OrderStatus']); ?></td>
                    <td class="actions"><a href="order-details.php?id=<?php echo (int)$o['id']; ?>" class="btn btn-sm">Manage</a></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
