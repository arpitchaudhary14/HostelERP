<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['change_role'])){
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    mysqli_query($conn,"UPDATE users SET role='$new_role' WHERE id='$user_id'");
    mysqli_query($conn,"
        INSERT INTO activity_logs (user_id, role, action)
        VALUES ('{$_SESSION['user_id']}','admin',
        'Changed role of user ID $user_id to $new_role')
    ");
}
if(isset($_GET['ban'])){
    $id = intval($_GET['ban']);
    mysqli_query($conn,"UPDATE users SET status='banned' WHERE id='$id'");
}
if(isset($_GET['unban'])){
    $id = intval($_GET['unban']);
    mysqli_query($conn,"UPDATE users SET status='active' WHERE id='$id'");
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM users";
if(!empty($search)){
    $query .= " WHERE full_name LIKE '%$search%' OR CONCAT(first_name,' ',last_name) LIKE '%$search%' OR email LIKE '%$search%'";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>Manage Users</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control" 
placeholder="Search by name or email"
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
<th>Role</th>
<th>Status</th>
<th>Actions</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= htmlspecialchars(trim(($row['first_name']??'').' '.($row['last_name']??''))) ?></td>
<td><?= $row['email'] ?></td>
<td>
<form method="POST" class="d-flex">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<input type="hidden" name="user_id" value="<?= $row['id'] ?>">
<select name="role" class="form-select form-select-sm">
<option <?= $row['role']=="student"?"selected":"" ?>>student</option>
<option <?= $row['role']=="warden"?"selected":"" ?>>warden</option>
<option <?= $row['role']=="admin"?"selected":"" ?>>admin</option>
</select>
<button class="btn btn-sm btn-primary ms-2" name="change_role">Update</button>
</form>
</td>
<td>
<?php if($row['status']=="active"){ ?>
<span class="badge bg-success">Active</span>
<?php } else { ?>
<span class="badge bg-danger">Banned</span>
<?php } ?>
</td>
<td>
<?php if($row['status']=="active"){ ?>
<a href="?ban=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Ban</a>
<?php } else { ?>
<a href="?unban=<?= $row['id'] ?>" class="btn btn-sm btn-success">Unban</a>
<?php } ?>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>