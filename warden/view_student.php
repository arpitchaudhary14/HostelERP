<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}
$student_id = intval($_GET['id'] ?? 0);
if(!$student_id){ header("Location: manage_students.php"); exit; }
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id=? AND role='student'");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if(!$student){ header("Location: manage_students.php"); exit; }
$full_name = trim(($student['first_name'] ?? '') . " " . ($student['last_name'] ?? ''));
if(empty(trim($full_name))) $full_name = $student['full_name'] ?? 'Unknown';
$room_stmt = mysqli_prepare($conn, "SELECT r.* FROM room_allocations ra JOIN rooms r ON ra.room_id=r.id WHERE ra.student_id=? AND ra.is_active=1 LIMIT 1");
mysqli_stmt_bind_param($room_stmt, "i", $student_id);
mysqli_stmt_execute($room_stmt);
$room = mysqli_fetch_assoc(mysqli_stmt_get_result($room_stmt));
$leaves_stmt = mysqli_prepare($conn, "SELECT * FROM leave_requests WHERE student_id=? ORDER BY created_at DESC LIMIT 5");
mysqli_stmt_bind_param($leaves_stmt, "i", $student_id);
mysqli_stmt_execute($leaves_stmt);
$leaves = mysqli_fetch_all(mysqli_stmt_get_result($leaves_stmt), MYSQLI_ASSOC);
$comp_stmt = mysqli_prepare($conn, "SELECT * FROM complaints WHERE student_id=? ORDER BY created_at DESC LIMIT 5");
mysqli_stmt_bind_param($comp_stmt, "i", $student_id);
mysqli_stmt_execute($comp_stmt);
$complaints = mysqli_fetch_all(mysqli_stmt_get_result($comp_stmt), MYSQLI_ASSOC);
$att = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    SUM(status='present') as present,
    SUM(status='absent') as absent,
    COUNT(*) as total
    FROM attendance WHERE user_id=$student_id"));
include("../header.php");
?>
<div class="container mt-4" style="max-width:800px;">
<a href="manage_students.php" class="btn btn-outline-secondary btn-sm mb-3">← Back to Students</a>
<div class="glass-card-light mb-4">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;"><?= htmlspecialchars($full_name) ?></h4>
<div class="row">
<div class="col-md-6">
    <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
    <p><strong>Phone:</strong> <?= htmlspecialchars($student['phone'] ?? '—') ?></p>
    <p><strong>Role:</strong> <?= ucfirst($student['role']) ?></p>
    <p><strong>Joined:</strong> <?= htmlspecialchars($student['created_at'] ?? '—') ?></p>
</div>
<div class="col-md-6">
    <p><strong>Room:</strong> <?= $room ? htmlspecialchars($room['room_number']) : 'Not assigned' ?></p>
    <p><strong>Attendance:</strong>
        <?php
        $total = intval($att['total'] ?? 0);
        $pres  = intval($att['present'] ?? 0);
        echo "$pres / $total days";
        if($total > 0) echo " (" . round($pres/$total*100) . "%)";
        ?>
    </p>
</div>
</div>
</div>
<div class="glass-card-light mb-4">
<h5 class="mb-3" style="font-weight:600; color:#1a1a2e;">Recent Leave Requests</h5>
<?php if($leaves): ?>
<table class="table table-bordered mb-0">
<thead class="table-dark"><tr><th>From</th><th>To</th><th>Reason</th><th>Status</th></tr></thead>
<tbody>
<?php foreach($leaves as $l): ?>
<tr>
<td><?= htmlspecialchars($l['from_date']) ?></td>
<td><?= htmlspecialchars($l['to_date']) ?></td>
<td><?= htmlspecialchars($l['reason']) ?></td>
<td><span class="badge <?= $l['status']==='Approved'?'bg-success':($l['status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= $l['status'] ?></span></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p class="text-muted">No leave requests.</p>
<?php endif; ?>
</div>
<div class="glass-card-light">
<h5 class="mb-3" style="font-weight:600; color:#1a1a2e;">Recent Complaints</h5>
<?php if($complaints): ?>
<table class="table table-bordered mb-0">
<thead class="table-dark"><tr><th>Subject</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php foreach($complaints as $c): ?>
<tr>
<td><?= htmlspecialchars($c['subject'] ?? '') ?></td>
<td><span class="badge <?= $c['status']==='Approved'?'bg-success':($c['status']==='Rejected'?'bg-danger':'bg-warning text-dark') ?>"><?= $c['status'] ?></span></td>
<td><?= htmlspecialchars($c['created_at']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p class="text-muted">No complaints.</p>
<?php endif; ?>
</div>
</div>
<?php include("../footer.php"); ?>