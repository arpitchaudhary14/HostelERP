<?php
include("session_check.php");
include("db.php");
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
if(isset($_POST['update_profile'])){
    validate_csrf();
    $phone   = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $upd = mysqli_prepare($conn, "UPDATE users SET phone=?, address=? WHERE id=?");
    mysqli_stmt_bind_param($upd, "ssi", $phone, $address, $user_id);
    mysqli_stmt_execute($upd);
    $success = "Profile updated successfully.";
    $stmt2 = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt2, "i", $user_id);
    mysqli_stmt_execute($stmt2);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
}
if(isset($_POST['upload_pic'])){
    validate_csrf();
    if(isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error']==0){
        $allowed = ['jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if(in_array($ext,$allowed)){
            $dir = "assets/profile/";
            if(!is_dir($dir)) mkdir($dir, 0755, true);
            $new_name = "user_".$user_id.".".$ext;
            $target = $dir.$new_name;
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target);
            $upd = mysqli_prepare($conn, "UPDATE users SET profile_pic=? WHERE id=?");
            mysqli_stmt_bind_param($upd, "si", $new_name, $user_id);
            mysqli_stmt_execute($upd);
            $success = "Profile picture updated successfully.";
            $stmt2 = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
            mysqli_stmt_bind_param($stmt2, "i", $user_id);
            mysqli_stmt_execute($stmt2);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
        } else {
            $error = "Only JPG, JPEG, PNG allowed.";
        }
    }
}
if(isset($_POST['change_password'])){
    validate_csrf();
    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    if(!password_verify($current, $user['password'])){
        $error = "Current password is incorrect.";
    } elseif (!preg_match('/^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/', $new)) {
        $error = "New password must be at least 10 characters with uppercase, lowercase, number, and 2 special characters.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $upd = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($upd, "si", $hashed, $user_id);
        mysqli_stmt_execute($upd);
        $success = "Password changed successfully.";
    }
}
if(isset($_POST['request_delete_otp'])){
    validate_csrf();
    require_once "otp_manager.php";
    $otpManager = new OTPManager($conn);
    $response = $otpManager->requestOTP($user['email'], 'delete_account');
    if ($response['status'] == 'success') {
        $success = "OTP sent to your email to verify account deletion.";
        $show_delete_verify = true;
    } else {
        $error = $response['message'];
    }
}
if(isset($_POST['confirm_delete_account'])){
    validate_csrf();
    $otp = trim($_POST['delete_otp']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM otp_codes WHERE email=? AND otp=? AND type='delete_account' AND expiry_time > NOW()");
    mysqli_stmt_bind_param($stmt, "ss", $user['email'], $otp);
    mysqli_stmt_execute($stmt);
    $check = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($check)==0){
        $error = "Invalid or expired OTP for account deletion.";
        $show_delete_verify = true;
    } else {
        $del = mysqli_prepare($conn, "DELETE FROM users WHERE id=?");
        mysqli_stmt_bind_param($del, "i", $user_id);
        mysqli_stmt_execute($del);
        session_destroy();
        header("Location: login.php?msg=" . urlencode("Account successfully deleted."));
        exit();
    }
}
if(isset($_POST['request_email_verification'])){
    validate_csrf();
    require_once "otp_manager.php";
    $otpManager = new OTPManager($conn);
    $response = $otpManager->requestOTP($user['email'], 'email_verification');
    if ($response['status'] == 'success') {
        $success = "Verification OTP sent to your email.";
        $show_email_verify = true;
    } else {
        $error = $response['message'];
    }
}
if(isset($_POST['confirm_email_verification'])){
    validate_csrf();
    $otp = trim($_POST['email_otp']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM otp_codes WHERE email=? AND otp=? AND type='email_verification' AND expiry_time > NOW()");
    mysqli_stmt_bind_param($stmt, "ss", $user['email'], $otp);
    mysqli_stmt_execute($stmt);
    $check = mysqli_stmt_get_result($stmt);
    if(mysqli_num_rows($check)==0){
        $error = "Invalid or expired OTP.";
        $show_email_verify = true;
    } else {
        $upd_v = mysqli_prepare($conn, "UPDATE users SET is_verified=1 WHERE id=?");
        mysqli_stmt_bind_param($upd_v, "i", $user_id);
        mysqli_stmt_execute($upd_v);
        $del_o = mysqli_prepare($conn, "DELETE FROM otp_codes WHERE email=? AND type='email_verification'");
        mysqli_stmt_bind_param($del_o, "s", $user['email']);
        mysqli_stmt_execute($del_o);
        $success = "Email verified successfully!";
        $stmt2 = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "i", $user_id);
        mysqli_stmt_execute($stmt2);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    }
}
if(isset($_POST['toggle_2fa'])){
    validate_csrf();
    if($user['is_verified'] == 0) {
        $error = "You must verify your email before enabling True 2-Factor Authentication.";
    } else {
        $new_status = $user['two_factor_enabled'] ? 0 : 1;
        $upd_2f = mysqli_prepare($conn, "UPDATE users SET two_factor_enabled=? WHERE id=?");
        mysqli_stmt_bind_param($upd_2f, "ii", $new_status, $user_id);
        mysqli_stmt_execute($upd_2f);
        
        $success = $new_status ? "Two-Factor Authentication Enabled." : "Two-Factor Authentication Disabled.";
        $stmt2 = mysqli_prepare($conn, "SELECT * FROM users WHERE id=?");
        mysqli_stmt_bind_param($stmt2, "i", $user_id);
        mysqli_stmt_execute($stmt2);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    }
}
$full_name = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
if (empty(trim($full_name))) $full_name = $user['full_name'] ?? 'User';
include("header.php");
?>
<div class="container mt-4 page-fade-in" style="max-width:700px;">
<div class="section-header-elite">
    <h3>Profile Settings</h3>
    <p>Manage your account security and personal details</p>
