<?php
$host = 'sql12.freesqldatabase.com';
$db   = 'sql12771446';
$user = 'sql12771446';
$pass = 'HUep3PG6mT';
$port = 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
