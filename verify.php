<?php
require_once 'config/db.php';

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    // Cek apakah token cocok dengan database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND verify_token = ? AND is_verified = 0");
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Token valid, update status verifikasi
        $update = $conn->prepare("UPDATE users SET is_verified = 1, verify_token = NULL WHERE email = ?");
        $update->bind_param("s", $email);
        if ($update->execute()) {
            echo "<h2 style='color:green;'>✅ Email kamu sudah diverifikasi!</h2>";
        } else {
            echo "<h2 style='color:red;'>❌ Gagal memperbarui status verifikasi.</h2>";
        }
    } else {
        echo "<h2 style='color:red;'>❌ Token tidak valid atau sudah digunakan.</h2>";
    }
} else {
    echo "<h2 style='color:red;'>❌ Permintaan tidak valid.</h2>";
}
?>
