<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM warden_leave_requests WHERE warden_id=? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
include("../header.php");
?>
<div class="container mt-4">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">📋 My Leave Requests</h4>
<div class="glass-card-light">
<table class="table table-bordered mb-0">
<thead class="table-dark">
<tr>
    <th>From</th>
    <th>To</th>
    <th>Reason</th>
    <th>Status</th>
    <th>Submitted</th>
</tr>
</thead>
<tbody>
<?php if(mysqli_num_rows($result) > 0): ?>
<?php while($row = mysqli_fetch_assoc($result)): ?>
<tr>
<td><?= htmlspecialchars($row['from_date']) ?></td>
<td><?= htmlspecialchars($row['to_date']) ?></td>
<td><?= htmlspecialchars($row['reason']) ?></td>
<td>
<?php
$s = $row['status'];
$cls = $s === 'Approved' ? 'bg-success' : ($s === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark');
echo "<span class='badge $cls'>$s</span>";
?>
</td>
<td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>
<?php endwhile; ?>
<?php else: ?>
<tr><td colspan="5" class="text-center text-muted">No leave requests yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
<?php include("../footer.php"); ?>