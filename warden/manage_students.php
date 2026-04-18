<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM users WHERE role='student'";
if(!empty($search)){
    $query .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR CONCAT(first_name,' ',last_name) LIKE '%$search%')";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h3>Manage Students</h3>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search Student"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary w-100">Search</button>
</div>
<div class="col-md-4 text-end">
<a href="../export_students.php" class="btn btn-success">Export Students (CSV)</a>
</div>
</div>
</form>
<table class="table table-bordered">
<tr>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Action</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= htmlspecialchars(trim(($row['first_name']??'').' '.($row['last_name']??''))) ?></td>
<td><?= $row['email'] ?></td>
<td><?= $row['phone'] ?></td>
<td>
<a href="view_student.php?id=<?= $row['id'] ?>" 
class="btn btn-info btn-sm">View</a>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>