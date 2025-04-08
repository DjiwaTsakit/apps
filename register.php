<?php
// File: register.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// Koneksi database
$host = 'sql12.freesqldatabase.com';
$db   = 'sql12771446';
$user = 'sql12771446';
$pass = 'HUep3PG6mT';
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $token = bin2hex(random_bytes(16));

    $stmt = $conn->prepare("INSERT INTO users (username, email, password, token, verified) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $username, $email, $password, $token);

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'najibullasror@gmail.com';
            $mail->Password = 'nzuzamiuvrrrraiy'; // App Password Gmail
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('najibullasror@gmail.com', 'Verify System');
            $mail->addAddress($email, $username);
            $mail->isHTML(true);
            $mail->Subject = 'Verifikasi Email Anda';
            $mail->Body = "<h3>Hi, $username!</h3>
                <p>Klik link berikut untuk verifikasi akun kamu:</p>
                <a href='https://nasss.azurewebsites.net/verify.php?email=$email&token=$token'>Verifikasi Email</a>";

            $mail->send();
            header('Location: verify_info.php');
        } catch (Exception $e) {
            echo "❌ Gagal kirim email: {$mail->ErrorInfo}";
        }
    } else {
        echo "❌ Error registrasi: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
} else {
    header('Location: index.php');
    exit;
}
?>
