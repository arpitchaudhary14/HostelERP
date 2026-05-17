<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit();
}
$check_rooms = mysqli_query($conn, "SHOW COLUMNS FROM rooms LIKE 'room_type'");
if(mysqli_num_rows($check_rooms) == 0) {
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN room_type VARCHAR(20) DEFAULT '2-Seater'");
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN comfort_tier VARCHAR(20) DEFAULT 'AC'");
    mysqli_query($conn, "UPDATE rooms SET room_type = IF(id % 2 = 0, '3-Seater', '2-Seater'), comfort_tier = IF(id % 3 = 0, 'AC', IF(id % 3 = 1, 'Air-Cooled', 'Non-AC'))");
}
$success = "";
$error = "";
if(isset($_POST['add_room'])){
    validate_csrf();
    $room_number = trim($_POST['room_number'] ?? '');
    $room_config = $_POST['room_config'] ?? '2-Seater|Normal';
    $config_parts = explode('|', $room_config);
    $room_type = $config_parts[0] ?? '2-Seater';
    $comfort_tier = $config_parts[1] ?? 'Normal';
    $capacity = ($room_type == '3-Seater') ? 3 : 2;
    if($room_number) {
        $dup = mysqli_query($conn, "SELECT id FROM rooms WHERE room_number = '" . mysqli_real_escape_string($conn, $room_number) . "'");
        if(mysqli_num_rows($dup) > 0) {
            $error = "Room number '$room_number' already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO rooms (room_number, capacity, current_occupancy, room_type, comfort_tier) VALUES (?, ?, 0, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siss", $room_number, $capacity, $room_type, $comfort_tier);
            mysqli_stmt_execute($stmt);
            $success = "Room '$room_number' added successfully.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
if(isset($_POST['delete_room'])){
    validate_csrf();
    $id = intval($_POST['room_id']);
    mysqli_query($conn,"DELETE FROM rooms WHERE id='$id'");
    $success = "Room deleted successfully.";
}
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM rooms";
if(!empty($search)){
    $query .= " WHERE room_number LIKE '%$search%'";
}
$query .= " ORDER BY room_number ASC";
$result = mysqli_query($conn,$query);
include("../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl); max-width: 950px; margin: 0 auto;">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-shield-lock text-primary me-2"></i> Admin Control: Room Configurations</h3>
        </div>
        <p class="text-muted" style="margin:0;">Global administration desk to manage physical room parameters, tiers, and inventory.</p>
    </div>
    <div class="glass-card-light reveal" style="max-width:950px; margin:0 auto; padding: var(--space-xl);">
        <?php if($success) echo "<div class='alert alert-success'><i class='bi bi-check-circle-fill me-2'></i> $success</div>"; ?>
        <?php if($error)   echo "<div class='alert alert-danger'><i class='bi bi-exclamation-octagon-fill me-2'></i> $error</div>"; ?>
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6">
                <form method="GET" class="input-group">
                    <span class="input-group-text bg-dark border-secondary text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-dark border-secondary text-white" 
                           placeholder="Search Room Number (e.g. 101)..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary fw-bold" type="submit">Search Room</button>
                </form>
            </div>
            <div class="col-md-6 text-md-end text-muted small">
                Total Rooms Configured: <span class="fw-bold text-white fs-6"><?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rooms")) ?></span>
            </div>
        </div>
        <form method="POST" class="row g-3 mb-4 align-items-end border-top border-secondary border-opacity-25 pt-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Room Number</label>
                <input type="text" name="room_number" class="form-control bg-dark border-secondary text-white" placeholder="e.g. 201" required>
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
                <button type="submit" class="btn btn-primary w-100 fw-bold" name="add_room"><i class="bi bi-plus-circle me-1"></i> Add Configured Room</button>
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
                        <th>Current Occupancy</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td class="fw-bold text-white fs-6">Room <?= htmlspecialchars($row['room_number']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['room_type'] ?? '2-Seater') ?></span></td>
                            <td>
                                <?php if(($row['comfort_tier'] ?? '') == 'AC'): ?>
                                    <span class="badge bg-info text-dark">AC</span>
                                <?php elseif(($row['comfort_tier'] ?? '') == 'Air-Cooled'): ?>
                                    <span class="badge bg-warning text-dark">Air-Cooled</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= intval($row['capacity']) ?></td>
                            <td><?= intval($row['current_occupancy']) ?></td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Permanently delete Room <?= htmlspecialchars($row['room_number']) ?>?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                                    <input type="hidden" name="room_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="action" value="delete_room">
                                    <button type="submit" name="delete_room" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr><td colspan="6" class="text-center text-muted">No rooms matching the search criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>