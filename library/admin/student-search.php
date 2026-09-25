<?php
require_once '../includes/functions.php';
require_admin();

$pageTitle = 'Search Student';
include 'includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3>Search Student</h3><a href="reg-students.php" class="btn btn-sm btn-secondary">All Students</a></div>
    <form id="studentSearchForm" class="search-bar" autocomplete="off">
        <input type="text" id="searchStudent" placeholder="Enter Student ID (e.g. SID10001), name or email" value="<?php echo e($_GET['q'] ?? ''); ?>">
        <button type="submit" class="btn">Search</button>
    </form>
    <div id="searchResult"><p class="text-muted">Search results will appear here without reloading the page (AJAX).</p></div>
</div>
<?php if (!empty($_GET['q'])): ?>
<script>document.addEventListener('DOMContentLoaded', function () { setTimeout(function () { $('#studentSearchForm').trigger('submit'); }, 50); });</script>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
