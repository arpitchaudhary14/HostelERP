<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db.php";
require_once "google_oauth_config.php";
require_once "microsoft_oauth_config.php";
if (isset($_GET['google_login'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'access_type'   => 'online',
        'state'         => $state,
    ]);
    header("Location: " . GOOGLE_AUTH_URL . "?$params");
    exit();
}
if (isset($_GET['ms_login'])) {
    $state = bin2hex(random_bytes(16));
    $_SESSION['ms_oauth_state'] = $state;
    $params = http_build_query([
        'client_id'     => MS_CLIENT_ID,
        'redirect_uri'  => MS_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile User.Read',
        'response_mode' => 'query',
        'state'         => $state,
    ]);
    header("Location: " . MS_AUTH_URL . "?$params");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    if ($user && password_verify($password, $user['password'])) {
        if (isset($user['status']) && $user['status'] === 'banned') {
            $error = "Your account has been suspended. Please contact the administrator.";
        } else {
            if (isset($user['two_factor_enabled']) && $user['two_factor_enabled'] == 1) {
                $_SESSION['pending_2fa_user_id'] = $user['id'];
                header("Location: 2fa_verification.php");
                exit();
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = strtolower($user['role']);
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
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to HostelERP - Smart Hostel Management System">
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <title>Login - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
</head>
<body class="auth-bg">
<div class="auth-container">
    <div class="glass-card card-enter" id="oauthCard">
        <img src="/WebTechProject/assets/images/logo.png" class="auth-logo" alt="HostelERP Logo">
        <h4 class="text-center mb-1" style="font-weight:700;">Welcome Back</h4>
        <p class="text-center mb-3" style="font-size:0.88rem;">Sign in to continue to HostelERP</p>
        <?php if(isset($error)) echo "<div class='alert-glass-danger mb-3'>$error</div>"; ?>
        <?php if(isset($_GET['msg'])) echo "<div class='alert-glass-success mb-3'>".htmlspecialchars($_GET['msg'])."</div>"; ?>
        <a href="login.php?google_login=1" class="btn-oauth mb-2" id="googleLoginBtn">
            <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            Sign in with Google
        </a>
        <a href="login.php?ms_login=1" class="btn-oauth" id="microsoftLoginBtn">
            <svg width="20" height="20" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg">
                <rect x="1" y="1" width="9" height="9" fill="#f25022"/>
                <rect x="11" y="1" width="9" height="9" fill="#7fba00"/>
                <rect x="1" y="11" width="9" height="9" fill="#00a4ef"/>
                <rect x="11" y="11" width="9" height="9" fill="#ffb900"/>
            </svg>
            Sign in with Microsoft
        </a>
    </div>
    <div class="glass-card card-enter" id="credentialsCard">
        <div class="auth-divider">or sign in with username</div>
        <form method="POST" id="loginForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="field-group">
                <label for="loginUsername">Username</label>
                <div class="input-wrapper">
                    <input
                        type="text"
                        name="username"
                        id="loginUsername"
                        class="form-input-modern"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    >
                    <span class="validation-icon icon-valid" aria-hidden="true">✅</span>
                    <span class="validation-icon icon-invalid" aria-hidden="true">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="loginPassword">Password</label>
                <div class="input-wrapper password-wrapper">
                    <input
                        type="password"
                        name="password"
                        id="loginPassword"
                        class="form-input-modern"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <span class="validation-icon icon-valid" aria-hidden="true">✅</span>
                    <span class="validation-icon icon-invalid" aria-hidden="true">❌</span>
                    <button type="button" class="eye-toggle" id="loginEyeToggle" aria-label="Toggle password visibility"></button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe" style="font-size: 0.9rem; color: var(--text-muted-light);">
                        Remember Me
                    </label>
                </div>
            </div>
            <button type="submit" name="login" id="loginBtn" class="btn-gradient" disabled>
                Login
            </button>
        </form>
    </div>
    <div class="glass-card card-enter" id="navCard" style="padding: 20px 32px;">
        <div class="text-center">
            <a href="forgot_password.php" style="font-size:0.9rem;">Forgot Password?</a>
        </div>
        <div class="auth-divider" style="margin:10px 0;"></div>
        <div class="text-center" style="font-size:0.9rem;">
            Don't have an account?
            <a href="register.php" style="font-weight:600;">Register</a>
        </div>
    </div>
</div>
<?php if(isset($_GET['session']) && $_GET['session'] == 'expired'): ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var sessionModal = new bootstrap.Modal(document.getElementById('sessionModal'));
    sessionModal.show();
    window.history.replaceState({}, document.title, "login.php");
});
</script>
<div class="modal fade modal-glass" id="sessionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center" style="max-width: 400px; margin: 0 auto;">
            <div class="modal-body p-4">
                <div style="font-size: 3.5rem; margin-bottom: 10px;">⏱️</div>
                <h4 style="font-weight: 700;">Session Expired</h4>
                <p style="color: var(--text-muted-light); font-size: 0.95rem;">For your security, your session has timed out. Please log in again to continue.</p>
                <button type="button" class="btn-gradient w-100 mt-3" data-bs-dismiss="modal">Log In</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<script src="/WebTechProject/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const loginForm    = document.getElementById('loginForm');
    const loginBtn     = document.getElementById('loginBtn');
    const usernameIn   = document.getElementById('loginUsername');
    const passwordIn   = document.getElementById('loginPassword');
    const eyeToggleBtn = document.getElementById('loginEyeToggle');
    const rememberMeCheck = document.getElementById('rememberMe');
    Validator.attachLiveValidation(usernameIn, { required: true, minLen: 3 });
    Validator.attachLiveValidation(passwordIn, { required: true, minLen: 1 });
    const checkBtn = () => {
        loginBtn.disabled = !(usernameIn.value.trim().length >= 3 && passwordIn.value.length >= 1);
    };
    loginForm.addEventListener('input', checkBtn);
    let savedCreds = JSON.parse(localStorage.getItem('hostelerp_creds') || '{}');
    usernameIn.addEventListener('input', function() {
        if (savedCreds[this.value] && rememberMeCheck.checked) {
            passwordIn.value = savedCreds[this.value];
            checkBtn();
        }
    });
    loginForm.addEventListener('submit', function() {
        if (rememberMeCheck.checked) {
            savedCreds[usernameIn.value] = passwordIn.value;
            localStorage.setItem('hostelerp_last_user', usernameIn.value);
        } else {
            delete savedCreds[usernameIn.value];
        }
        localStorage.setItem('hostelerp_creds', JSON.stringify(savedCreds));
        localStorage.setItem('hostelerp_remember', rememberMeCheck.checked);
    });
    if (localStorage.getItem('hostelerp_remember') === 'true') {
        rememberMeCheck.checked = true;
        let lastUser = localStorage.getItem('hostelerp_last_user');
        if (lastUser && savedCreds[lastUser]) {
            usernameIn.value = lastUser;
            passwordIn.value = savedCreds[lastUser];
            checkBtn();
        }
    }
    const autofillTimer = setInterval(() => {
        if (usernameIn.value.length > 0 && passwordIn.value.length > 0) {
            loginBtn.disabled = false;
            clearInterval(autofillTimer);
        }
    }, 300);
    createEyeToggle(passwordIn, eyeToggleBtn);
});
</script>
<?php include("footer.php"); ?>
</body>
</html>