<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/security_config.php';
$host = $_ENV['DB_HOST'] ?? "localhost";
$user = $_ENV['DB_USER'] ?? "root";
$pass = $_ENV['DB_PASS'] ?? "";
$dbname = $_ENV['DB_NAME'] ?? "hostelerp_db";
$port = $_ENV['DB_PORT'] ?? 3306;
$conn = mysqli_connect($host, $user, $pass, $dbname, $port);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($conn, "SET time_zone = '+05:30'");
mysqli_set_charset($conn, "utf8mb4");