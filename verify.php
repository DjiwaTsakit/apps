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
            $message = "Email sudah diverifikasi sebelumnya.";
        } else {
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE email = ?");
            $update->execute([$email]);
            $message = "Verifikasi berhasil. Email Anda telah diverifikasi!";
        }
    } else {
        $message = "Token tidak valid atau email salah.";
    }
} else {
    $message = "Akses tidak valid. Tidak ada token atau email.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Email</title>
    <style>
        body { font-family: sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .box { background: white; padding: 20px 40px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); text-align: center; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Status Verifikasi</h2>
        <p><?= $message ?></p>
        <a href="index.html">Kembali ke Beranda</a>
    </div>
</body>
</html>
