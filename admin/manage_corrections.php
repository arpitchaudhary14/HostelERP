<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_GET['approve']) && intval($_GET['approve']) > 0){
    $cid = intval($_GET['approve']);
    $admin_id = intval($_SESSION['user_id']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM attendance_corrections WHERE id=? AND status='Pending'");
    mysqli_stmt_bind_param($stmt, "i", $cid);
    mysqli_stmt_execute($stmt);
    $corr = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if($corr){
        $stmt2 = mysqli_prepare($conn,
            "INSERT INTO attendance (user_id, date, status, marked_by)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)"
        );
        mysqli_stmt_bind_param($stmt2, "issi", $corr['user_id'], $corr['date'], $corr['requested_status'], $admin_id);
        mysqli_stmt_execute($stmt2);
        $stmt3 = mysqli_prepare($conn, "UPDATE attendance_corrections SET status='Approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?");
        mysqli_stmt_bind_param($stmt3, "ii", $admin_id, $cid);
        mysqli_stmt_execute($stmt3);
        $success = "Correction approved and attendance updated.";
    }
}
if(isset($_GET['reject']) && intval($_GET['reject']) > 0){
    $cid = intval($_GET['reject']);
    $admin_id = intval($_SESSION['user_id']);
    $stmt = mysqli_prepare($conn, "UPDATE attendance_corrections SET status='Rejected', reviewed_by=?, reviewed_at=NOW() WHERE id=? AND status='Pending'");
    mysqli_stmt_bind_param($stmt, "ii", $admin_id, $cid);
    mysqli_stmt_execute($stmt);
    $error_msg = "Correction request rejected.";
}
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['override'])){
    validate_csrf();
    $o_user = intval($_POST['override_user']);
    $o_date = $_POST['override_date'];
    $o_status = $_POST['override_status'];
    $admin_id = intval($_SESSION['user_id']);
    $allowed = ['present','absent','leave'];
    if($o_user > 0 && !empty($o_date) && in_array($o_status, $allowed)){
        $stmt = mysqli_prepare($conn,
            "INSERT INTO attendance (user_id, date, status, marked_by)
             VALUES (?,?,?,?)
             ON DUPLICATE KEY UPDATE status=VALUES(status), marked_by=VALUES(marked_by)"
        );
        mysqli_stmt_bind_param($stmt, "issi", $o_user, $o_date, $o_status, $admin_id);
        mysqli_stmt_execute($stmt);
        $success = "Attendance record overridden successfully.";
    }
}
$query = "
SELECT ac.*, CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) as full_name
FROM attendance_corrections ac
JOIN users u ON ac.user_id = u.id
ORDER BY ac.status = 'Pending' DESC, ac.created_at DESC
";
$result = mysqli_query($conn, $query);
$all_users = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name, role FROM users WHERE role IN ('student','warden') ORDER BY role, first_name");
include("../header.php");
?>
<div class="container mt-4">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">👑 Attendance Corrections & Override</h3>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error_msg)) echo "<div class='alert alert-warning'>$error_msg</div>"; ?>
<div class="glass-card-light mb-4">
<h5 style="font-weight:600; color:#1a1a2e;">⚡ Direct Override <small style="color:#888; font-weight:400;">(Admin only — edit any record)</small></h5>
<form method="POST" class="row g-3 mt-2">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="col-md-3">
    <label style="font-weight:500; color:#555; font-size:0.88rem;">User</label>
    <select name="override_user" class="form-select form-select-sm" required>
        <option value="">Select User</option>
        <?php while($u = mysqli_fetch_assoc($all_users)): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['full_name']) ?> (<?= ucfirst($u['role']) ?>)</option>
        <?php endwhile; ?>
    </select>
</div>
<div class="col-md-3">
    <label style="font-weight:500; color:#555; font-size:0.88rem;">Date</label>
    <input type="date" name="override_date" class="form-control form-control-sm" required>
</div>
<div class="col-md-3">
    <label style="font-weight:500; color:#555; font-size:0.88rem;">Set Status</label>
    <select name="override_status" class="form-select form-select-sm" required>
        <option value="present">Present</option>
        <option value="absent">Absent</option>
        <option value="leave">Leave</option>
    </select>
</div>
<div class="col-md-3 d-flex align-items-end">
    <button type="submit" name="override" class="btn-gradient" style="width:100%; padding:8px 16px;">Override Record</button>
</div>
</form>
</div>
<div class="glass-card-light">
<h5 class="mb-3" style="font-weight:600; color:#1a1a2e;">📝 Correction Requests</h5>
<table class="table table-bordered mb-0">
<thead class="table-dark">
<tr>
<th>Name</th>
<th>Role</th>
<th>Date</th>
<th>Current</th>
<th>Requested</th>
<th>Reason</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($result) > 0): ?>
<?php while($row = mysqli_fetch_assoc($result)):
    $s = $row['status'];
    $cls = $s==='Approved'?'bg-success':($s==='Rejected'?'bg-danger':'bg-warning text-dark');
    $cur_cls = $row['current_status']==='present'?'bg-success':($row['current_status']==='absent'?'bg-danger':'bg-warning text-dark');
    $req_cls = $row['requested_status']==='present'?'bg-success':($row['requested_status']==='absent'?'bg-danger':'bg-warning text-dark');
?>
<tr>
<td><?= htmlspecialchars($row['full_name']) ?></td>
<td><span class="badge bg-info"><?= ucfirst($row['role']) ?></span></td>
<td><?= htmlspecialchars($row['date']) ?></td>
<td><span class="badge <?= $cur_cls ?>"><?= ucfirst($row['current_status']) ?></span></td>
<td><span class="badge <?= $req_cls ?>"><?= ucfirst($row['requested_status']) ?></span></td>
<td><?= htmlspecialchars($row['reason']) ?></td>
<td><span class="badge <?= $cls ?>"><?= $s ?></span></td>
<td>
<?php if($row['status'] == 'Pending'): ?>
<a href="?approve=<?= $row['id'] ?>" class="btn btn-success btn-sm">Approve</a>
<a href="?reject=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="8" class="text-center text-muted">No correction requests found.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
<?php include("../footer.php"); ?>