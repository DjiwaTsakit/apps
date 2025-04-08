<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Halaman Register</h2>";

// Coba include register.php
if (file_exists("register.php")) {
    echo "✅ File register.php ditemukan.<br>";
} else {
    echo "❌ File register.php TIDAK ditemukan!<br>";
}

// Coba konek ke DB
try {
    require 'config/db.php';
    echo "✅ Koneksi ke database berhasil.<br>";
} catch (PDOException $e) {
    echo "❌ Gagal konek database: " . $e->getMessage() . "<br>";
}

// Coba simulasikan POST
echo "<h3>Simulasi Kirim POST ke register.php</h3>";

echo '<form method="POST" action="register.php">
  <input type="text" name="username" placeholder="Username" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm_password" placeholder="Konfirmasi Password" required>
  <button type="submit">Test Daftar</button>
</form>';

echo "<br><small>Kalau form ini tampil dan bisa di-submit, kemungkinan besar masalah bukan di routing.</small>";
?>
