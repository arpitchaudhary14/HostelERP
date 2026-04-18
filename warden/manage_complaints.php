<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role']!='warden' && $_SESSION['role']!='admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_GET['resolve'])){
    $id = intval($_GET['resolve']);
    mysqli_query($conn,"UPDATE complaints SET status='Approved' WHERE id='$id'");
}
if(isset($_GET['reject'])){
    $id = intval($_GET['reject']);
    mysqli_query($conn,"UPDATE complaints SET status='Rejected' WHERE id='$id'");
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "
SELECT complaints.*, CONCAT(users.first_name,' ',COALESCE(users.last_name,'')) as full_name
FROM complaints
JOIN users ON complaints.student_id = users.id
";
if(!empty($search)){
    $query .= " WHERE complaints.status LIKE '%$search%'
                OR CONCAT(users.first_name,' ',users.last_name) LIKE '%$search%'";
}
$query .= " ORDER BY complaints.created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>Manage Complaints</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search by Status or Student Name"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<table class="table table-bordered">
<tr>
<th>Student</th>
<th>Message</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= $row['full_name'] ?></td>
<td><?= $row['message'] ?></td>
<td><?= $row['status'] ?></td>
<td>
<?php if($row['status']=="Pending"){ ?>
<a href="?resolve=<?= $row['id'] ?>" 
class="btn btn-success btn-sm">Approve</a>
<a href="?reject=<?= $row['id'] ?>" 
class="btn btn-danger btn-sm">Reject</a>
<?php } ?>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>