<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$visits = mysqli_query($conn, "SELECT v.*, u.first_name, u.last_name FROM visitors v JOIN users u ON v.student_id = u.id ORDER BY v.visit_date DESC, v.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal">
        <h4 style="font-weight:700; color:#1a1a2e;">Global Visitor Records</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Visitor</th>
                        <th>Relation</th>
                        <th>Purpose</th>
                        <th>Status</th>
                        <th>In / Out Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($visits)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['visit_date']) ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['visitor_name']) ?></td>
                        <td><?= htmlspecialchars($row['relation']) ?></td>
                        <td><?= htmlspecialchars($row['purpose']) ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Approved' ? 'success' : ($row['status'] == 'Rejected' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                        <td><?= $row['in_time'] ? $row['in_time'] : '--' ?> / <?= $row['out_time'] ? $row['out_time'] : '--' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($visits) == 0) echo "<tr><td colspan='7' class='text-center'>No visitor records found.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>