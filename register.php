<?php
// File: register.php

session_start();
require_once 'config/db.php';

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}

// Bersihkan input
$username = clean_input($_POST['username'], $mysqli);
$email = clean_input($_POST['email'], $mysqli);
$password = $_POST['password']; // Password tidak di-escape karena akan di-hash
$confirm_password = $_POST['confirm_password'];
$security_answer = clean_input($_POST['security_answer'], $mysqli);

// Validasi input
$errors = [];

// Validasi username
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors[] = "Username must be 3-20 characters and can only contain letters, numbers, and underscore";
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Validasi password
if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
}

if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter, one lowercase letter and one number";
}

// Validasi security answer
if (strlen($security_answer) < 3) {
    $errors[] = "Security answer must be at least 3 characters";
}

// Jika ada error, tampilkan
if (!empty($errors)) {
    die(implode("<br>", $errors));
}

// Hash password dan security answer
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$security_answer_hash = password_hash(strtolower($security_answer), PASSWORD_BCRYPT);

// Cek apakah email sudah terdaftar
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die("Email already registered");
}

// Insert user baru dengan prepared statement
$stmt = $mysqli->prepare("INSERT INTO users (username, email, password, security_answer_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("ssss", $username, $email, $password_hash, $security_answer_hash);

if ($stmt->execute()) {
    // Registrasi sukses
    $_SESSION['registration_success'] = true;
    header("Location: login.php");
    exit();
} else {
    die("Registration failed: " . $mysqli->error);
}

$stmt->close();
$mysqli->close();
?>
