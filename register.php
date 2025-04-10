<?php
require 'vendor/autoload.php';
require_once 'config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi Email (gunakan Mailtrap Production atau Gmail)
$emailSender = 'redwordlist@gmail.com'; // ganti kalau pakai Gmail
$emailPassword = 'wzluxmvskspthkkm'; // password aplikasi Gmail atau Mailtrap live
$emailSMTPHost = 'smtp.gmail.com'; // atau smtp.gmail.com
$emailSMTPPort = 587;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    if ($password !== $confirm_password) {
        die("❌ Password tidak cocok.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $verify_token = bin2hex(random_bytes(16));

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, verify_token, is_verified) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $verify_token);

    if ($stmt->execute()) {
        // Buat link verifikasi
        $verifyLink = "https://nasss.azurewebsites.net/verify.php?token=$verify_token&email=$email";

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $emailSMTPHost;
            $mail->SMTPAuth = true;
            $mail->Username = $emailSender;
            $mail->Password = $emailPassword;
            $mail->SMTPSecure = 'tls';
            $mail->Port = $emailSMTPPort;

            $mail->setFrom($emailSender, 'Register Bot');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
$mail->Subject = "Verifikasi Akun Anda - Lab Pentest";
$mail->Body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        h2 { color: #333; }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007BFF;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .btn-wrapper {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #888;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h2>Halo, $username!</h2>
        <p>Terima kasih telah mendaftar di <strong>Lab Pentest</strong>.</p>
        <p>Untuk mengaktifkan akun Anda, silakan klik tombol di bawah ini:</p>

        <div class='btn-wrapper'>
            <a href='$verifyLink' class='btn'>Verifikasi Akun</a>
        </div>

        <p>Jika tombol di atas tidak berfungsi, Anda juga dapat menyalin dan menempelkan tautan berikut ke peramban Anda:</p>
        <p><a href='$verifyLink'>$verifyLink</a></p>

        <div class='footer'>
            <p>Email ini dikirim secara otomatis. Mohon tidak membalas pesan ini.</p>
            <p>&copy; 2025 Lab Pentest. All rights reserved.</p>
        </div>
    </div>
</body>
</html>";

            $mail->send();
            header("Location: v2/success.html"); // redirect ke halaman sukses (bisa kamu buat)
            exit;
        } catch (Exception $e) {
            echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ Gagal register: " . $stmt->error;
    }
}
?>
