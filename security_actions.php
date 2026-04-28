<?php
require_once "db.php";
require_once "security_config.php";
$action = $_GET['action'] ?? '';
$email  = $_GET['email'] ?? '';
$token  = $_GET['token'] ?? '';
$status = '';
$message = '';
$details = '';
if (empty($action) || empty($email) || empty($token)) {
    die("Invalid request parameters.");
}
$stmt = mysqli_prepare($conn, "SELECT otp, ip_address FROM otp_codes WHERE email=? ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);
if (!$data) {
    $status = 'error';
    $message = "Request Expired or Invalid";
    $details = "This verification request is no longer active in our system.";
} else {
    $expectedToken = hash_hmac('sha256', $email . $data['otp'], $_ENV['DB_PASS'] ?? 'secret');
    if (!hash_equals($expectedToken, $token)) {
        $status = 'error';
        $message = "Security Token Mismatch";
        $details = "The security token provided does not match our records. Please use the link from your latest email.";
    } else {
        $req_ip = $data['ip_address'];
        if ($action === 'cancel') {
            mysqli_query($conn, "DELETE FROM otp_codes WHERE email='" . mysqli_real_escape_string($conn, $email) . "'");
            $status = 'success';
            $message = "OTP Cancelled Successfully";
            $details = "The verification code has been invalidated. No one can use it to access your account now.";
        } 
        elseif ($action === 'block') {
            $blocked_until = date("Y-m-d H:i:s", strtotime("+24 hours"));
            $stmt_block = mysqli_prepare($conn, "INSERT INTO otp_rate_limits (identifier, type, last_attempt_time, attempts, blocked_until) 
                                                VALUES (?, 'ip', NOW(), 5, ?) 
                                                ON DUPLICATE KEY UPDATE blocked_until=?, attempts=5, last_attempt_time=NOW()");
            mysqli_stmt_bind_param($stmt_block, "sss", $req_ip, $blocked_until, $blocked_until);
            mysqli_stmt_execute($stmt_block);
            mysqli_query($conn, "DELETE FROM otp_codes WHERE email='" . mysqli_real_escape_string($conn, $email) . "'");
            $status = 'warning';
            $message = "Requester IP Blocked";
            $details = "The requester's IP ($req_ip) has been blocked from requesting OTPs for the next 24 hours. Your account security has been prioritized.";
        }
        else {
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <title>Security Action Center - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
    <style>
        .action-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 100px auto;
            animation: fadeIn 0.6s ease-out;
        }
        .status-icon {
            font-size: 60px;
            margin-bottom: 20px;
            display: block;
        }
        .btn-action {
            margin-top: 30px;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .status-details { color: rgba(255,255,255,0.7); font-size: 1rem; margin-top: 1rem; }
    </style>
</head>
<body class="auth-bg">
    <div class="container">
        <div class="action-card glass-card">
            <?php if ($status === 'success'): ?>
                <span class="status-icon">🛡️</span>
                <h3 class="text-success"><?php echo $message; ?></h3>
                <p class="status-details"><?php echo $details; ?></p>
            <?php elseif ($status === 'warning'): ?>
                <span class="status-icon">🚫</span>
                <h3 class="text-warning"><?php echo $message; ?></h3>
                <p class="status-details"><?php echo $details; ?></p>
            <?php else: ?>
                <span class="status-icon">❌</span>
                <h3 class="text-danger"><?php echo $message; ?></h3>
                <p class="status-details"><?php echo $details; ?></p>
            <?php endif; ?>
            <div class="mt-4">
                <a href="login.php" class="btn btn-outline-light btn-action">Go to Login</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn btn-primary btn-action ms-2">Manage Security</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>