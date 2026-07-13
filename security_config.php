<?php
date_default_timezone_set('Asia/Kolkata');
if (session_status() === PHP_SESSION_NONE) { session_start();
}
if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header_remove("Content-Security-Policy");
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com; frame-src 'self' https://www.google.com https://recaptcha.google.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self' https://www.google.com https://www.gstatic.com https://generativelanguage.googleapis.com; img-src 'self' data: https:;");
// Note: CSP allows external Google APIs (reCAPTCHA, Gemini) and internal localhost proxying.
// LEON API calls are proxied through /api/leon/chat.php (same-origin)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
function validate_csrf() { if ($_SERVER['REQUEST_METHOD'] === 'POST') { if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) { http_response_code(403); die("CSRF Token Validation Failed. Please refresh and try again."); } }
}
function record_login($conn, $user_id, $type = 'normal') { $ip = $_SERVER['REMOTE_ADDR']; $ua = $_SERVER['HTTP_USER_AGENT']; $stmt = mysqli_prepare($conn, "INSERT INTO login_history (user_id, ip_address, user_agent, login_type) VALUES (?, ?, ?, ?)"); mysqli_stmt_bind_param($stmt, "isss", $user_id, $ip, $ua, $type); mysqli_stmt_execute($stmt);
}