<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$msg = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $visit_id = intval($_POST['visit_id']);
    $action = $_POST['action'];   
    if($action == "approve") {
        mysqli_query($conn, "UPDATE visitors SET status='Approved' WHERE id=$visit_id");
    } elseif($action == "reject") {
        mysqli_query($conn, "UPDATE visitors SET status='Rejected' WHERE id=$visit_id");
    } elseif($action == "mark_in") {
        mysqli_query($conn, "UPDATE visitors SET in_time=CURRENT_TIME() WHERE id=$visit_id");
    } elseif($action == "mark_out") {
        mysqli_query($conn, "UPDATE visitors SET out_time=CURRENT_TIME() WHERE id=$visit_id");
    }
    header("Location: visitors.php");
    exit();
}
$visits = mysqli_query($conn, "SELECT v.*, u.first_name, u.last_name FROM visitors v JOIN users u ON v.student_id = u.id ORDER BY v.visit_date DESC, v.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal">
        <h4 style="font-weight:700; color:#1a1a2e;">Manage Visitors</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Visitor</th>
                        <th>Relation & Purpose</th>
                        <th>Status</th>
                        <th>In / Out Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($visits)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['visit_date']) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                        <td><?= htmlspecialchars($row['relation']) ?> - <?= htmlspecialchars($row['purpose']) ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Rejected' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                        <td><?= $row['in_time'] ? $row['in_time'] : '--' ?> / <?= $row['out_time'] ? $row['out_time'] : '--' ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="visit_id" value="<?= $row['id'] ?>">
                                <?php if($row['status'] == 'Pending'): ?>
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                <?php endif; ?>
                                <?php if($row['status'] == 'Approved' && !$row['in_time']): ?>
                                    <button type="submit" name="action" value="mark_in" class="btn btn-sm btn-primary">Mark In</button>
                                <?php endif; ?>                          
                                <?php if($row['in_time'] && !$row['out_time']): ?>
                                    <button type="submit" name="action" value="mark_out" class="btn btn-sm btn-secondary">Mark Out</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($visits) == 0) echo "<tr><td colspan='7' class='text-center'>No visitor records.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>