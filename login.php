<?php
// File: login.php

session_start();
require_once 'config/db.php';

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

// Rate limiting - contoh sederhana
if (isset($_SESSION['last_login_attempt']) && (time() - $_SESSION['last_login_attempt'] < 5)) {
    die("Too many login attempts. Please wait 5 seconds.");
}

// Bersihkan input
$email = clean_input($_POST['email'], $mysqli);
$password = $_POST['password']; // Password tidak di-escape karena akan di-hash
$security_answer = clean_input($_POST['security_answer'], $mysqli);

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format");
}

// Query dengan prepared statement
$stmt = $mysqli->prepare("SELECT id, username, password, security_answer_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Verifikasi jawaban keamanan
        if (password_verify(strtolower($security_answer), strtolower($user['security_answer_hash']))) {
            // Login sukses
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['logged_in'] = true;
            
            // Regenerate session ID untuk mencegah session fixation
            session_regenerate_id(true);
            
            // Redirect ke halaman dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            die("Security answer incorrect");
        }
    } else {
        // Password salah
        $_SESSION['last_login_attempt'] = time();
        die("Invalid email or password");
    }
} else {
    // Email tidak ditemukan
    $_SESSION['last_login_attempt'] = time();
    die("Invalid email or password");
}

$stmt->close();
$mysqli->close();
?>
