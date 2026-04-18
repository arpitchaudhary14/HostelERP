<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$parcels = mysqli_query($conn, "SELECT p.*, s.first_name as s_fn, s.last_name as s_ln, w.first_name as w_fn, w.last_name as w_ln FROM parcels p JOIN users s ON p.student_id = s.id LEFT JOIN users w ON p.received_by_warden_id = w.id ORDER BY p.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
        <h4 style="font-weight:700; color:#1a1a2e;">Global Parcel Records</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date Arrived</th>
                        <th>Student</th>
                        <th>Sender Name</th>
                        <th>Tracking Number</th>
                        <th>Status</th>
                        <th>Received by Warden</th>
                        <th>Delivered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($parcels)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['created_at']) ?></td>
                        <td><?= htmlspecialchars($row['s_fn'] . ' ' . $row['s_ln']) ?></td>
                        <td><?= htmlspecialchars($row['sender_name']) ?></td>
                        <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Delivered' ? 'success' : 'primary' ?>"><?= $row['status'] ?></span></td>
                        <td><?= htmlspecialchars($row['w_fn'] . ' ' . $row['w_ln']) ?></td>
                        <td><?= $row['delivered_at'] ? htmlspecialchars($row['delivered_at']) : '--' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($parcels) == 0) echo "<tr><td colspan='7' class='text-center'>No parcel records found.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>