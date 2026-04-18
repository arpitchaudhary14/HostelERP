<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['submit_feedback'])){
    validate_csrf();
    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, role, type, subject, message) VALUES (?, 'warden', 'System', ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $user_id, $subject, $message);
    mysqli_stmt_execute($stmt);
    $success = "Feedback submitted to Admin successfully!";
}
include("../header.php");
?>
<div class="container mt-4">
<h4>System Feedback (To Admin)</h4>
<hr>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label>Subject</label>
<input type="text" name="subject" class="form-control" required>
</div>
<div class="mb-3">
<label>Message</label>
<textarea name="message" class="form-control" required></textarea>
</div>
<button type="submit" class="btn btn-primary" name="submit_feedback">
Submit Feedback
</button>
</form>
</div>
<?php include("../footer.php"); ?>