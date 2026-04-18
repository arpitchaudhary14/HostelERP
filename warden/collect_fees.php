<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}
if(isset($_POST['collect'])){
    validate_csrf();
    $fee_id = intval($_POST['fee_id']);
    $method = mysqli_real_escape_string($conn, $_POST['method'] ?? 'Cash');
    $stmt = mysqli_prepare($conn, "UPDATE fees SET status='Paid', paid_on=CURDATE() WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $fee_id);
    mysqli_stmt_execute($stmt);
    $success = "Fee marked as Paid.";
}
mysqli_query($conn, "UPDATE fees SET status='Overdue' WHERE due_date < CURDATE() AND status='Pending'");
$students = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name FROM users WHERE role='student' ORDER BY first_name");
$fees_result = mysqli_query($conn, "
    SELECT f.*, CONCAT(u.first_name,' ',COALESCE(u.last_name,'')) as full_name
    FROM fees f
    JOIN users u ON f.student_id = u.id
    ORDER BY f.created_at DESC
");
include("../header.php");
?>
<div class="container mt-4">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">Collect Fees</h3>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<div class="glass-card-light mb-4">
<h5 class="mb-3" style="font-weight:600;">All Fee Records</h5>
<div class="table-responsive">
<table class="table table-bordered mb-0">
<thead class="table-dark">
<tr>
    <th>Student</th>
    <th>Amount</th>
    <th>Due Date</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>
<tbody>
<?php while($f = mysqli_fetch_assoc($fees_result)): ?>
<tr>
<td><?= htmlspecialchars($f['full_name']) ?></td>
<td>₹<?= number_format($f['amount'], 2) ?></td>
<td><?= htmlspecialchars($f['due_date']) ?></td>
<td>
<?php
$s = $f['status'];
$cls = $s === 'Paid' ? 'bg-success' : ($s === 'Overdue' ? 'bg-danger' : 'bg-warning text-dark');
echo "<span class='badge $cls'>$s</span>";
?>
</td>
<td>
<?php if($f['status'] !== 'Paid'): ?>
<form method="POST" class="d-flex gap-2">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<input type="hidden" name="fee_id" value="<?= $f['id'] ?>">
<select name="method" class="form-select form-select-sm" style="width:auto;">
    <option>Cash</option>
    <option>UPI</option>
    <option>Bank</option>
</select>
<button name="collect" class="btn btn-success btn-sm">Mark Paid</button>
</form>
<?php else: ?>
<span class="text-muted small">Paid on <?= $f['paid_on'] ?></span>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php include("../footer.php"); ?>