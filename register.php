<?php
require_once __DIR__ . '/vendor/autoload.php'; // untuk load dotenv

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'vendor/autoload.php';
require_once 'config/db.php';
require_once 'config/dotenv.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Baca variabel dari .env
$emailSender = getenv('EMAIL_USERNAME');
$emailPassword = getenv('EMAIL_PASSWORD');

// Proses form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi awal
    if ($password !== $confirm_password) {
        die("❌ Password tidak cocok.");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Token verifikasi
    $verify_token = bin2hex(random_bytes(16));

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, verify_token, is_verified) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $verify_token);

    if ($stmt->execute()) {
        // Kirim email verifikasi
        $verifyLink = "https://sss.azurewebsites.net/verify.php?token=$verify_token&email=$email";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $emailSender;
            $mail->Password = $emailPassword;
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom($emailSender, 'Register Bot');
            $mail->addAddress($email, $username);
            $mail->Subject = "Verifikasi Akunmu";
            $mail->Body = "Klik link ini untuk verifikasi akunmu:\n\n$verifyLink";

            $mail->send();
            echo "✅ Register berhasil. Silakan cek email untuk verifikasi.";
        } catch (Exception $e) {
            echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ Gagal register: " . $stmt->error;
    }
}
?>
