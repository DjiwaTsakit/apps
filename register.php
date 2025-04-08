<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    $question   = $_POST['security_question'];
    $answer     = trim($_POST['security_answer']);

    if ($password !== $confirm) {
        die("Password tidak cocok!");
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, security_question, security_answer, verify_token) 
                           VALUES (?, ?, ?, ?, ?, ?)");

    try {
        $stmt->execute([$username, $email, $hash, $question, $answer, $token]);

        // Kirim email verifikasi
        $subject = "Verifikasi Email Anda";
        $message = "Hai $username, klik link berikut untuk verifikasi email kamu:\n\n";
        $message .= "https://nasss.azurewebsites.net/verify.php?email=$email&token=$token";
        $headers = "From: noreply@nasss.azurewebsites.net";

        if (mail($email, $subject, $message, $headers)) {
            echo "<script>alert('Registrasi berhasil! Silakan cek email untuk verifikasi.'); window.location.href='index.html';</script>";
        } else {
            echo "Gagal mengirim email.";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
