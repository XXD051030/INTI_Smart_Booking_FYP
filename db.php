<?php
// Set timezone for Malaysia (UTC+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

$host = 'localhost';
$dbname = 'reservation_system';
$username = 'webapp';  // Dedicated web application user
$password = 'webapp123';  // Password for webapp user

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set MySQL timezone to match system timezone
    $pdo->exec("SET time_zone = '+08:00'");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?> 