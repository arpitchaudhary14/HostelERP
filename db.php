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
if (!$conn) { die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($conn, "SET time_zone = '+05:30'");
mysqli_set_charset($conn, "utf8mb4");

// Auto-heal missing decommission tables if not created yet
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS room_decommission_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    warden_id INT NOT NULL,
    reason TEXT NOT NULL,
    affected_students TEXT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_id INT NULL,
    admin_remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL
)");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS room_decommission_disputes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    claim_type ENUM('fee_refund', 'equivalent_room', 'both') DEFAULT 'fee_refund',
    details TEXT NOT NULL,
    status ENUM('pending', 'resolved', 'rejected') DEFAULT 'pending',
    resolution_type ENUM('refund_processed', 'room_allotted', 'rejected') NULL,
    admin_remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL
)");