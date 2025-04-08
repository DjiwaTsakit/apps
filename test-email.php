<?php
require_once __DIR__ . '/vendor/autoload.php'; // pastikan kamu sudah install PHPMailer via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Konfigurasi SMTP Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'najibullasror@gmail.com'; // Ganti dengan email kamu
    $mail->Password = 'tzdotbvnaffzoyi'; // Ganti dengan password aplikasi (16 karakter)
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Siapa pengirim dan penerima
    $mail->setFrom('najibullasror@gmail.com', 'Tester');
    $mail->addAddress('najibullasror@gmail.com', 'Najib'); // Kirim ke email kamu sendiri dulu

    // Isi email
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = 'Halo! Ini hanya email testing dari <b>PHPMailer</b>.';

    $mail->send();
    echo '✅ Email berhasil dikirim!';
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
