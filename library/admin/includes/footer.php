<?php if (is_admin_logged_in()): ?>
        </main>
        <footer class="app-footer">&copy; <?php echo date('Y'); ?> Online Library Management System &mdash; Admin Panel</footer>
    </div>
</div>
<?php else: ?>
</main>
<footer class="guest-footer">&copy; <?php echo date('Y'); ?> Online Library Management System &mdash; College Project</footer>
<?php endif; ?>
<script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/main.js"></script>
</body>
</html>
