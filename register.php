<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
include("db.php");
if (isset($_POST['register'])) {
    validate_csrf();
    $first_name = trim($_POST['first_name']);
    $last_name  = !empty($_POST['last_name']) ? trim($_POST['last_name']) : NULL;
    $dob        = !empty($_POST['dob']) ? $_POST['dob'] : NULL;
    $email      = trim($_POST['email']);
    $phone      = !empty($_POST['phone']) ? trim($_POST['phone']) : NULL;
    $gender     = !empty($_POST['gender']) ? $_POST['gender'] : NULL;
    $address    = !empty($_POST['address']) ? trim($_POST['address']) : NULL;
    $role       = $_POST['role'];
    $username   = trim($_POST['username']);
    $password   = $_POST['password'];

    $recaptcha_secret = $_ENV['RECAPTCHA_SECRET_KEY'] ?? "";
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $verify = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}"));

    if(empty($recaptcha_response)) {
        $error = "Please check the 'I'm not a robot' checkbox.";
    } elseif(!$verify || !$verify->success) {
        $error = "Please verify that you are not a robot.";
    } elseif (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    }
    elseif (!preg_match('/^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/', $password)) {
        $error = "Password must be strong (10+ chars, upper, lower, number, 2 special characters).";
    }
    else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $full_name = $first_name . " " . ($last_name ?? "");
        $stmt = mysqli_prepare($conn, "INSERT INTO users 
        (full_name, first_name, last_name, dob, email, phone, gender, address, username, password, role) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, "sssssssssss",
            $full_name,
            $first_name,
            $last_name,
            $dob,
            $email,
            $phone,
            $gender,
            $address,
            $username,
            $hashed_password,
            $role
        );
        try {
            mysqli_stmt_execute($stmt);
            header("Location: login.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $error = "Username or Email already exists.";
            } else {
                $error = "Execute failed: " . $e->getMessage();
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
    <meta name="description" content="Register for HostelERP - Create your account">
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <title>Register - HostelERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-bg">
<div class="auth-container register">
    <div class="glass-card card-enter" id="registerCard">
        <img src="/WebTechProject/assets/images/logo.png" class="auth-logo" alt="HostelERP Logo">
        <h4 class="text-center mb-1" style="font-weight:700;">Create Account</h4>
        <p class="text-center mb-3" style="font-size:0.88rem;">Join HostelERP today</p>
        <?php if(isset($success)) echo "<div class='alert-glass-success mb-3'>$success</div>"; ?>
        <?php if(isset($error)) echo "<div class='alert-glass-danger mb-3'>$error</div>"; ?>
        <form method="POST" id="registerForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="field-group">
                    <label for="regFirstName">First Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="first_name" id="regFirstName"
                               class="form-input-modern" placeholder="First Name"
                               required pattern="[A-Za-z]+" autocomplete="given-name">
                        <span class="validation-icon icon-valid">✅</span>
                        <span class="validation-icon icon-invalid">❌</span>
                    </div>
                </div>
                <div class="field-group">
                    <label for="regLastName">Last Name</label>
                    <div class="input-wrapper">
                        <input type="text" name="last_name" id="regLastName"
                               class="form-input-modern" placeholder="Last Name (Optional)"
                               autocomplete="family-name">
                        <span class="validation-icon icon-valid">✅</span>
                        <span class="validation-icon icon-invalid">❌</span>
                    </div>
                </div>
            </div>
            <div class="field-group">
                <label for="regDob">Date of Birth</label>
                <div class="input-wrapper">
                    <input type="date" name="dob" id="regDob"
                           class="form-input-modern" max="<?php echo date('Y-m-d'); ?>">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regEmail">Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" id="regEmail"
                           class="form-input-modern" placeholder="Enter your email"
                           required autocomplete="email">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regPhone">Phone Number</label>
                <div class="input-wrapper">
                    <input type="text" name="phone" id="regPhone"
                           class="form-input-modern" placeholder="10-digit phone number (Optional)"
                           title="Enter exactly 10 digits"
                           autocomplete="tel">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regGender">Gender</label>
                <div class="input-wrapper">
                    <select name="gender" id="regGender" class="form-select-modern">
                        <option value="">Select Gender</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regAddress">Address</label>
                <div class="input-wrapper">
                    <textarea name="address" id="regAddress"
                              class="form-input-modern" placeholder="Enter your address (Optional)"
                              rows="2"></textarea>
                </div>
            </div>
            <div class="field-group">
                <label for="regRole">Role</label>
                <div class="input-wrapper">
                    <select name="role" id="regRole" class="form-select-modern" required>
                        <option value="">Select Role</option>
                        <option value="student">Student</option>
                        <option value="warden">Warden</option>
                        <option value="admin">Admin</option>
                    </select>
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regUsername">Username</label>
                <div class="input-wrapper">
                    <input type="text" name="username" id="regUsername"
                           class="form-input-modern" placeholder="Choose a username"
                           required autocomplete="username">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                </div>
            </div>
            <div class="field-group">
                <label for="regPassword">Password</label>
                <div class="input-wrapper password-wrapper">
                    <input type="password" name="password" id="regPassword"
                           class="form-input-modern" placeholder="Create a strong password"
                           required autocomplete="new-password">
                    <span class="validation-icon icon-valid">✅</span>
                    <span class="validation-icon icon-invalid">❌</span>
                    <button type="button" class="eye-toggle" id="regEyeToggle" aria-label="Toggle password visibility"></button>
                </div>
                <div class="strength-meter">
                    <div class="strength-meter-fill" id="strengthFill"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
            </div>
            <div class="g-recaptcha mb-3" data-sitekey="<?php echo $_ENV['RECAPTCHA_SITE_KEY'] ?? ''; ?>"></div>
            <button type="submit" name="register" id="registerBtn" class="btn-gradient" disabled>
                Create Account
            </button>
            <div class="text-center mt-field" style="font-size:0.9rem;">
                Already have an account?
                <a href="login.php" style="font-weight:600;">Login</a>
            </div>
        </form>
    </div>
</div>
<script src="/WebTechProject/assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    const btn  = document.getElementById('registerBtn');
    const firstName = document.getElementById('regFirstName');
    const lastName  = document.getElementById('regLastName');
    const dob       = document.getElementById('regDob');
    const email     = document.getElementById('regEmail');
    const phone     = document.getElementById('regPhone');
    const gender    = document.getElementById('regGender');
    const role      = document.getElementById('regRole');
    const username  = document.getElementById('regUsername');
    const password  = document.getElementById('regPassword');
    const eyeBtn    = document.getElementById('regEyeToggle');
    Validator.attachLiveValidation(firstName, { required: true, pattern: /^[A-Za-z]+$/ });
    Validator.attachLiveValidation(lastName,  { pattern: /^[A-Za-z]+$/ });
    Validator.attachLiveValidation(email,     { required: true, pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/ });
    Validator.attachLiveValidation(phone,     { pattern: /^[0-9]{10}$/ });
    Validator.attachLiveValidation(role,      { required: true });
    Validator.attachLiveValidation(username,  { required: true, minLen: 3 });
    Validator.attachLiveValidation(password,  {
        required: true,
        pattern: /^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/
    });
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    Validator.attachPasswordStrength(password, strengthFill, strengthText);
    createEyeToggle(password, eyeBtn);
    form.addEventListener('input',  updateBtn);
    form.addEventListener('change', updateBtn);
    function updateBtn() {
        btn.disabled = !form.checkValidity();
    }
    form.addEventListener('submit', function(e) {
        let pw = password.value;
        let pattern = /^(?=(?:.*[^A-Za-z0-9]){2,})(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{10,}$/;
        if (!pattern.test(pw)) {
            alert(
`Password must follow these rules:
• At least 10 characters
• At least 1 uppercase letter
• At least 1 lowercase letter
• At least 1 number
• At least 2 special characters`
            );
            e.preventDefault();
        }
    });
});
</script>
<?php include("footer.php"); ?>
</body>
</html>