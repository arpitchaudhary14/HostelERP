<?php
date_default_timezone_set('Asia/Kolkata');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header_remove("Content-Security-Policy");
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com 'unsafe-inline'; frame-src 'self' https://www.google.com https://recaptcha.google.com; style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline'; font-src 'self' https://fonts.gstatic.com; connect-src 'self' https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com http://127.0.0.1:5000 http://localhost:5000; img-src 'self' data: https:;");
header_remove('X-Powered-By');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
function validate_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            die("CSRF Token Validation Failed. Please refresh and try again.");
        }
    }
}
function record_login($conn, $user_id, $type = 'normal') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $ua = $_SERVER['HTTP_USER_AGENT'];
    $stmt = mysqli_prepare($conn, "INSERT INTO login_history (user_id, ip_address, user_agent, login_type) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $ip, $ua, $type);
    mysqli_stmt_execute($stmt);
}