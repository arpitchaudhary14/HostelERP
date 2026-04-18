<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = "";
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['visitor_name'])) {
    $vname = mysqli_real_escape_string($conn, $_POST['visitor_name']);
    $relation = mysqli_real_escape_string($conn, $_POST['relation']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);
    $vdate = mysqli_real_escape_string($conn, $_POST['visit_date']);   
    $stmt = mysqli_prepare($conn, "INSERT INTO visitors (student_id, visitor_name, relation, purpose, visit_date) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $vname, $relation, $purpose, $vdate);
    if(mysqli_stmt_execute($stmt)){
         $msg = "Visitor request submitted successfully!";
    } else {
         $error = "Failed to submit request.";
    }
}
$visits = mysqli_query($conn, "SELECT * FROM visitors WHERE student_id = '$user_id' ORDER BY created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card-light p-4 reveal">
                <h4 style="font-weight:700; color:#1a1a2e;">New Visitor Request</h4>
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Visitor Name</label>
                        <input type="text" name="visitor_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Relation</label>
                        <input type="text" name="relation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Purpose</label>
                        <textarea name="purpose" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Visit Date</label>
                        <input type="date" name="visit_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit Request</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
                <h4 style="font-weight:700; color:#1a1a2e;">My Visitors</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Relation</th>
                                <th>Status</th>
                                <th>In/Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($visits)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['visit_date']) ?></td>
                                <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                                <td><?= htmlspecialchars($row['relation']) ?></td>
                                <td><span class="badge bg-<?= $row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Rejected' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                                <td><?= $row['in_time'] ? $row['in_time'] : '--' ?> / <?= $row['out_time'] ? $row['out_time'] : '--' ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($visits) == 0) echo "<tr><td colspan='5' class='text-center'>No visitor records found.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>