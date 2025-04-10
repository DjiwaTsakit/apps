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
