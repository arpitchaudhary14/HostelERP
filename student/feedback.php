<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['submit_feedback'])){
    validate_csrf();
    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating  = intval($_POST['rating'] ?? 5);
    $stmt = mysqli_prepare($conn, "INSERT INTO feedback (user_id, role, type, subject, message, rating) VALUES (?, 'student', 'Hostel', ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issi", $user_id, $subject, $message, $rating);
    mysqli_stmt_execute($stmt);
    $success = "Feedback submitted successfully!";
}
include("../header.php");
?>
<div class="container mt-4">
<h4>Hostel Feedback</h4>
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
<div class="mb-3">
<label>Rating (1-5)</label>
<select name="rating" class="form-select">
<option value="5">5 - Excellent</option>
<option value="4">4 - Good</option>
<option value="3">3 - Average</option>
<option value="2">2 - Poor</option>
<option value="1">1 - Very Poor</option>
</select>
</div>
<button type="submit" class="btn btn-primary" name="submit_feedback">
Submit Feedback
</button>
</form>
</div>
<?php include("../footer.php"); ?>