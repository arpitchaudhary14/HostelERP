<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden' && $_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_GET['action']) && isset($_GET['id'])){
    $id     = intval($_GET['id']);
    $action = $_GET['action'];
    if($action === "approve"){
        $stmt = mysqli_prepare($conn, "UPDATE leave_requests SET status='Approved' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
    if($action === "reject"){
        $stmt = mysqli_prepare($conn, "UPDATE leave_requests SET status='Rejected' WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
    }
}
$query = "
SELECT lr.*, CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) as full_name
FROM leave_requests lr
JOIN users u ON lr.student_id = u.id
ORDER BY lr.created_at DESC
";
$result = mysqli_query($conn, $query);
include("../header.php");
?>
<div class="container mt-4">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">Leave Requests</h4>
<div class="glass-card-light">
<table class="table table-bordered mb-0">
<thead class="table-dark">
<tr>
<th>Student</th>
<th>From</th>
<th>To</th>
<th>Reason</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?php echo htmlspecialchars($row['full_name']); ?></td>
<td><?php echo htmlspecialchars($row['from_date']); ?></td>
<td><?php echo htmlspecialchars($row['to_date']); ?></td>
<td><?php echo htmlspecialchars($row['reason']); ?></td>
<td><?php
$s = $row['status'];
$cls = $s==='Approved'?'bg-success':($s==='Rejected'?'bg-danger':'bg-warning text-dark');
echo "<span class='badge $cls'>$s</span>";
?></td>
<td>
<?php if($row['status'] == 'Pending'){ ?>
<a href="?action=approve&id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Approve</a>
<a href="?action=reject&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm">Reject</a>
<?php } ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>
</div>
<?php include("../footer.php"); ?>