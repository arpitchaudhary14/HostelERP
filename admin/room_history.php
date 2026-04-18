<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$query = "
SELECT room_history.*, 
users.first_name, users.last_name,
rooms.room_number
FROM room_history
JOIN users ON room_history.student_id = users.id
JOIN rooms ON room_history.room_id = rooms.id
ORDER BY room_history.assigned_on DESC
";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">Room Allocation History</h4>
<table class="table table-bordered">
<tr>
<th>Student</th>
<th>Room</th>
<th>Assigned On</th>
<th>Vacated On</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
<td><?php echo $row['room_number']; ?></td>
<td><?php echo $row['assigned_on']; ?></td>
<td><?php echo $row['vacated_on'] ?? "Currently Staying"; ?></td>
</tr>
<?php } ?>
</table>
</div>
</body>
</html>
<?php include("../footer.php"); ?>