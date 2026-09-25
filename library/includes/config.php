<?php
/**
 * Database configuration
 * ----------------------
 * Change these values if your MySQL settings are different.
 * Default XAMPP / WAMP settings are: host = localhost, user = root, password = (empty)
 */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$con) {
    die('<h2 style="font-family:Arial;color:#c0392b">Database connection failed: ' . mysqli_connect_error() .
        '</h2><p style="font-family:Arial">Make sure MySQL is running in XAMPP and the database <b>library_db</b> is imported (see read me/installation.txt).</p>');
}

mysqli_set_charset($con, 'utf8mb4');
date_default_timezone_set('Asia/Kolkata');
