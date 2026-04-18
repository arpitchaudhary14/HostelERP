<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['update_settings'])){
    validate_csrf();
    $name  = trim($_POST['hostel_name']);
    $email = trim($_POST['contact_email']);
    $phone = trim($_POST['contact_phone']);
    $stmt = mysqli_prepare($conn,
        "UPDATE system_settings SET hostel_name=?, contact_email=?, contact_phone=? WHERE id=1"
    );
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $phone);
    mysqli_stmt_execute($stmt);
    $success = "Settings updated successfully.";
}
$settings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM system_settings WHERE id=1"));
include("../header.php");
?>
<div class="container mt-4" style="max-width:700px;">
<div class="glass-card-light">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">System Settings</h4>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Hostel Name</label>
<input type="text" name="hostel_name" class="form-control"
value="<?php echo htmlspecialchars($settings['hostel_name'] ?? ''); ?>" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Contact Email</label>
<input type="email" name="contact_email" class="form-control"
value="<?php echo htmlspecialchars($settings['contact_email'] ?? ''); ?>" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Contact Phone</label>
<input type="text" name="contact_phone" class="form-control"
value="<?php echo htmlspecialchars($settings['contact_phone'] ?? ''); ?>" required>
</div>
<button type="submit" class="btn-gradient" name="update_settings">Update Settings</button>
</form>
</div>
</div>
<?php include("../footer.php"); ?>