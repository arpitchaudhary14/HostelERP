<?php
include("../session_check.php");
include("../db.php");
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM notices";
if(!empty($search)){
    $query .= " WHERE title LIKE '%$search%'";
}
$query .= " ORDER BY created_at DESC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4">
<h4>Notice Board</h4>
<hr>
<form method="GET">
<div class="row mb-3">
<div class="col-md-6">
<input type="text" name="search" class="form-control"
placeholder="Search by Title"
value="<?= htmlspecialchars($search) ?>">
</div>
<div class="col-md-2">
<button class="btn btn-primary">Search</button>
</div>
</div>
</form>
<?php if(mysqli_num_rows($result) == 0){ ?>
<div class="alert alert-info">No notices found.</div>
<?php } ?>
<?php while($row = mysqli_fetch_assoc($result)){ ?>
<div class="card mb-3 shadow-sm">
<div class="card-body">
<div class="d-flex justify-content-between">
<small class="text-muted"><?= $row['created_at'] ?></small>
</div>
<h5 class="mt-2"><?= htmlspecialchars($row['title']) ?></h5>
<p><?= htmlspecialchars($row['message']) ?></p>
</div>
</div>
<?php } ?>
</div>
<?php include("../footer.php"); ?>