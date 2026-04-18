<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "
SELECT activity_logs.*, CONCAT(users.first_name,' ',COALESCE(users.last_name,'')) as full_name
FROM activity_logs
LEFT JOIN users ON activity_logs.user_id = users.id
";
if(!empty($search)){
    $query .= " WHERE activity_logs.action LIKE '%$search%'";
}
$query .= " ORDER BY activity_logs.created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>System Activity Logs</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control" 
placeholder="Search by Action"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<table class="table table-bordered table-striped">
<tr>
<th>User</th>
<th>Action</th>
<th>Date</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= htmlspecialchars($row['full_name'] ?? 'System') ?></td>
<td><?= $row['action'] ?></td>
<td><?= $row['created_at'] ?></td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>