<?php
include("../session_check.php");
include("../db.php");

if($_SESSION['role'] != 'admin'){ 
    header("Location: ../dashboard.php"); 
    exit();
}

$admin_id = intval($_SESSION['user_id']);

$check_rooms = mysqli_query($conn, "SHOW COLUMNS FROM rooms LIKE 'room_type'");
if(mysqli_num_rows($check_rooms) == 0) { 
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN room_type VARCHAR(20) DEFAULT '2-Seater'"); 
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN comfort_tier VARCHAR(20) DEFAULT 'AC'"); 
    mysqli_query($conn, "UPDATE rooms SET room_type = IF(id % 2 = 0, '3-Seater', '2-Seater'), comfort_tier = IF(id % 3 = 0, 'AC', IF(id % 3 = 1, 'Air-Cooled', 'Non-AC'))");
}

$success = "";
$error = "";

// ADD ROOM (Direct Admin Capability)
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

// APPROVE DECOMMISSION PETITION (SUPREME AUTHORIZATION)
if(isset($_POST['action']) && $_POST['action'] === 'approve_decommission') {
    validate_csrf();
    $request_id = intval($_POST['request_id'] ?? 0);
    $admin_remarks = trim($_POST['admin_remarks'] ?? 'Approved by Supreme Admin');

    $req_res = mysqli_query($conn, "SELECT * FROM room_decommission_requests WHERE id = $request_id AND status = 'pending' LIMIT 1");
    if($req = mysqli_fetch_assoc($req_res)) {
        $room_id = intval($req['room_id']);
        $room_num = $req['room_number'];
        $warden_id = intval($req['warden_id']);

        // Fetch living residents before unallocation
        $alloc_stmt = mysqli_prepare($conn, "
            SELECT u.id, u.full_name, u.first_name, u.last_name, u.email
            FROM room_allocations ra
            JOIN users u ON ra.user_id = u.id
            WHERE ra.room_id = ? AND ra.status = 'active'
        ");
        mysqli_stmt_bind_param($alloc_stmt, "i", $room_id);
        mysqli_stmt_execute($alloc_stmt);
        $alloc_res = mysqli_stmt_get_result($alloc_stmt);

        $evicted_students = [];
        $evicted_ids = [];
        while($st = mysqli_fetch_assoc($alloc_res)) {
            $name = trim($st['full_name'] ?? '');
            if(empty($name)) $name = trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
            if(empty($name)) $name = $st['email'];
            $evicted_students[] = $name;
            $evicted_ids[] = intval($st['id']);
        }

        // 1. Unallocate residents
        mysqli_query($conn, "DELETE FROM room_allocations WHERE room_id = $room_id");

        // 2. Cancel pending swaps
        if(!empty($evicted_ids)) {
            $id_list = implode(',', $evicted_ids);
            @mysqli_query($conn, "UPDATE room_swaps SET status = 'cancelled' WHERE (sender_id IN ($id_list) OR receiver_id IN ($id_list)) AND status IN ('pending', 'accepted_by_receiver')");
        }

        // 3. Delete the physical room
        mysqli_query($conn, "DELETE FROM rooms WHERE id = $room_id");

        // 4. Update decommission petition status
        $upd_stmt = mysqli_prepare($conn, "UPDATE room_decommission_requests SET status = 'approved', admin_id = ?, admin_remarks = ?, reviewed_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($upd_stmt, "isi", $admin_id, $admin_remarks, $request_id);
        mysqli_stmt_execute($upd_stmt);

        // 5. Notify Warden
        $w_title = "Petition Approved: Room $room_num Decommissioned";
        $w_msg = "Your petition to decommission Room $room_num has been authorized by Supreme Admin. Active residents have been transitioned to the priority pool. Remarks: $admin_remarks";
        mysqli_query($conn, "INSERT INTO notifications (user_id, sender_id, target_role, title, message, is_read) VALUES ($warden_id, $admin_id, 'warden', '" . mysqli_real_escape_string($conn, $w_title) . "', '" . mysqli_real_escape_string($conn, $w_msg) . "', 0)");

        // 6. Notify each student with Guaranteed 5-Day SLA & Refund Guarantee
        $s_title = "Important Notice: Room $room_num Decommissioned [5-Day SLA Guarantee]";
        $s_msg = "Your allotted Room $room_num has been officially decommissioned by Supreme Admin authorization following structural/facility review. Your allotment has been moved to the Priority Re-allocation Pool.\n\n🛡️ GUARANTEED 5-DAY SLA: You are entitled to an equivalent room allotment or 100% proportional fee refund within 5 Business Days. If you do not receive a replacement room or refund within this window, please submit an escalated claim directly to the Supreme Admin via the Housing Portal for immediate redressal.";
        foreach($evicted_ids as $sid) {
            $n_stmt = mysqli_prepare($conn, "INSERT INTO notifications (user_id, sender_id, target_role, title, message, is_read) VALUES (?, ?, 'student', ?, ?, 0)");
            if($n_stmt) {
                mysqli_stmt_bind_param($n_stmt, "iiss", $sid, $admin_id, $s_title, $s_msg);
                mysqli_stmt_execute($n_stmt);
            }
        }

        $_SESSION['action_type'] = 'admin_room_decommission_executed';
        header("Location: ../thank_you.php");
        exit();
    } else {
        $error = "Petition not found or already processed.";
    }
}

// REJECT DECOMMISSION PETITION
if(isset($_POST['action']) && $_POST['action'] === 'reject_decommission') {
    validate_csrf();
    $request_id = intval($_POST['request_id'] ?? 0);
    $admin_remarks = trim($_POST['admin_remarks'] ?? 'Petition declined. Ground deemed insufficient.');

    $req_res = mysqli_query($conn, "SELECT * FROM room_decommission_requests WHERE id = $request_id AND status = 'pending' LIMIT 1");
    if($req = mysqli_fetch_assoc($req_res)) {
        $room_num = $req['room_number'];
        $warden_id = intval($req['warden_id']);

        $upd_stmt = mysqli_prepare($conn, "UPDATE room_decommission_requests SET status = 'rejected', admin_id = ?, admin_remarks = ?, reviewed_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($upd_stmt, "isi", $admin_id, $admin_remarks, $request_id);
        mysqli_stmt_execute($upd_stmt);

        // Notify Warden of rejection
        $w_title = "Petition Rejected: Room $room_num Closure Denied";
        $w_msg = "Your petition to decommission Room $room_num was REJECTED by Supreme Admin. Room and residents remain active. Remarks: $admin_remarks";
        mysqli_query($conn, "INSERT INTO notifications (user_id, sender_id, target_role, title, message, is_read) VALUES ($warden_id, $admin_id, 'warden', '" . mysqli_real_escape_string($conn, $w_title) . "', '" . mysqli_real_escape_string($conn, $w_msg) . "', 0)");

        $success = "Decommission petition for Room '$room_num' was rejected. The room and its residents remain fully protected.";
    } else {
        $error = "Petition not found or already processed.";
    }
}

// Fetch pending decommission petitions
$pending_petitions = [];
try {
    $petitions_query = "
        SELECT rdr.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as warden_name, u.email as warden_email
        FROM room_decommission_requests rdr
        JOIN users u ON rdr.warden_id = u.id
        WHERE rdr.status = 'pending'
        ORDER BY rdr.created_at ASC
    ";
    $pending_petitions_res = mysqli_query($conn, $petitions_query);
    if($pending_petitions_res) {
        while($p = mysqli_fetch_assoc($pending_petitions_res)) {
            $pending_petitions[] = $p;
        }
    }
} catch (Throwable $e) {
    error_log("Admin pending petitions query error: " . $e->getMessage());
}

// Fetch rooms
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$query = "SELECT * FROM rooms";
if(!empty($search)){ 
    $query .= " WHERE room_number LIKE '%$search%'";
}
$query .= " ORDER BY room_number ASC";
$result = mysqli_query($conn, $query);

$pageTitle = 'Manage Rooms (Admin) - HostelERP';
$pageDesc = 'Supreme Administration desk for room configurations and decommission authorizations.';
include("../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl); max-width: 980px; margin: 0 auto;">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-shield-lock text-primary me-2"></i> Supreme Authority: Room Decommission Desk</h3>
        </div>
        <p class="text-muted" style="margin:0;">Dual-control institutional authority to audit Warden petitions, approve closures, and manage global room inventory.</p>
    </div>

    <div class="glass-card-light reveal mb-4" style="max-width:980px; margin:0 auto; padding: var(--space-xl);">
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-octagon-fill me-2"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- SUPREME PETITIONS REVIEW PANEL -->
        <div class="card border-0 mb-4" style="background: rgba(239, 68, 68, 0.05); border-radius: var(--radius-md); border-left: 5px solid var(--accent-danger) !important;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-danger">
                        <i class="bi bi-shield-exclamation me-2"></i> Pending Decommission Petitions Awaiting Supreme Authorization
                    </h5>
                    <span class="badge bg-danger fs-6"><?= count($pending_petitions) ?> Action Required</span>
                </div>

                <?php if(empty($pending_petitions)): ?>
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                        <span class="small">All clear. No pending room closure petitions from Wardens.</span>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0 table-sticky">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Petitioner (Warden)</th>
                                    <th>Affected Residents</th>
                                    <th>Warden's Justification</th>
                                    <th>Supreme Decision</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pending_petitions as $pet): ?>
                                    <?php 
                                    $aff = json_decode($pet['affected_students'] ?? '[]', true); 
                                    $aff_count = is_array($aff) ? count($aff) : 0;
                                    ?>
                                    <tr>
                                        <td class="fw-bold fs-6">Room <?= htmlspecialchars($pet['room_number']) ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($pet['warden_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars(date('M d, H:i', strtotime($pet['created_at']))) ?></small>
                                        </td>
                                        <td>
                                            <?php if($aff_count == 0): ?>
                                                <span class="badge bg-secondary">Unoccupied (0)</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger mb-1"><?= $aff_count ?> Student(s)</span><br>
                                                <small class="text-muted">
                                                    <?php 
                                                    $names = array_column($aff, 'name');
                                                    echo htmlspecialchars(implode(', ', array_slice($names, 0, 2)));
                                                    if(count($names) > 2) echo " +" . (count($names) - 2) . " more";
                                                    ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div style="max-width: 260px; line-height: 1.4;" class="text-muted">
                                                <?= htmlspecialchars($pet['reason']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <!-- APPROVE BUTTON (TRIGGER MODAL) -->
                                                <button type="button" class="btn btn-xs btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#approvePetModal_<?= $pet['id'] ?>">
                                                    <i class="bi bi-check-lg me-1"></i> Authorize
                                                </button>
                                                <!-- REJECT BUTTON (TRIGGER MODAL) -->
                                                <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#rejectPetModal_<?= $pet['id'] ?>">
                                                    <i class="bi bi-x-lg me-1"></i> Decline
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- APPROVAL MODAL -->
                                    <div class="modal fade" id="approvePetModal_<?= $pet['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content glass-card-light" style="border: 2px solid var(--accent-danger);">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title fw-bold text-danger">Authorize Room <?= htmlspecialchars($pet['room_number']) ?> Closure</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body pt-0">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="approve_decommission">
                                                        <input type="hidden" name="request_id" value="<?= $pet['id'] ?>">

                                                        <div class="alert alert-warning py-2 px-3 small mb-3">
                                                            <i class="bi bi-shield-fill-exclamation me-1"></i>
                                                            Confirming this supreme order will delete Room <strong><?= htmlspecialchars($pet['room_number']) ?></strong>, unallocate <?= $aff_count ?> student(s), and automatically dispatch the <strong>5-Day SLA Refund & Re-allocation Guarantee</strong> to affected residents.
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-muted">Supreme Admin Executive Remarks</label>
                                                            <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Approved. Decommission authorized for safety renovation. Students prioritized."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Confirm & Execute Decommission
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- REJECTION MODAL -->
                                    <div class="modal fade" id="rejectPetModal_<?= $pet['id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content glass-card-light">
                                                <div class="modal-header border-0">
                                                    <h5 class="modal-title fw-bold">Decline Decommission Petition</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body pt-0">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="reject_decommission">
                                                        <input type="hidden" name="request_id" value="<?= $pet['id'] ?>">

                                                        <p class="small text-muted mb-3">
                                                            Room <strong><?= htmlspecialchars($pet['room_number']) ?></strong> and all its residents will remain protected. Please provide a reason to the Warden.
                                                        </p>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold text-muted">Administrative Rejection Feedback <span class="text-danger">*</span></label>
                                                            <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Disapproved. Alternative repair scheduled without tenant relocation." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0 pt-0">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                                                            <i class="bi bi-x-circle me-1"></i> Decline Petition
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- GLOBAL ROOM INVENTORY & ADD FORM -->
        <div class="row g-4 mb-4 align-items-center">
            <div class="col-md-6">
                <form method="GET" class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search Room Number (e.g. 101)..." value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-primary fw-bold" type="submit">Search Room</button>
                </form>
            </div>
            <div class="col-md-6 text-md-end text-muted small">
                Total Global Rooms: <span class="fw-bold fs-6" style="color:var(--inner-heading);"><?= mysqli_num_rows(mysqli_query($conn, "SELECT id FROM rooms")) ?></span>
            </div>
        </div>

        <form method="POST" class="row g-3 mb-4 align-items-end border-top border-secondary border-opacity-25 pt-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Room Number</label>
                <input type="text" name="room_number" class="form-control" placeholder="e.g. 201" required>
            </div>
            <div class="col-md-5">
                <label class="form-label text-muted small fw-semibold">Room Configuration (Type & Comfort)</label>
                <select name="room_config" class="form-select" required>
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

        <div class="table-responsive" style="max-height: 500px;">
            <table class="table table-hover align-middle small mb-0 table-sticky">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Comfort Tier</th>
                        <th>Capacity</th>
                        <th>Current Occupancy</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)){ ?>
                        <?php
                        $chk_p = mysqli_query($conn, "SELECT id FROM room_decommission_requests WHERE room_id = {$row['id']} AND status = 'pending'");
                        $is_p = mysqli_num_rows($chk_p) > 0;
                        ?>
                        <tr>
                            <td class="fw-bold fs-6">Room <?= htmlspecialchars($row['room_number']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['room_type'] ?? '2-Seater') ?></span></td>
                            <td>
                                <?php if(($row['comfort_tier'] ?? '') == 'AC'): ?>
                                    <span class="badge bg-info text-dark">AC</span>
                                <?php elseif(($row['comfort_tier'] ?? '') == 'Air-Cooled'): ?>
                                    <span class="badge bg-warning text-dark">Air-Cooled</span>
                                <?php else: ?>
                                    <span class="badge">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td><?= intval($row['capacity']) ?></td>
                            <td><?= intval($row['current_occupancy']) ?></td>
                            <td>
                                <?php if($is_p): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Petition Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                        <tr>
                            <td colspan="6">
                                <div class="table-empty">
                                    <i class="bi bi-inbox"></i>
                                    <p>No rooms matching the search criteria.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>