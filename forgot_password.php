<?php
date_default_timezone_set("Asia/Kolkata");
include("db.php");
require_once "otp_manager.php";
if(isset($_POST['send_otp'])){
    validate_csrf();
}
    $email = trim($_POST['email']);
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format.";
    } else {
        $recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? "";
        $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
        $verify = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"));
        if(!$verify->success) {
            $error = "Please verify that you are not a robot.";
        } else {
            $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check = mysqli_stmt_get_result($stmt);
        if(mysqli_num_rows($check) == 0){
            $error = "Email not registered.";
        } else {
            $otpManager = new OTPManager($conn);
            $response   = $otpManager->requestOTP($email, 'forgot_password');
            if ($response['status'] === 'success') {
                $success = "OTP sent to your email. Redirecting...";
                header("refresh:2;url=reset.php?email=$email&timer=1");
            } else {
                $error = $response['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Forgot Password - HostelERP">
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <title>Forgot Password - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-bg">
<div class="auth-container" style="max-width:420px;">
    <div class="glass-card card-enter">
        <img src="/WebTechProject/assets/images/logo.png" class="auth-logo" alt="HostelERP Logo">
        <h4 class="text-center mb-1" style="font-weight:700;">Forgot Password</h4>
        <p class="text-center mb-3" style="font-size:0.88rem;">Enter your email to receive a 6-digit OTP</p>
        <?php if(isset($success)) echo "<div class='alert-glass-success mb-3'>$success</div>"; ?>
        <?php if(isset($error))   echo "<div class='alert-glass-danger mb-3'>$error</div>"; ?>
        <form method="POST" id="forgotForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="field-group">
                <label for="forgotEmail">Email Address</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="forgotEmail"
                           class="form-input-modern" placeholder="Enter your registered email"
                           required autocomplete="email">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="g-recaptcha mb-3" data-sitekey="<?php echo $_ENV['RECAPTCHA_SITE_KEY'] ?? ''; ?>"></div>
            <button type="submit" name="send_otp" class="btn-gradient mt-4" id="sendOtpBtn" disabled>
                Send OTP to Email
            </button>
        </form>
        <div class="text-center mt-field" style="font-size:0.9rem;">
            <a href="reset.php">Already have OTP? Reset Password</a>
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
    const form    = document.getElementById('forgotForm');
    const btn     = document.getElementById('sendOtpBtn');
    const emailIn = document.getElementById('forgotEmail');
    Validator.attachLiveValidation(emailIn, {
        required: true,
        pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    });
    form.addEventListener('input', () => {
        btn.disabled = !form.checkValidity();
    });
    form.addEventListener('submit', () => {
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Sending OTP...';
        btn.disabled = true;
    });
});
</script>
<?php include("auth_footer.php"); ?>
</body>
</html>