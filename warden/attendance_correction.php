<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$prefill_date = isset($_GET['date']) ? $_GET['date'] : '';
$prefill_current = isset($_GET['current']) ? $_GET['current'] : '';
if(isset($_POST['submit_correction'])){
    validate_csrf();
    $corr_date = $_POST['correction_date'];
    $current_st = $_POST['current_status'];
    $requested_st = $_POST['requested_status'];
    $reason = trim($_POST['reason']);
    $allowed = ['present','absent','leave'];
    if(empty($corr_date) || empty($reason)){
        $error = "Please fill all fields.";
    } elseif(!in_array($current_st, $allowed) || !in_array($requested_st, $allowed)){
        $error = "Invalid status selected.";
    } elseif($current_st === $requested_st){
        $error = "Requested status is the same as current status.";
    } elseif($corr_date === date('Y-m-d')){
        $error = "Same-day corrections should be resolved directly by Admin.";
    } else {
        $chk = mysqli_prepare($conn, "SELECT id FROM attendance_corrections WHERE user_id=? AND date=? AND status='Pending'");
        mysqli_stmt_bind_param($chk, "is", $user_id, $corr_date);
        mysqli_stmt_execute($chk);
        $chk_r = mysqli_stmt_get_result($chk);
        if(mysqli_num_rows($chk_r) > 0){
            $error = "A pending correction request already exists for this date.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO attendance_corrections (user_id, role, date, current_status, requested_status, reason) VALUES (?,?,?,?,?,?)");
            $role = 'warden';
            mysqli_stmt_bind_param($stmt, "isssss", $user_id, $role, $corr_date, $current_st, $requested_st, $reason);
            if(mysqli_stmt_execute($stmt)){
                $success = "Correction request submitted. Awaiting Admin approval.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
include("../header.php");
?>
<div class="container mt-4" style="max-width:600px;">
<div class="glass-card-light">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">📝 Request Attendance Correction</h4>
<p style="color:#888; font-size:0.9rem;">Submit a formal request to Admin to correct a past attendance record.</p>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Date</label>
<input type="date" name="correction_date" class="form-input-light" max="<?= date('Y-m-d', strtotime('-1 day')) ?>" value="<?= htmlspecialchars($prefill_date) ?>" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Current Status</label>
<select name="current_status" class="form-select" required>
<option value="">Select</option>
<option value="present" <?= $prefill_current==='present'?'selected':'' ?>>Present</option>
<option value="absent" <?= $prefill_current==='absent'?'selected':'' ?>>Absent</option>
<option value="leave" <?= $prefill_current==='leave'?'selected':'' ?>>Leave</option>
</select>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Requested Status</label>
<select name="requested_status" class="form-select" required>
<option value="">Select</option>
<option value="present">Present</option>
<option value="absent">Absent</option>
<option value="leave">Leave</option>
</select>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555; font-size:0.88rem;">Reason for Correction</label>
<textarea name="reason" class="form-input-light" rows="3" placeholder="Explain why this attendance needs correction..." required></textarea>
</div>
<button type="submit" name="submit_correction" class="btn-gradient">Submit Correction Request</button>
</form>
</div>
</div>
<?php include("../footer.php"); ?>