</div>
<?php if(isset($success)) echo "<div class='alert alert-success' style='border-radius:var(--radius-sm);'>$success</div>"; ?>
<?php if(isset($error)) echo "<div class='alert alert-danger' style='border-radius:var(--radius-sm);'>$error</div>"; ?>
<div class="glass-card-light mb-4 text-center reveal">
    <img src="/WebTechProject/assets/profile/<?php echo htmlspecialchars($user['profile_pic'] ?? 'default.png'); ?>"
         width="120" height="120"
         style="border-radius:50%; object-fit:cover; border:4px solid rgba(108,99,255,0.3); box-shadow: 0 4px 20px rgba(108,99,255,0.15);"
         alt="Profile Picture"
         onerror="this.src='/WebTechProject/assets/images/default_avatar.png'">
    <form method="POST" enctype="multipart/form-data" class="mt-3">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="file" name="profile_pic" accept=".jpg,.jpeg,.png" class="form-input-light mb-2" required style="padding:8px;">
        <button name="upload_pic" class="btn-gradient" style="max-width:200px; margin:0 auto;">
            Upload Picture
        </button>
    </form>
</div>
<div class="glass-card-light mb-4 reveal">
<h5 style="font-weight:600; color:#1a1a2e; margin-bottom:var(--space-md);">Profile Information</h5>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Full Name</label>
<input type="text" class="form-input-light"
value="<?php echo htmlspecialchars($full_name); ?>" readonly style="opacity:0.7;">
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Email</label>
<input type="email" class="form-input-light"
value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly style="opacity:0.7;">
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Role</label>
<input type="text" class="form-input-light"
value="<?php echo ucfirst($user['role'] ?? ''); ?>" readonly style="opacity:0.7;">
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Phone</label>
<input type="text" name="phone" class="form-input-light"
value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Address</label>
<textarea name="address" class="form-input-light" style="min-height:80px; resize:vertical;"><?php
echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
</div>
<button class="btn-gradient" name="update_profile">
Update Profile
</button>
</form>
</div>
<div class="glass-card-light reveal">
<h5 style="font-weight:600; color:#1a1a2e; margin-bottom:var(--space-md);">Change Password</h5>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Current Password</label>
<input type="password" name="current_password" class="form-input-light" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">New Password</label>
<div class="input-wrapper password-wrapper" style="position:relative;">
    <input type="password" name="new_password" id="profileNewPass" class="form-input-light" required
           placeholder="Min 10 chars, upper, lower, number, 2 special chars">
    <button type="button" class="eye-toggle" id="profileEyeToggle" aria-label="Toggle password visibility"></button>
</div>
<div class="strength-meter mt-1"><div class="strength-meter-fill" id="profileStrengthFill"></div></div>
<div class="strength-text" id="profileStrengthText" style="font-size:0.8rem; margin-top:4px;"></div>
</div>
<button class="btn-gradient" name="change_password" style="background:linear-gradient(135deg, #ff9800, #ff5722);">
Change Password
</button>
</form>
</div>
<div class="glass-card-light reveal mt-4 mb-4">
    <h5 style="font-weight:600; color:#1a1a2e; margin-bottom:var(--space-md);">Security Hub</h5>
    <div class="d-flex align-items-center justify-content-between p-3 mb-3 border rounded" style="background: rgba(0,0,0,0.02)">
        <div>
            <h6 class="mb-1" style="font-weight:600;">Email Verification</h6>
            <small class="text-muted">Required for high-security actions</small>
        </div>
        <div>
            <?php if($user['is_verified']): ?>
                <span class="status-pill verified">Verified</span>
            <?php else: ?>
                <?php if(isset($show_email_verify) && $show_email_verify): ?>
                    <form method="POST" class="d-flex flex-column align-items-end">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="d-flex mb-1">
                            <input type="text" name="email_otp" id="emailVerifyOtpIn" class="form-input-light form-control-sm me-2" style="width:100px;" placeholder="OTP Code" required pattern="[0-9]{6}">
                            <button class="btn btn-sm btn-success" name="confirm_email_verification" id="emailVerifyConfirmBtn">Verify</button>
                        </div>
                        <div id="emailVerifyExpiryTimerWrapper" class="timer-pill" style="transform: scale(0.8); margin-top:-5px;">
                            ⏱️ Expires: <strong id="emailVerifyExpiryTimer">02:00</strong>
                        </div>
                    </form>
                <?php else: ?>
                    <span class="status-pill unverified">Unverified</span>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button class="btn btn-sm btn-outline-primary w-100" name="request_email_verification">Verify Email</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex align-items-center justify-content-between p-3 border rounded" style="background: rgba(0,0,0,0.02)">
        <div>
            <h6 class="mb-1" style="font-weight:600;">Two-Factor Authentication (2FA)</h6>
            <small class="text-muted">Protects your login with an active email OTP</small>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" name="toggle_2fa" class="status-pill <?= $user['two_factor_enabled'] ? 'verified' : 'unverified' ?>" style="cursor:pointer; border:none; outline:none;">
                <?= $user['two_factor_enabled'] ? 'Enabled' : 'Disabled' ?>
            </button>
        </form>
    </div>
