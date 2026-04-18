<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$parcels = mysqli_query($conn, "SELECT p.*, u.first_name, u.last_name FROM parcels p LEFT JOIN users u ON p.received_by_warden_id = u.id WHERE p.student_id = '$user_id' ORDER BY p.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
        <h4 style="font-weight:700; color:#1a1a2e;">My Parcels</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date Arrived</th>
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
                        <td><?= htmlspecialchars($row['sender_name']) ?></td>
                        <td><?= htmlspecialchars($row['tracking_number']) ?></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Delivered' ? 'success' : 'primary' ?>"><?= $row['status'] ?></span></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= $row['delivered_at'] ? htmlspecialchars($row['delivered_at']) : '--' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($parcels) == 0) echo "<tr><td colspan='6' class='text-center'>No parcels found.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>