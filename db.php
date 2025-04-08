<?php
// File: config/db.php

// Disable error reporting in production
error_reporting(0);

// Database configuration
define('DB_HOST', 'sql12.freesqldatabase.com');
define('DB_USERNAME', 'sql12771446');
define('DB_PASSWORD', 'HUep3PG6mT');
define('DB_NAME', 'sql12771446');
define('DB_PORT', 3306);

// Membuat koneksi database dengan MySQLi
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if ($mysqli->connect_errno) {
    // Log error ke file daripada menampilkan ke user
    error_log("Database connection failed: " . $mysqli->connect_error);
    die("Maaf, sedang ada masalah teknis. Silakan coba lagi nanti.");
}

// Set charset untuk mencegah SQL injection
$mysqli->set_charset("utf8mb4");

// Fungsi untuk membersihkan input
function clean_input($data, $mysqli) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $mysqli->real_escape_string($data);
}
?>
