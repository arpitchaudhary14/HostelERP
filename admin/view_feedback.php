<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$query = "
SELECT feedback.*, CONCAT(users.first_name,' ',COALESCE(users.last_name,'')) as full_name
FROM feedback
JOIN users ON feedback.user_id = users.id
ORDER BY feedback.created_at DESC
";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>All Feedback</h4>
<hr>
<table class="table table-bordered">
<tr>
<th>User</th>
<th>Role</th>
<th>Type</th>
<th>Subject</th>
<th>Message</th>
<th>Rating</th>
<th>Date</th>
</tr>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<tr>
<td><?= htmlspecialchars($row['full_name'] ?? '—') ?></td>
<td><?= ucfirst($row['role']) ?></td>
<td><?= $row['type'] ?></td>
<td><?= $row['subject'] ?></td>
<td><?= $row['message'] ?></td>
<td><?= $row['rating'] ?? '-' ?></td>
<td><?= $row['created_at'] ?></td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>