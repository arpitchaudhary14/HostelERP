<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM fees WHERE student_id='$user_id'";
if(!empty($search)){
    $query .= " AND status LIKE '%$search%'";
}
$query .= " ORDER BY due_date DESC";
$result = mysqli_query($conn,$query);
$summary_query = "
SELECT 
SUM(amount) as total,
SUM(CASE WHEN status='Paid' THEN amount ELSE 0 END) as paid,
SUM(CASE WHEN status='Pending' THEN amount ELSE 0 END) as pending
FROM fees 
WHERE student_id='$user_id'
";
$summary = mysqli_fetch_assoc(mysqli_query($conn,$summary_query));
include("../header.php");
?>
<div class="container mt-4">
<h3>My Fees</h3>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search by Status"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<div class="row mb-4">
<div class="col-md-4">
<div class="card bg-primary text-white p-3">
<h6>Total</h6>
₹ <?= number_format($summary['total'] ?? 0,2) ?>
</div>
</div>
<div class="col-md-4">
<div class="card bg-success text-white p-3">
<h6>Paid</h6>
₹ <?= number_format($summary['paid'] ?? 0,2) ?>
</div>
</div>
<div class="col-md-4">
<div class="card bg-danger text-white p-3">
<h6>Pending</h6>
₹ <?= number_format($summary['pending'] ?? 0,2) ?>
</div>
</div>
</div>
<table class="table table-bordered">
<tr>
<th>Amount</th>
<th>Due Date</th>
<th>Status</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td>₹ <?= number_format($row['amount'],2) ?></td>
<td><?= $row['due_date'] ?></td>
<td>
<?php if($row['status']=="Paid"){ ?>
<span class="badge bg-success">Paid</span>
<?php } elseif($row['status']=="Overdue"){ ?>
<span class="badge bg-danger">Overdue</span>
<?php } else { ?>
<span class="badge bg-warning text-dark">Pending</span>
<?php } ?>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>