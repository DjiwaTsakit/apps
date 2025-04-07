<?php
session_start();

// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://www.google.com");

// Validasi CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']));
}

// Validasi reCAPTCHA
$recaptcha_secret = "6Le9IA0rAAAAAN8poyyUvKjEuNsUNqjAkf49c90G";
$recaptcha_response = $_POST['g-recaptcha-response'];

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = [
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_response,
    'remoteip' => $_SERVER['REMOTE_ADDR']
];

$recaptcha_options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => http_build_query($recaptcha_data)
    ]
];

$recaptcha_context = stream_context_create($recaptcha_options);
$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
$recaptcha_json = json_decode($recaptcha_result);

if (!$recaptcha_json->success) {
    die(json_encode(['success' => false, 'message' => 'Verifikasi reCAPTCHA gagal.']));
}

// Koneksi database (gunakan PDO untuk keamanan)
try {
    $pdo = new PDO('mysql:host=localhost;dbname=your_database;charset=utf8', 'db_username', 'db_password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Koneksi database gagal.']));
}

// Validasi input
$username = trim($_POST['username']);
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validasi username
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    die(json_encode(['success' => false, 'message' => 'Username harus 3-20 karakter dan hanya boleh mengandung huruf, angka, dan underscore.']));
}

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'Format email tidak valid.']));
}

// Validasi password
if ($password !== $confirm_password) {
    die(json_encode(['success' => false, 'message' => 'Password dan konfirmasi password tidak cocok.']));
}

if (strlen($password) < 8) {
    die(json_encode(['success' => false, 'message' => 'Password minimal 8 karakter.']));
}

if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    die(json_encode(['success' => false, 'message' => 'Password harus mengandung setidaknya satu huruf besar, satu huruf kecil, dan satu angka.']));
}

// Cek apakah email sudah terdaftar
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->fetch()) {
    die(json_encode(['success' => false, 'message' => 'Email sudah terdaftar.']));
}

// Cek apakah username sudah terdaftar
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username LIMIT 1");
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->fetch()) {
    die(json_encode(['success' => false, 'message' => 'Username sudah terdaftar.']));
}

// Hash password
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Insert user baru ke database
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at, updated_at) VALUES (:username, :email, :password, NOW(), NOW())");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
    $stmt->execute();
    
    // Dapatkan ID user yang baru dibuat
    $user_id = $pdo->lastInsertId();
    
    // Set session
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID untuk mencegah session fixation
    session_regenerate_id(true);
    
    echo json_encode(['success' => true, 'message' => 'Registrasi berhasil!', 'redirect' => '/dashboard.php']);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi.']));
}
?>