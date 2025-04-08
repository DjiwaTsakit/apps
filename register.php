<?php
session_start();
include 'config/db.php'; // Menghubungkan ke database

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $security_question = $_POST['security_question'];
    $security_answer = $_POST['security_answer'];
    $token = bin2hex(random_bytes(50)); // Generate token untuk verifikasi

    // Simpan data pengguna ke database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, security_question, security_answer, token, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssssss", $username, $email, $password, $security_question, $security_answer, $token);
    
    if ($stmt->execute()) {
        // Kirim email verifikasi
        $to = $email;
        $subject = "Verifikasi Email";
        $message = "Klik link berikut untuk memverifikasi email Anda: ";
        $message .= "http://yourdomain.com/verify.php?token=" . $token; // Ganti dengan domain Anda
        $headers = "From: no-reply@yourdomain.com";

        mail($to, $subject, $message, $headers);

        echo "Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
