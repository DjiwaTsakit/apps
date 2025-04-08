<?php
require 'config/db.php';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verify_token = ?");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if ($user) {
        if ($user['is_verified']) {
            echo "Email sudah diverifikasi.";
        } else {
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE email = ?");
            $update->execute([$email]);
            echo "Email berhasil diverifikasi!";
        }
    } else {
        echo "Token tidak valid atau email salah.";
    }
}
?>
