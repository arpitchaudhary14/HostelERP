<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
if(isset($_POST['submit_leave'])){
    validate_csrf();
    $from   = $_POST['from_date'];
    $to     = $_POST['to_date'];
    $reason = trim($_POST['reason']);
    if(empty($from) || empty($to)){
        $error = "Please select both From and To dates.";
    } elseif(strtotime($from) < strtotime(date('Y-m-d'))){
        $error = "From date cannot be in the past.";
    } elseif(strtotime($to) < strtotime($from)){
        $error = "To date cannot be before From date.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO leave_requests (student_id, from_date, to_date, reason) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $from, $to, $reason);
        if(mysqli_stmt_execute($stmt)){
            $success = "Leave request submitted successfully.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
include("../header.php");
?>
<div class="container mt-4" style="max-width:600px;">
<div class="glass-card-light">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">Request Leave</h4>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">From Date</label>
<input type="date" name="from_date" class="form-input-light" min="<?= date('Y-m-d') ?>" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">To Date</label>
<input type="date" name="to_date" class="form-input-light" min="<?= date('Y-m-d') ?>" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Reason</label>
<textarea name="reason" class="form-input-light" rows="3" required></textarea>
</div>
<button type="submit" name="submit_leave" class="btn-gradient">Submit Request</button>
</form>
</div>
</div>
<?php include("../footer.php"); ?>