<?php
include("../session_check.php");
include("../db.php");
$user_id = $_SESSION['user_id'];
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM complaints WHERE student_id='$user_id'";
if(!empty($search)){
    $query .= " AND status LIKE '%$search%'";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>My Complaints</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search by Status (Pending/Approved/Rejected)"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<?php if(mysqli_num_rows($result) == 0){ ?>
<div class="alert alert-info">No complaints found.</div>
<?php } else { ?>
<table class="table table-bordered table-hover">
<tr>
<th>Subject</th>
<th>Message</th>
<th>Status</th>
<th>Date</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= htmlspecialchars($row['subject'] ?? '') ?></td>
<td><?= htmlspecialchars($row['message']) ?></td>
<td>
<?php if($row['status']==="Pending"){ ?>
<span class="badge bg-warning text-dark">Pending</span>
<?php } elseif($row['status']==="Approved"){ ?>
<span class="badge bg-success">Approved</span>
<?php } elseif($row['status']==="Rejected"){ ?>
<span class="badge bg-danger">Rejected</span>
<?php } ?>
</td>
<td><?= htmlspecialchars($row['created_at']) ?></td>
</tr>
<?php } ?>
</table>
<?php } ?>
</div>
<?php include("../footer.php"); ?>