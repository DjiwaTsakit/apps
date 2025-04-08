<?php
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Konfigurasi SMTP Mailtrap
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = 'ff77ba2c954116'; // Ganti dengan username Mailtrap kamu
    $mail->Password = '8846831a1fbce7'; // Ganti dengan password asli Mailtrap kamu
    $mail->SMTPSecure = 'tls'; // atau 'ssl'
    $mail->Port = 587; // Bisa juga 2525 atau 465

    // Dari siapa dan ke siapa
    $mail->setFrom('no-reply@nasss.azurewebsites.net', 'Mailtrap Tester');
    $mail->addAddress('redwordlist@nasss.azurewebsites.net', 'Test Recipient'); // Email fiktif, Mailtrap gak kirim ke dunia nyata

    $mail->isHTML(true);
    $mail->Subject = 'Testing Mailtrap';
    $mail->Body    = 'Halo! Ini adalah <b>test email</b> dari PHPMailer via Mailtrap.';

    $mail->send();
    echo '✅ Email berhasil dikirim ke Mailtrap!';
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
