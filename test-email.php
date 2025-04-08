<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Konfigurasi SMTP produksi Mailtrap
    $mail->isSMTP();
    $mail->Host       = 'live.smtp.mailtrap.io';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'api'; // atau 'smtp@mailtrap.io'
    $mail->Password   = '202dc756cdf32b79fa7e4d61c0b9d346'; // Ganti dengan password asli
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Siapa yang kirim dan ke siapa
    $mail->setFrom('noreply@nasss.azurewebsites.net', 'Verifikasi Web');
    $mail->addAddress('redwordlist@gmail.com', 'Nama Penerima'); // Email nyata

    // Isi email
    $mail->isHTML(true);
    $mail->Subject = 'Halo dari Mailtrap Production!';
    $mail->Body    = 'Selamat! Email ini berhasil dikirim ke <b>dunia nyata</b>.';

    $mail->send();
    echo '✅ Email berhasil dikirim ke email nyata!';
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
