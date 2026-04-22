<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validate_csrf();
    $date = date("Y-m-d");
    foreach($_POST['status'] as $warden_id => $status){
        $warden_id = intval($warden_id);
        $allowed = ['present','absent','leave'];
        if(!in_array($status, $allowed)) continue;
        $stmt = mysqli_prepare($conn,
            "INSERT INTO attendance (user_id, date, status, marked_by)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE status=VALUES(status)"
        );
        $marker = intval($_SESSION['user_id']);
        mysqli_stmt_bind_param($stmt, "issi", $warden_id, $date, $status, $marker);
        mysqli_stmt_execute($stmt);
    }
    $success = "Warden attendance marked for " . date("d M Y") . ".";
}
$wardens = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name FROM users WHERE role='warden' ORDER BY first_name");
include("../header.php");
?>
<div class="container mt-4">
<div class="glass-card-light" style="max-width:700px; margin:0 auto;">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">📋 Mark Warden Attendance</h3>
<p style="color:#666;">Date: <strong><?= date("d M Y") ?></strong></p>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<table class="table table-bordered">
<thead class="table-dark">
<tr><th>Warden</th><th>Status</th></tr>
</thead>
<tbody>
<?php while($w = mysqli_fetch_assoc($wardens)): ?>
<tr>
<td><?= htmlspecialchars($w['full_name']) ?></td>
<td>
<select name="status[<?= $w['id'] ?>]" class="form-select form-select-sm">
<option value="present">Present</option>
<option value="absent">Absent</option>
<option value="leave">Leave</option>
</select>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<button type="submit" class="btn-gradient">Submit Attendance</button>
</form>
</div>
</div>
<?php include("../footer.php"); ?>