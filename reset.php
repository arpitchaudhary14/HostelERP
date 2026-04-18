<?php
date_default_timezone_set("Asia/Kolkata");
include("db.php");
require_once "otp_manager.php";
$email_prefill = $_GET['email'] ?? '';
$timer_start = isset($_GET['timer']);
if(isset($_POST['resend_otp'])) {
    validate_csrf();
    $email = trim($_POST['email']);
    $recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? "";
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $verify = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"));
    if(!$verify->success) {
        $error = "Please verify that you are not a robot to resend.";
    } else {
        $otpManager = new OTPManager($conn);
    $response = $otpManager->requestOTP($email, 'forgot_password');
    if ($response['status'] == 'success') {
        $success = "OTP resent successfully!";
        $timer_start = true; 
    } else {
        $error = $response['message'];
    }
}
}
if(isset($_POST['reset'])){
    validate_csrf();
    $email = trim($_POST['email']);
    $otp = trim($_POST['otp']);
    $password = $_POST['password'];
    if (!preg_match('/^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/', $password)) {
        $error = "Password must be strong.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM otp_codes WHERE email=? AND otp=? AND expiry_time > NOW()");
        mysqli_stmt_bind_param($stmt, "ss", $email, $otp);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($check)==0){
            $error = "Invalid or expired OTP.";
        } else {
            $hashed = password_hash($password,PASSWORD_DEFAULT);
            $stmt_update = mysqli_prepare($conn, "UPDATE users SET password=? WHERE email=?");
            mysqli_stmt_bind_param($stmt_update, "ss", $hashed, $email);
            mysqli_stmt_execute($stmt_update);
            $stmt_del = mysqli_prepare($conn, "DELETE FROM otp_codes WHERE email=?");
            mysqli_stmt_bind_param($stmt_del, "s", $email);
            mysqli_stmt_execute($stmt_del);
            $success = "Password reset successful.";
            header("refresh:2;url=login.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Reset Password - HostelERP">
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <title>Reset Password - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-bg">
<div class="auth-container" style="max-width:420px;">
    <div class="glass-card card-enter">
        <img src="/WebTechProject/assets/images/logo.png" class="auth-logo" alt="HostelERP Logo">
        <h4 class="text-center mb-1" style="font-weight:700;">Reset Password</h4>
        <p class="text-center mb-3" style="font-size:0.88rem;">Enter your OTP and new password</p>
        <?php if(isset($success)) echo "<div class='alert-glass-success mb-3'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert-glass-danger mb-3'>$error</div>"; ?>
        <form method="POST" id="resetForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="field-group">
                <label for="resetEmail">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="resetEmail"
                           value="<?php echo htmlspecialchars($email_prefill); ?>"
                           class="form-input-modern" placeholder="Enter your email"
                           required autocomplete="email">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="resetOtp">OTP Code</label>
                <div class="input-wrapper">
                    <input type="text" name="otp" id="resetOtp"
                           class="form-input-modern" placeholder="Enter 6-digit OTP"
                           required pattern="[0-9]{6}">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="resetPassword">New Password</label>
                <div class="input-wrapper password-wrapper">
                    <input type="password" name="password" id="resetPassword"
                           class="form-input-modern" placeholder="Create a strong password"
                           required autocomplete="new-password">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                    <button type="button" class="eye-toggle" id="resetEyeToggle" aria-label="Toggle password visibility"></button>
                </div>
                <div class="strength-meter">
                    <div class="strength-meter-fill" id="resetStrengthFill"></div>
                </div>
                <div class="strength-text" id="resetStrengthText"></div>
            </div>
            <button type="submit" name="reset" class="btn-gradient" id="resetBtn" disabled>
                Reset Password
            </button>
        </form>
        <div class="text-center mt-3" style="font-size:0.85rem;" id="resendContainer">
            <p class="mb-2">Didn't receive code?</p>
            <form method="POST" id="resendForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="email" id="resendEmail" value="<?php echo htmlspecialchars($email_prefill); ?>">
                <div class="d-flex justify-content-center mb-2">
                    <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['RECAPTCHA_SITE_KEY'] ?? ''; ?>"></div>
                </div>
                <button type="submit" name="resend_otp" id="resendBtn" class="btn btn-outline-secondary btn-sm" disabled>
                    Resend OTP <span id="timerDisplay"></span>
                </button>
            </form>
        </div>
        <div class="auth-divider" style="margin:12px 0;"></div>
        <div class="text-center" style="font-size:0.9rem;">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</div>
<script src="/WebTechProject/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form    = document.getElementById('resetForm');
    const btn     = document.getElementById('resetBtn');
    const emailIn = document.getElementById('resetEmail');
    const otpIn   = document.getElementById('resetOtp');
    const passIn  = document.getElementById('resetPassword');
    const eyeBtn  = document.getElementById('resetEyeToggle');
    Validator.attachLiveValidation(emailIn, { required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ });
    Validator.attachLiveValidation(otpIn,   { required: true, pattern: /^[0-9]{6}$/ });
    Validator.attachLiveValidation(passIn,  {
        required: true,
        pattern: /^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/
    });
    const strengthFill = document.getElementById('resetStrengthFill');
    const strengthText = document.getElementById('resetStrengthText');
    Validator.attachPasswordStrength(passIn, strengthFill, strengthText);
    createEyeToggle(passIn, eyeBtn);
    form.addEventListener('input', () => {
        btn.disabled = !form.checkValidity();
    });
    const resendBtn = document.getElementById('resendBtn');
    const timerDisplay = document.getElementById('timerDisplay');
    const emailWrap = document.getElementById('resendEmail').value;
    let timerKey = "otp_timer_" + emailWrap;
    let storedTime = sessionStorage.getItem(timerKey);
    let duration = 60;
    <?php if($timer_start): ?>
    storedTime = Date.now() + (duration * 1000);
    sessionStorage.setItem(timerKey, storedTime);
    <?php endif; ?>
    function updateTimer() {
        if(!storedTime || !emailWrap) {
            resendBtn.disabled = false;
            return;
        }
        let remaining = Math.max(0, Math.floor((storedTime - Date.now()) / 1000));
        if(remaining > 0) {
            resendBtn.disabled = true;
            let s = remaining.toString().padStart(2, '0');
            timerDisplay.textContent = `(${s}s)`;
            setTimeout(updateTimer, 1000);
        } else {
            resendBtn.disabled = false;
            timerDisplay.textContent = "";
            sessionStorage.removeItem(timerKey);
        }
    }
    updateTimer();
});
</script>
<?php include("footer.php"); ?>
</body>
</html>