<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}
if(isset($_POST['add'])){
    validate_csrf();
    $room_number = trim($_POST['room_number']);
    $capacity    = intval($_POST['capacity']);
    if($room_number && $capacity > 0){
        $stmt = mysqli_prepare($conn, "INSERT INTO rooms (room_number, capacity, current_occupancy) VALUES (?,?,0)");
        mysqli_stmt_bind_param($stmt, "si", $room_number, $capacity);
        mysqli_stmt_execute($stmt);
        $success = "Room '$room_number' added successfully.";
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_number");
$rooms = [];
while($row = mysqli_fetch_assoc($result)){
    $rooms[] = $row;
}
include("../header.php");
?>
<div class="container mt-4">
<div class="glass-card-light" style="max-width:800px; margin:0 auto;">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">Manage Rooms</h3>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST" class="row g-2 mb-4">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="col-md-5">
    <input name="room_number" placeholder="Room Number" class="form-control" required>
</div>
<div class="col-md-4">
    <input name="capacity" type="number" min="1" placeholder="Capacity" class="form-control" required>
</div>
<div class="col-md-3">
    <button type="submit" name="add" class="btn btn-primary w-100">Add Room</button>
</div>
</form>
<table class="table table-bordered mb-0">
<thead class="table-dark">
<tr><th>Room</th><th>Capacity</th><th>Occupied</th><th>Available</th></tr>
</thead>
<tbody>
<?php foreach($rooms as $r): ?>
<tr>
<td><?= htmlspecialchars($r['room_number']) ?></td>
<td><?= intval($r['capacity']) ?></td>
<td><?= intval($r['current_occupancy']) ?></td>
<td><?= intval($r['capacity']) - intval($r['current_occupancy']) ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($rooms)): ?>
<tr><td colspan="4" class="text-center text-muted">No rooms added yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
<?php include("../footer.php"); ?>