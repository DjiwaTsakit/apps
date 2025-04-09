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
            header("Location: v2/verify_success.html");
            exit;
        } else {
            $username = $row['username'];
            $token = $row['verify_token'];

            $verifyLink = "https://nasss.azurewebsites.net/verify.php?token=$token&email=$email";

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'redwordlist@gmail.com';
                $mail->Password = 'wzluxmvskspthkkm';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('no-replay@gmail.com', 'Verifikasi Ulang');
                $mail->addAddress($email, $username);
                $mail->Subject = "Kirim Ulang Verifikasi";
                $mail->Body = "Klik link ini untuk verifikasi akunmu:\n\n$verifyLink";

                $mail->send();
                header("Location: v2/verify_success.html");
                exit;
            } catch (Exception $e) {
                header("Location: v2/verify_failed.html");
                exit;
            }
        }
    } else {
                header("Location: v2/verify_failed.html");
                exit;
    }
}
?>
