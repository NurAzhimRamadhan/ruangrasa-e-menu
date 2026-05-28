<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Diubah menjadi 'root' agar sesuai dengan XAMPP lokal
define('DB_PASS', '');              // Dikosongkan karena MySQL bawaan XAMPP tidak pakai password
define('DB_NAME', 'ruangrasa');

// Create connection
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($koneksi->connect_error) {
    error_log("Database connection failed: " . $koneksi->connect_error);
    die("Terjadi kesalahan sistem. Silakan hubungi administrator.");
}

// Set charset
$koneksi->set_charset("utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load security helper functions
require_once __DIR__ . '/security.php';
?>