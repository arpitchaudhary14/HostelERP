<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['assign_fee'])){
    validate_csrf();
    $student_id = intval($_POST['student_id'] ?? 0);
    $amount     = floatval($_POST['amount'] ?? 0);
    $due_date   = $_POST['due_date'] ?? '';
    $stmt = mysqli_prepare($conn, "INSERT INTO fees (student_id, amount, due_date, status) VALUES (?, ?, ?, 'Pending')");
    mysqli_stmt_bind_param($stmt, "ids", $student_id, $amount, $due_date);
    mysqli_stmt_execute($stmt);
}
if(isset($_GET['paid'])){
    $id = intval($_GET['paid']);
    mysqli_query($conn,"
        UPDATE fees SET status='Paid', paid_on=CURDATE()
        WHERE id='$id'
    ");
}
mysqli_query($conn,"
    UPDATE fees SET status='Overdue'
    WHERE due_date < CURDATE() AND status='Pending'
");
$students = mysqli_query($conn,"SELECT id, first_name, last_name FROM users WHERE role='student'");
$fees = mysqli_query($conn,"
    SELECT fees.*, users.first_name, users.last_name
    FROM fees
    JOIN users ON fees.student_id = users.id
    ORDER BY fees.created_at DESC
");
include("../header.php");
?>
<div class="container mt-4">
<h4>Assign Fee</h4>
<hr>
<form method="POST" class="row g-3 mb-4">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
<div class="col-md-4">
<select name="student_id" class="form-select" required>
<option value="">Select Student</option>
<?php while($s = mysqli_fetch_assoc($students)){ ?>
<option value="<?php echo $s['id']; ?>">
<?php echo htmlspecialchars($s['first_name']." ".$s['last_name']); ?>
</option>
<?php } ?>
</select>
</div>
<div class="col-md-3">
<input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
</div>
<div class="col-md-3">
<input type="date" name="due_date" class="form-control" required>
</div>
<div class="col-md-2">
<button type="submit" class="btn btn-primary" name="assign_fee">Assign</button>
</div>
</form>
<h4>All Fees</h4>
<hr>
<table class="table table-bordered">
<tr>
<th>Student</th>
<th>Amount</th>
<th>Due Date</th>
<th>Status</th>
<th>Action</th>
</tr>
<?php while($row = mysqli_fetch_assoc($fees)){ ?>
<tr>
<td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
<td>₹<?php echo $row['amount']; ?></td>
<td><?php echo $row['due_date']; ?></td>
<td>
<?php if($row['status']=="Paid"){ ?>
<span class="badge bg-success">Paid</span>
<?php } elseif($row['status']=="Overdue"){ ?>
<span class="badge bg-danger">Overdue</span>
<?php } else { ?>
<span class="badge bg-warning text-dark">Pending</span>
<?php } ?>
</td>
<td>
<?php if($row['status']!="Paid"){ ?>
<a href="?paid=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
Mark Paid
</a>
<?php } ?>
</td>
</tr>
<?php } ?>
</table>
</div>
<?php include("../footer.php"); ?>