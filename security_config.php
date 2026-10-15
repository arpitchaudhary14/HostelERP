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
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.google.com https://www.gstatic.com; frame-src 'self' https://www.google.com https://recaptcha.google.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self' https://www.google.com https://www.gstatic.com https://generativelanguage.googleapis.com; img-src 'self' data: https:;");

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Output buffering to prevent half-rendered HTML on fatal errors
if (ob_get_level() === 0) {
    ob_start();
}

// Global Exception Handler - catches all unhandled exceptions
set_exception_handler(function ($exception) {
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . ":" . $exception->getLine());
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code(500);
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/api/') !== false || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => 'Internal Server Error', 'code' => 500]);
    } else {
        $code = 500;
        if (file_exists(__DIR__ . '/error.php')) {
            include __DIR__ . '/error.php';
        }
    }
    exit();
});

// Global Shutdown Function - catches fatal compile/runtime errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
        error_log("Fatal Error: " . $error['message'] . " in " . $error['file'] . ":" . $error['line']);
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(500);
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['error' => 'Internal Server Error', 'code' => 500]);
        } else {
            $code = 500;
            if (file_exists(__DIR__ . '/error.php')) {
                include __DIR__ . '/error.php';
            }
        }
        exit();
    }
});

function validate_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            http_response_code(403);
            die("CSRF Token Validation Failed. Please refresh and try again.");
        }
    }
}

function record_login($conn, $user_id, $type = 'normal') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $stmt = mysqli_prepare($conn, "INSERT INTO login_history (user_id, ip_address, user_agent, login_type) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $ip, $ua, $type);
    mysqli_stmt_execute($stmt);
}

function redirect_thank_you() {
    $base = (strpos($_SERVER['REQUEST_URI'] ?? '', '/WebTechProject') !== false || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/WebTechProject') !== false) ? '/WebTechProject' : '';
    header("Location: {$base}/thank_you.php");
    exit();
}