<?php
require_once __DIR__ . '/vendor/autoload.php'; // Composer autoload
require_once 'config/db.php'; // Koneksi ke database

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ambil data dari form
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($password !== $confirm_password) {
    exit('❌ Password tidak cocok.');
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Generate token unik
$token = bin2hex(random_bytes(32));

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO users (username, email, password, token, verified) VALUES (?, ?, ?, ?, 0)");
$stmt->bind_param("ssss", $username, $email, $hashedPassword, $token);

if ($stmt->execute()) {
    // Konfigurasi PHPMailer
    $mail = new PHPMailer(true);
    try {
        // Konfigurasi SMTP Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'najibullasror@gmail.com';
        $mail->Password = 'tzdotbvnaffzoyi'; // Sandi aplikasi Gmail
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('najibullasror@gmail.com', 'Verify System');
        $mail->addAddress($email, $username);

        // Link verifikasi
        $verifyLink = "https://" . $_SERVER['HTTP_HOST'] . "/verify.php?token=$token";

        $mail->isHTML(true);
        $mail->Subject = 'Verifikasi Email Kamu';
        $mail->Body    = "Hai <b>$username</b>,<br><br>Silakan klik link berikut untuk verifikasi akun kamu:<br><a href='$verifyLink'>$verifyLink</a>";

        $mail->send();
        echo '✅ Registrasi berhasil. Cek email untuk verifikasi.';
    } catch (Exception $e) {
        echo '❌ Gagal kirim email: ' . $mail->ErrorInfo;
    }
} else {
    echo '❌ Gagal registrasi: ' . $stmt->error;
}
