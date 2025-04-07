<?php
session_start();

// Set security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://www.google.com https://www.gstatic.com; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://www.google.com");

// Rate limiting - maksimal 5 percobaan dalam 5 menit
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_attempt_time'] = time();
}

if ($_SESSION['login_attempts'] >= 5) {
    if (time() - $_SESSION['first_attempt_time'] < 300) { // 5 menit
        die(json_encode(['success' => false, 'message' => 'Terlalu banyak percobaan login. Silakan coba lagi nanti.']));
    } else {
        // Reset counter setelah 5 menit
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_attempt_time'] = time();
    }
}

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
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$password = $_POST['password'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'Format email tidak valid.']));
}

// Cari user di database
$stmt = $pdo->prepare("SELECT id, username, email, password, last_login, failed_attempts FROM users WHERE email = :email LIMIT 1");
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['login_attempts']++;
    die(json_encode(['success' => false, 'message' => 'Email atau password salah.']));
}

// Cek apakah akun terkunci karena terlalu banyak percobaan gagal
if ($user['failed_attempts'] >= 5 && strtotime($user['last_login']) > time() - 300) {
    die(json_encode(['success' => false, 'message' => 'Akun terkunci sementara karena terlalu banyak percobaan login gagal. Silakan coba lagi nanti.']));
}

// Verifikasi password
if (password_verify($password, $user['password'])) {
    // Reset failed attempts
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = 0, last_login = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID untuk mencegah session fixation
    session_regenerate_id(true);
    
    // Reset login attempts counter
    $_SESSION['login_attempts'] = 0;
    
    echo json_encode(['success' => true, 'message' => 'Login berhasil!', 'redirect' => '/dashboard.php']);
} else {
    // Tambahkan failed attempt
    $stmt = $pdo->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_login = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $_SESSION['login_attempts']++;
    die(json_encode(['success' => false, 'message' => 'Email atau password salah.']));
}
?>