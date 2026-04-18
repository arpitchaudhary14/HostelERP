<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$total = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as total FROM fees"))['total'];
$paid = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as paid FROM fees WHERE status='Paid'"))['paid'];
$pending = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as pending FROM fees WHERE status='Pending'"))['pending'];
$overdue = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as overdue FROM fees WHERE status='Overdue'"))['overdue'];
include("../header.php");
?>
<div class="container mt-4">
<h4>Fee Reports</h4>
<hr>
<div class="row g-4">
<div class="col-md-3">
<div class="card text-center shadow-sm">
<div class="card-body">
<h5>Total Revenue</h5>
<h3>₹<?php echo $total ?? 0; ?></h3>
</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-center shadow-sm border-success">
<div class="card-body">
<h5>Paid</h5>
<h3 class="text-success">₹<?php echo $paid ?? 0; ?></h3>
</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-center shadow-sm border-warning">
<div class="card-body">
<h5>Pending</h5>
<h3 class="text-warning">₹<?php echo $pending ?? 0; ?></h3>
</div>
</div>
</div>
<div class="col-md-3">
<div class="card text-center shadow-sm border-danger">
<div class="card-body">
<h5>Overdue</h5>
<h3 class="text-danger">₹<?php echo $overdue ?? 0; ?></h3>
</div>
</div>
</div>
</div>
</div>
<?php include("../footer.php"); ?>