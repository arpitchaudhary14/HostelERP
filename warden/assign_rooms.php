<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validate_csrf();
    $student_id = intval($_POST['student_id']);
    $room_id    = intval($_POST['room_id']);
    $stmt = mysqli_prepare($conn, "INSERT INTO room_allocations (user_id, room_id, status, allocated_at) VALUES (?,?,'active',NOW())");
    mysqli_stmt_bind_param($stmt, "ii", $student_id, $room_id);
    mysqli_stmt_execute($stmt);
    $stmt2 = mysqli_prepare($conn, "UPDATE rooms SET current_occupancy = current_occupancy + 1 WHERE id=?");
    mysqli_stmt_bind_param($stmt2, "i", $room_id);
    mysqli_stmt_execute($stmt2);
    header("Location: assign_rooms.php");
    exit();
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$student_query = "SELECT id, CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name FROM users WHERE role='student'";
if(!empty($search)){
    $student_query .= " AND CONCAT(first_name,' ',COALESCE(last_name,'')) LIKE '%$search%'";
}
$student_query .= " ORDER BY first_name";
$students = mysqli_query($conn,$student_query);
$rooms = mysqli_query($conn,"SELECT * FROM rooms");
include("../header.php");
?>
<div class="container mt-4">
<h3>Assign Rooms</h3>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search Student"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<form method="POST" class="row g-3">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="col-md-6">
<select name="student_id" class="form-select" required>
<option value="">Select Student</option>
<?php while($s = mysqli_fetch_assoc($students)){ ?>
<option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
<?php } ?>
</select>
</div>
<div class="col-md-6">
<select name="room_id" class="form-select" required>
<option value="">Select Room</option>
<?php while($r = mysqli_fetch_assoc($rooms)){ ?>
<option value="<?= $r['id'] ?>">
Room <?= htmlspecialchars($r['room_number']) ?>
(<?= intval($r['current_occupancy']) ?>/<?= intval($r['capacity']) ?>)
</option>
<?php } ?>
</select>
</div>
<div class="col-md-12">
<button type="submit" class="btn btn-primary">Assign Room</button>
</div>
</form>
</div>
<?php include("../footer.php"); ?>