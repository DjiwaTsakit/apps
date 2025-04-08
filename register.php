<?php
// File: register.php

// Mulai session dengan pengaturan yang lebih aman
session_start([
    'cookie_lifetime' => 86400, // 1 hari
    'cookie_secure'   => true,   // Hanya dikirim melalui HTTPS
    'cookie_httponly' => true,   // Tidak bisa diakses via JavaScript
    'use_strict_mode' => true    // Perlindungan session fixation
]);

// Include koneksi database
require_once 'config/db.php';

// Validasi request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    die("Method not allowed");
}

// Validasi CSRF token dengan cara yang lebih aman
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
    http_response_code(403); // Forbidden
    die("CSRF token missing");
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403); // Forbidden
    die("Invalid CSRF token");
}

// Bersihkan dan validasi input
$errors = [];
$username = isset($_POST['username']) ? clean_input($_POST['username'], $mysqli) : '';
$email = isset($_POST['email']) ? clean_input($_POST['email'], $mysqli) : '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$security_answer = isset($_POST['security_answer']) ? clean_input($_POST['security_answer'], $mysqli) : '';

// Validasi username
if (empty($username)) {
    $errors[] = "Username is required";
} elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    $errors[] = "Username must be 3-20 characters and can only contain letters, numbers, and underscore";
}

// Validasi email
if (empty($email)) {
    $errors[] = "Email is required";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}

// Validasi password
if (empty($password)) {
    $errors[] = "Password is required";
} elseif (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters";
} elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one uppercase letter, one lowercase letter and one number";
} elseif ($password !== $confirm_password) {
    $errors[] = "Passwords do not match";
}

// Validasi security answer
if (empty($security_answer)) {
    $errors[] = "Security answer is required";
} elseif (strlen($security_answer) < 3) {
    $errors[] = "Security answer must be at least 3 characters";
}

// Jika ada error, tampilkan dalam format JSON
if (!empty($errors)) {
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    die(json_encode(['success' => false, 'errors' => $errors]));
}

try {
    // Hash password dan security answer
    $password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $security_answer_hash = password_hash(strtolower($security_answer), PASSWORD_BCRYPT, ['cost' => 12]);

    // Cek apakah email sudah terdaftar (dalam transaction)
    $mysqli->begin_transaction();

    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? FOR UPDATE");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $mysqli->rollback();
        http_response_code(409); // Conflict
        die("Email already registered");
    }

    // Insert user baru dengan prepared statement
    $stmt = $mysqli->prepare("INSERT INTO users (username, email, password, security_answer_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $username, $email, $password_hash, $security_answer_hash);

    if (!$stmt->execute()) {
        $mysqli->rollback();
        http_response_code(500); // Internal Server Error
        die("Registration failed: Database error");
    }

    $mysqli->commit();

    // Registrasi sukses
    $_SESSION['registration_success'] = true;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Regenerate CSRF token setelah sukses

    // Response sukses
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'redirect' => 'login.php']);
    exit();

} catch (Exception $e) {
    if (isset($mysqli)) {
        $mysqli->rollback();
    }
    error_log("Registration error: " . $e->getMessage());
    http_response_code(500);
    die("An error occurred during registration. Please try again.");
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?>
