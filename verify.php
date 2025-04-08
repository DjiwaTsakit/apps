<?php
session_start();
include 'config/db.php'; // Menghubungkan ke database

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Update status verifikasi pengguna
    $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Email Anda telah diverifikasi!";
    } else {
        echo "Token tidak valid atau sudah digunakan.";
    }

    $stmt->close();
}

$conn->close();
?>
