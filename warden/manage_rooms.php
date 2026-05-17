<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}
$check_rooms = mysqli_query($conn, "SHOW COLUMNS FROM rooms LIKE 'room_type'");
if(mysqli_num_rows($check_rooms) == 0) {
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN room_type VARCHAR(20) DEFAULT '2-Seater'");
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN comfort_tier VARCHAR(20) DEFAULT 'AC'");
    mysqli_query($conn, "UPDATE rooms SET room_type = IF(id % 2 = 0, '3-Seater', '2-Seater'), comfort_tier = IF(id % 3 = 0, 'AC', IF(id % 3 = 1, 'Air-Cooled', 'Non-AC'))");
}
$success = "";
$error = "";
if(isset($_POST['add'])){
    validate_csrf();
    $room_number = trim($_POST['room_number']);
    $room_config = $_POST['room_config'] ?? '2-Seater|Normal';
    $config_parts = explode('|', $room_config);
    $room_type = $config_parts[0] ?? '2-Seater';
    $comfort_tier = $config_parts[1] ?? 'Normal';
    $capacity = ($room_type == '3-Seater') ? 3 : 2;
    if($room_number){
        $dup = mysqli_query($conn, "SELECT id FROM rooms WHERE room_number = '" . mysqli_real_escape_string($conn, $room_number) . "'");
        if(mysqli_num_rows($dup) > 0) {
            $error = "Room number '$room_number' already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO rooms (room_number, capacity, current_occupancy, room_type, comfort_tier) VALUES (?, ?, 0, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siss", $room_number, $capacity, $room_type, $comfort_tier);
            mysqli_stmt_execute($stmt);
            $success = "Room '$room_number' ($room_type - $comfort_tier) added successfully.";
        }
    } else {
        $error = "Please fill in room number.";
    }
}
if(isset($_POST['delete_room'])) {
    validate_csrf();
    $del_id = intval($_POST['room_id']);
    mysqli_query($conn, "DELETE FROM rooms WHERE id = $del_id");
    $success = "Room deleted successfully.";
}
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_number");
$rooms = [];
while($row = mysqli_fetch_assoc($result)){
    $rooms[] = $row;
}
include("../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl); max-width: 900px; margin: 0 auto;">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-door-closed text-primary me-2"></i> Manage Rooms</h3>
        </div>
        <p class="text-muted" style="margin:0;">Configure room tiers, seating capacities, comfort levels, and oversee overall occupancy.</p>
    </div>
    <div class="glass-card-light reveal" style="max-width:900px; margin:0 auto; padding: var(--space-xl);">
        <?php if($success) echo "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i> $success</div>"; ?>
        <?php if($error)   echo "<div class='alert alert-danger'><i class='bi bi-exclamation-octagon-fill me-2'></i> $error</div>"; ?>
        <form method="POST" class="row g-3 mb-4 align-items-end">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Room Number</label>
                <input name="room_number" placeholder="e.g. 101, A-12" class="form-control bg-dark border-secondary text-white" required>
            </div>
            <div class="col-md-5">
                <label class="form-label text-muted small fw-semibold">Room Configuration (Type & Comfort)</label>
                <select name="room_config" class="form-select bg-dark border-secondary text-white" required>
                    <option value="2-Seater|AC">2-Seater (AC)</option>
                    <option value="2-Seater|Air-Cooled">2-Seater (Air-Cooled)</option>
                    <option value="2-Seater|Normal" selected>2-Seater (Normal)</option>
                    <option value="3-Seater|AC">3-Seater (AC)</option>
                    <option value="3-Seater|Air-Cooled">3-Seater (Air-Cooled)</option>
                    <option value="3-Seater|Normal">3-Seater (Normal)</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" name="add" class="btn btn-primary w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Configured Room</button>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle small mb-0">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Comfort Tier</th>
                        <th>Capacity</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rooms as $r): ?>
                        <tr>
                            <td class="fw-bold text-white fs-6">Room <?= htmlspecialchars($r['room_number']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['room_type'] ?? '2-Seater') ?></span></td>
                            <td>
                                <?php if(($r['comfort_tier'] ?? '') == 'AC'): ?>
                                    <span class="badge bg-info text-dark">AC</span>
                                <?php elseif(($r['comfort_tier'] ?? '') == 'Air-Cooled'): ?>
                                    <span class="badge bg-warning text-dark">Air-Cooled</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= intval($r['capacity']) ?></td>
                            <td><?= intval($r['current_occupancy']) ?></td>
                            <td>
                                <?php 
                                    $av = intval($r['capacity']) - intval($r['current_occupancy']);
                                    if($av <= 0) {
                                        echo "<span class='badge bg-danger'>Full</span>";
                                    } else {
                                        echo "<span class='badge bg-success'>$av Free</span>";
                                    }
                                ?>
                            </td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete Room <?= htmlspecialchars($r['room_number']) ?>? All active allocations in this room will be cleared.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                                    <input type="hidden" name="action" value="delete_room">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($rooms)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No rooms added yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>