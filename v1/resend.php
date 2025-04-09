<?php
require 'vendor/autoload.php';
require_once 'config/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Cek user di database
    $stmt = $conn->prepare("SELECT username, verify_token, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        if ($row['is_verified']) {
            echo "✅ Email sudah diverifikasi.";
        } else {
            $username = $row['username'];
            $token = $row['verify_token'];

            $verifyLink = "https://domainmu/verify.php?token=$token&email=$email";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'redwordlist@gmail.com';
                $mail->Password = 'PASSWORD_APLIKASI';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('EMAIL_KAMU@gmail.com', 'Verifikasi Ulang');
                $mail->addAddress($email, $username);
                $mail->Subject = "Kirim Ulang Verifikasi";
                $mail->Body = "Klik link ini untuk verifikasi akunmu:\n\n$verifyLink";

                $mail->send();
                echo "📧 Link verifikasi telah dikirim ulang.";
            } catch (Exception $e) {
                echo "❌ Gagal kirim ulang email: {$mail->ErrorInfo}";
            }
        }
    } else {
        echo "❌ Email tidak ditemukan.";
    }
}
?>
