<?php
$host = '20.63.18.172';
$db   = 'UTS';
$user = 'nassapps';
$pass = 'nass1922.';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
