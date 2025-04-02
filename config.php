<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'bookstore');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Bookstore');
define('SITE_URL', 'http://localhost/bookstore');
define('ADMIN_EMAIL', 'admin@bookstore.com');

// File upload configuration
define('UPLOAD_DIR', 'assets/images/books/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>