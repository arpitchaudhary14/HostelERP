<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn,
    "SELECT r.room_number, r.capacity, r.current_occupancy, ra.allocated_at
     FROM room_allocations ra
     JOIN rooms r ON ra.room_id = r.id
     WHERE ra.user_id = ? AND ra.status = 'active'
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$room = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
include("../header.php");
?>
<div class="container mt-4" style="max-width:700px;">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">My Room Details</h3>
<?php if($room): ?>
    <div class="glass-card-light">
        <h5 style="font-weight:600; color:#1a1a2e;">Room Number: <?= htmlspecialchars($room['room_number']) ?></h5>
        <p><strong>Allocated On:</strong> <?= htmlspecialchars($room['allocated_at'] ?? '—') ?></p>
        <p><strong>Capacity:</strong> <?= intval($room['capacity']) ?></p>
        <p><strong>Current Occupancy:</strong> <?= intval($room['current_occupancy']) ?></p>
        <?php
            $percent = ($room['capacity'] > 0)
                ? ($room['current_occupancy'] / $room['capacity']) * 100
                : 0;
        ?>
        <div class="progress mt-3">
            <div class="progress-bar bg-info" style="width: <?= round($percent) ?>%">
                <?= round($percent) ?>% Occupied
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning">
        No room allocated yet. Please contact your warden.
    </div>
<?php endif; ?>
</div>
<?php include("../footer.php"); ?>