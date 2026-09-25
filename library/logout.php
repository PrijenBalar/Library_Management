<?php
require_once 'includes/functions.php';
unset($_SESSION['student_pk'], $_SESSION['student_id'], $_SESSION['student_name']);
set_flash('success', 'You have been logged out successfully.');
redirect('index.php');
