<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['add_room'])){
    validate_csrf();
    $room_number = trim($_POST['room_number'] ?? '');
    $capacity    = intval($_POST['capacity'] ?? 0);
    if($room_number && $capacity > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO rooms (room_number, capacity) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $room_number, $capacity);
        mysqli_stmt_execute($stmt);
    }
}
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    mysqli_query($conn,"DELETE FROM rooms WHERE id='$id'");
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM rooms";
if(!empty($search)){
    $query .= " WHERE room_number LIKE '%$search%'";
}
$query .= " ORDER BY room_number ASC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>Manage Rooms</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control" 
placeholder="Search Room Number"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<form method="POST" class="row g-3 mb-4">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
<div class="col-md-4">
<input type="text" name="room_number" class="form-control" placeholder="Room Number" required>
</div>
<div class="col-md-4">
<input type="number" name="capacity" class="form-control" placeholder="Capacity" required>
</div>
<div class="col-md-4">
<button type="submit" class="btn btn-primary" name="add_room">Add Room</button>
</div>
</form>
<table class="table table-bordered">
<tr>
<th>Room Number</th>
<th>Capacity</th>
<th>Action</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= $row['room_number'] ?></td>
<td><?= $row['capacity'] ?></td>
<td>
<a href="?delete=<?= $row['id'] ?>" 
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this room?')">
Delete
</a>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>