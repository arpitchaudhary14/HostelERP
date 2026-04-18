<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$total_students = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role='student'"))['total'];
$total_wardens = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM users WHERE role='warden'"))['total'];
$total_rooms = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM rooms"))['total'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(amount) as total FROM fees WHERE status='Paid'"))['total'];
$pending_leaves = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM leave_requests WHERE status='Pending'"))['total'];
$open_complaints = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT COUNT(*) as total FROM complaints WHERE status='Pending'"))['total'];
include("../header.php");
?>
<div class="container mt-4">
<h4>System Statistics</h4>
<hr>
<div class="row g-4">
<div class="col-md-4">
<div class="card text-center shadow-sm">
<div class="card-body">
<h5>Students</h5>
<h3><?php echo $total_students; ?></h3>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card text-center shadow-sm">
<div class="card-body">
<h5>Wardens</h5>
<h3><?php echo $total_wardens; ?></h3>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card text-center shadow-sm">
<div class="card-body">
<h5>Rooms</h5>
<h3><?php echo $total_rooms; ?></h3>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card text-center shadow-sm border-success">
<div class="card-body">
<h5>Total Revenue</h5>
<h3 class="text-success">₹<?php echo $total_revenue ?? 0; ?></h3>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card text-center shadow-sm border-warning">
<div class="card-body">
<h5>Pending Leaves</h5>
<h3 class="text-warning"><?php echo $pending_leaves; ?></h3>
</div>
</div>
</div>
<div class="col-md-4">
<div class="card text-center shadow-sm border-danger">
<div class="card-body">
<h5>Open Complaints</h5>
<h3 class="text-danger"><?php echo $open_complaints; ?></h3>
</div>
</div>
</div>
</div>
</div>
<?php include("../footer.php"); ?>