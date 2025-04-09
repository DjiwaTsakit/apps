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
            $mail->Subject = "Verifikasi Akunmu";
            $mail->Body = "<h3>Halo, $username!</h3><p>Klik link berikut untuk verifikasi akunmu:</p><a href='$verifyLink'>$verifyLink</a>";

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
