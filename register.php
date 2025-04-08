<?php
require_once 'config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm_password) {
        echo 'Password dan konfirmasi tidak sama.';
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, verified) VALUES (?, ?, ?, 0)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    try {
        $stmt->execute();

        // Kirim email
        $token = bin2hex(random_bytes(16));
        $verify_link = "https://sss.azurewebsites.net/verify.php?token=$token&email=$email";

        // Simpan token ke database
        $stmt_token = $conn->prepare("UPDATE users SET token = ? WHERE email = ?");
        $stmt_token->bind_param("ss", $token, $email);
        $stmt_token->execute();

        $mail = new PHPMailer(true);

// Load dotenv (harus sudah ada sebelumnya)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Konfigurasi SMTP Gmail
$mail->isSMTP();
$mail->Host       = $_ENV['MAIL_HOST'];
$mail->SMTPAuth   = true;
$mail->Username   = $_ENV['MAIL_USERNAME'];
$mail->Password   = $_ENV['MAIL_PASSWORD']; // App Password Gmail
$mail->SMTPSecure = 'tls';
$mail->Port       = $_ENV['MAIL_PORT'];

$mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
$mail->addAddress($email, $username); // Tujuan
        $mail->isHTML(true);
        $mail->Subject = 'Verifikasi Email';
        $mail->Body = "Klik link berikut untuk verifikasi email Anda: <a href='$verify_link'>$verify_link</a>";

        $mail->send();
        echo '✅ Register sukses. Cek email untuk verifikasi.';
    } catch (Exception $e) {
        echo '❌ Gagal kirim email: ' . $mail->ErrorInfo;
    } catch (mysqli_sql_exception $e) {
        echo '❌ Gagal menyimpan data: ' . $e->getMessage();
    }
}
?>
