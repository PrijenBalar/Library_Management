<?php
require_once '../includes/functions.php';
unset($_SESSION['admin_id'], $_SESSION['admin_name']);
set_flash('success', 'You have been logged out successfully.');
redirect('index.php');
