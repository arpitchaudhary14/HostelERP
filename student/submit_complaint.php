<?php
include("../session_check.php");
include("../db.php");
$user_id = $_SESSION['user_id'];
if(isset($_POST['submit_complaint'])){
    validate_csrf();
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $stmt = mysqli_prepare($conn, "INSERT INTO complaints (student_id, subject, message) VALUES (?,?,?)");
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $subject, $message);
    if(mysqli_stmt_execute($stmt)){
        $success = "Complaint submitted successfully.";
    } else {
        $error = "Something went wrong. Please try again.";
    }
}
include("../header.php");
?>
<div class="container mt-4" style="max-width:700px;">
<div class="glass-card-light">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">Submit Complaint</h4>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Subject</label>
<input type="text" name="subject" class="form-input-light" required maxlength="200">
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Message</label>
<textarea name="message" class="form-input-light" rows="4" required></textarea>
</div>
<button type="submit" name="submit_complaint" class="btn-gradient">Submit Complaint</button>
</form>
</div>
</div>
<?php include("../footer.php"); ?>