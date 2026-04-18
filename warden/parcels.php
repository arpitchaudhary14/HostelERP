<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == "add") {
        $student_id = intval($_POST['student_id']);
        $sender = mysqli_real_escape_string($conn, $_POST['sender_name']);
        $tracking = mysqli_real_escape_string($conn, trim($_POST['tracking_number']));       
        $stmt = mysqli_prepare($conn, "INSERT INTO parcels (student_id, sender_name, tracking_number, received_by_warden_id) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issi", $student_id, $sender, $tracking, $user_id);
        if(mysqli_stmt_execute($stmt)){
             $msg = "Parcel logged successfully!";
        } else {
             $error = "Failed to log parcel.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == "deliver") {
        $parcel_id = intval($_POST['parcel_id']);
        mysqli_query($conn, "UPDATE parcels SET status='Delivered', delivered_at=NOW() WHERE id=$parcel_id");
        $msg = "Parcel marked as delivered.";
    }
}
$students = mysqli_query($conn, "SELECT id, first_name, last_name, username FROM users WHERE role='student' AND status='active'");
$parcels = mysqli_query($conn, "SELECT p.*, s.first_name as s_fn, s.last_name as s_ln, w.first_name as w_fn, w.last_name as w_ln FROM parcels p JOIN users s ON p.student_id = s.id LEFT JOIN users w ON p.received_by_warden_id = w.id ORDER BY p.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card-light p-4 reveal">
                <h4 style="font-weight:700; color:#1a1a2e;">Log New Parcel</h4>
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Student</label>
                        <select name="student_id" class="form-select" required>
                            <option value="">Select Student...</option>
                            <?php while($s = mysqli_fetch_assoc($students)): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['s_fn'].' '.$s['s_ln'].' ('.$s['username'].')') ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Sender Name</label>
                        <input type="text" name="sender_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Tracking Number (Optional)</label>
                        <input type="text" name="tracking_number" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Log Parcel</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
                <h4 style="font-weight:700; color:#1a1a2e;">Parcel Logs</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Arrived</th>
                                <th>Student</th>
                                <th>Sender</th>
                                <th>Tracking</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($parcels)): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, H:i', strtotime($row['created_at']))) ?></td>
                                <td><?= htmlspecialchars($row['s_fn'] . ' ' . $row['s_ln']) ?></td>
                                <td><?= htmlspecialchars($row['sender_name']) ?></td>
                                <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                                <td><span class="badge bg-<?= $row['status'] == 'Delivered' ? 'success' : 'primary' ?>"><?= $row['status'] ?></span></td>
                                <td>
                                    <?php if($row['status'] == 'Arrived'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="deliver">
                                        <input type="hidden" name="parcel_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Mark Delivered</button>
                                    </form>
                                    <?php else: ?>
                                    <small class="text-muted"><?= htmlspecialchars(date('M d, H:i', strtotime($row['delivered_at']))) ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($parcels) == 0) echo "<tr><td colspan='6' class='text-center'>No parcel records found.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>