</div>
<div class="glass-card-light reveal mt-4" style="border: 1px solid rgba(255, 0, 0, 0.2);">
<h5 style="font-weight:600; color:#d32f2f; margin-bottom:var(--space-md);">Danger Zone</h5>
<p style="font-size:0.88rem; color:#666;">Once you delete your account, there is no going back. Please be certain.</p>
<?php if(isset($show_delete_verify) && $show_delete_verify): ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <div class="mb-3">
            <label style="font-weight:500; color:#d32f2f; font-size:0.88rem;">Enter OTP sent to <?php echo htmlspecialchars($user['email']); ?></label>
            <input type="text" name="delete_otp" id="deleteOtpIn" class="form-input-light" placeholder="6-digit code" required pattern="[0-9]{6}">
            <div class="mt-3 d-flex justify-content-center">
                <div id="deleteExpiryTimerWrapper" class="timer-pill warning">
                    ⏱️ Code expires in: <strong id="deleteExpiryTimer">02:00</strong>
                </div>
            </div>
        </div>
        <button class="btn btn-danger w-100" name="confirm_delete_account" id="deleteConfirmBtn" style="font-weight:600; padding:10px;">
            Confirm Permanent Deletion
        </button>
    </form>
    <form method="POST" class="mt-2">
         <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
         <button class="btn btn-link w-100 text-muted" style="text-decoration:none;">Cancel</button>
    </form>
<?php else: ?>
    <form method="POST" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This will send an OTP to your email.');">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <button class="btn btn-outline-danger w-100" name="request_delete_otp" style="font-weight:600; padding:10px;">
            Delete Account
        </button>
    </form>
<?php endif; ?>
</div>
</div>
<script src="/WebTechProject/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const passIn     = document.getElementById('profileNewPass');
    const eyeBtn     = document.getElementById('profileEyeToggle');
    const fillEl     = document.getElementById('profileStrengthFill');
    const textEl     = document.getElementById('profileStrengthText');
    if (passIn && eyeBtn) createEyeToggle(passIn, eyeBtn);
    if (passIn && fillEl) Validator.attachPasswordStrength(passIn, fillEl, textEl);
    function startExpiryTimer(displayId, inputId, btnId, type, initialDuration = 120) {
        const display = document.getElementById(displayId);
        const input = document.getElementById(inputId);
        const btn = document.getElementById(btnId);
        if(!display) return;
        const email = "<?php echo $user['email']; ?>";
        const key = "otp_expiry_" + type + "_" + email;
        let stored = sessionStorage.getItem(key);
        if(!stored) {
            stored = Date.now() + (initialDuration * 1000);
            sessionStorage.setItem(key, stored);
        }
        function tick() {
            let remaining = Math.max(0, Math.floor((stored - Date.now()) / 1000));
            const wrapper = document.getElementById(displayId + 'Wrapper');
            if(remaining > 0) {
                let m = Math.floor(remaining / 60).toString().padStart(2, '0');
                let s = (remaining % 60).toString().padStart(2, '0');
                display.textContent = `${m}:${s}`;
                if(remaining <= 30 && wrapper) wrapper.classList.add('warning');
                setTimeout(tick, 1000);
            } else {
                display.textContent = "EXPIRED";
                if(wrapper) wrapper.classList.add('warning');
                if(input) input.disabled = true;
                if(btn) btn.disabled = true;
                sessionStorage.removeItem(key);
            }
        }
        tick();
    }
    <?php if(isset($show_delete_verify) && $show_delete_verify): ?>
    startExpiryTimer('deleteExpiryTimer', 'deleteOtpIn', 'deleteConfirmBtn', 'delete');
    <?php endif; ?>
    <?php if(isset($show_email_verify) && $show_email_verify): ?>
    startExpiryTimer('emailVerifyExpiryTimer', 'emailVerifyOtpIn', 'emailVerifyConfirmBtn', 'email');
    <?php endif; ?>
});
</script>
<?php include("footer.php"); ?>