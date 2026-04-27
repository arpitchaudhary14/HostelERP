<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db.php";
require_once "otp_manager.php";
if (!isset($_SESSION['pending_2fa_user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['pending_2fa_user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$user) {
    header("Location: login.php");
    exit();
}
$otpManager = new OTPManager($conn);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['sent'])) {
    $res = $otpManager->requestOTP($user['email'], '2fa');
    if ($res['status'] == 'success') {
        header("Location: 2fa_verification.php?sent=1");
        exit();
    } else {
        $error = "Failed to send 2FA code: " . $res['message'];
    }
}
if (isset($_GET['sent'])) {
    $success = "A 6-digit True 2-Factor Authentication code has been sent to your email.";
}
if (isset($_POST['verify_2fa'])) {
    validate_csrf();
    $otp = trim($_POST['otp']);
    $stmt2 = mysqli_prepare($conn, "SELECT * FROM otp_codes WHERE email=? AND otp=? AND type='2fa' AND expiry_time > NOW()");
    mysqli_stmt_bind_param($stmt2, "ss", $user['email'], $otp);
    mysqli_stmt_execute($stmt2);
    $check = mysqli_stmt_get_result($stmt2);
    if (mysqli_num_rows($check) == 0) {
        $error = "Invalid or expired 2FA code.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = strtolower($user['role']);
        unset($_SESSION['pending_2fa_user_id']);
        mysqli_query($conn, "DELETE FROM otp_codes WHERE email='{$user['email']}' AND type='2fa'");
        if ($_SESSION['role'] == 'student') {
            header("Location: student/dashboard.php");
        } elseif ($_SESSION['role'] == 'warden') {
            header("Location: warden/dashboard.php");
        } elseif ($_SESSION['role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: login.php");
        }
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2-Factor Authentication - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
</head>
<body class="auth-bg">
<div class="auth-container" style="max-width:420px;">
    <div class="glass-card card-enter">
        <div class="text-center mb-3">
            <div style="font-size:3rem; margin-bottom:10px;">🔐</div>
            <h4 style="font-weight:700;">Two-Step Verification</h4>
            <p style="font-size:0.88rem; color:#666;">Enter the code sent to <strong><?php echo htmlspecialchars($user['email']); ?></strong></p>
        </div>
        <?php if(isset($success)) echo "<div class='alert-glass-success mb-3'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert-glass-danger mb-3'>$error</div>"; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="field-group">
                <label>Authentication Code</label>
                <div class="input-wrapper">
                    <input type="text" name="otp" id="otpIn" class="form-input-modern text-center" style="font-size:1.2rem; letter-spacing:4px;" placeholder="• • • • • •" required pattern="[0-9]{6}">
                </div>
                <div class="otp-expiry-info mt-3" style="font-size: 0.95rem; color: #495057; display: flex; justify-content: center; align-items: center; font-weight: 600; gap: 8px;">
                    <span>⏱️ Code expires in: <strong id="otpExpiryTimer" class="text-primary" style="font-size: 1.1rem;">02:00</strong></span>
                </div>
            </div>
            <button type="submit" name="verify_2fa" id="verifyBtn" class="btn-gradient w-100 mt-3" style="font-weight:600;">
                Verify & Continue
            </button>
        </form>  
        <div class="text-center mt-3">
             <a href="login.php" class="text-muted" style="text-decoration:none; font-size:0.85rem;">← Back to login</a>
        </div>
    </div>
</div>
<script src="/WebTechProject/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const otpIn = document.getElementById('otpIn');
    const verifyBtn = document.getElementById('verifyBtn');
    const otpExpiryTimer = document.getElementById('otpExpiryTimer');
    const email = "<?php echo $user['email']; ?>";
    const timerKey = "otp_expiry_2fa_" + email;
    let storedExpiry = sessionStorage.getItem(timerKey);
    let duration = 120; 
    <?php if(isset($_GET['sent'])): ?>
    if(!storedExpiry) {
        storedExpiry = Date.now() + (duration * 1000);
        sessionStorage.setItem(timerKey, storedExpiry);
    }
    <?php endif; ?>
    function updateExpiryTimer() {
        if(!storedExpiry || !otpExpiryTimer) return;
        let remaining = Math.max(0, Math.floor((storedExpiry - Date.now()) / 1000));
        if(remaining > 0) {
            let m = Math.floor(remaining / 60).toString().padStart(2, '0');
            let s = (remaining % 60).toString().padStart(2, '0');
            otpExpiryTimer.textContent = `${m}:${s}`;
            if(remaining <= 30) {
                otpExpiryTimer.style.color = '#ff5252';
                otpExpiryTimer.classList.remove('text-primary');
            }
            setTimeout(updateExpiryTimer, 1000);
        } else {
            otpExpiryTimer.textContent = "EXPIRED";
            otpExpiryTimer.style.color = '#ff5252';
            otpIn.disabled = true;
            verifyBtn.disabled = true;
            otpIn.placeholder = "Expired";
            sessionStorage.removeItem(timerKey);
        }
    }
    updateExpiryTimer();
});
</script>
</body>
</html>