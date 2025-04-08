use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);

try {
    // Konfigurasi Gmail SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'najibullasror@gmail.com'; // Email Gmail kamu
    $mail->Password   = 'nzuzamiuvrrrraiy';   // Password Aplikasi (bukan password akun Gmail)
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('najibullasror@gmail.com', 'Nama Kamu');
    $mail->addAddress('redwordlist@gmail.com', 'Target');

    $mail->isHTML(true);
    $mail->Subject = 'Verifikasi Email';
    $mail->Body    = 'Hai! Ini email verifikasi dari SMTP Gmail.';

    $mail->send();
    echo '✅ Email berhasil dikirim!';
} catch (Exception $e) {
    echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
}
