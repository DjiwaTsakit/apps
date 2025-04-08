<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Debug
    $mail->SMTPDebug = 2; 
    $mail->Debugoutput = 'html';

    // Konfigurasi SMTP Gmail
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'najibullasror@gmail.com';
    $mail->Password   = 'nzuzamiuvrrrraiy'; // password aplikasi Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Info pengirim & penerima
    $mail->setFrom('najibullasror@gmail.com', 'Verify System');
    $mail->addAddress('redwordlist@gmail.com', 'Target');

    // Konten email
    $mail->isHTML(true);
    $mail->Subject = 'Verifikasi Email';
    $mail->Body    = '<h3>Hai!</h3><p>Ini email verifikasi dari SMTP Gmail.</p>';

    $mail->send();
    echo '✅ Email berhasil dikirim!';
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
