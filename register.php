<?php
require 'config/db.php';
require 'vendor/autoload.php'; // pastikan sudah install PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        die("Password dan konfirmasi tidak sama.");
    }

    $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        die("Email sudah terdaftar.");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verify_token, is_verified) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$username, $email, $hashed_password, $token]);

    // Kirim email verifikasi
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv("EMAIL_USERNAME"); // simpan di .env
        $mail->Password   = getenv("EMAIL_PASSWORD");
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom(getenv("EMAIL_USERNAME"), 'Verifikasi Akun');
        $mail->addAddress($email, $username);
        $mail->isHTML(true);
        $mail->Subject = 'Verifikasi Email';

        $verify_link = "https://nasss.azurewebsites.net/verify.php?email=$email&token=$token";
        $mail->Body = "Halo $username,<br><br>Klik link berikut untuk verifikasi akun Anda:<br><a href='$verify_link'>$verify_link</a>";

        $mail->send();
        echo "Pendaftaran berhasil. Silakan cek email untuk verifikasi.";
    } catch (Exception $e) {
        echo "Email gagal dikirim. Error: {$mail->ErrorInfo}";
    }
} else {
    echo "Akses tidak valid.";
}
?>
