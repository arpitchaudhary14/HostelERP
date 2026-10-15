<?php
include("../session_check.php");
include("../db.php");

if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}

$warden_id = intval($_SESSION['user_id']);

$check_rooms = mysqli_query($conn, "SHOW COLUMNS FROM rooms LIKE 'room_type'");
if(mysqli_num_rows($check_rooms) == 0) {
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN room_type VARCHAR(20) DEFAULT '2-Seater'");
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN comfort_tier VARCHAR(20) DEFAULT 'AC'");
    mysqli_query($conn, "UPDATE rooms SET room_type = IF(id % 2 = 0, '3-Seater', '2-Seater'), comfort_tier = IF(id % 3 = 0, 'AC', IF(id % 3 = 1, 'Air-Cooled', 'Non-AC'))");
}

$success = "";
$error = "";

// ADD ROOM
if(isset($_POST['add'])){
    validate_csrf();
    $room_number = trim($_POST['room_number'] ?? '');
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

// PETITION ROOM DECOMMISSION (MAKER-CHECKER WORKFLOW FOR SUPREME ADMIN APPROVAL)
if(isset($_POST['request_decommission']) || (isset($_POST['action']) && $_POST['action'] === 'request_decommission')) {
    validate_csrf();
    $room_id = intval($_POST['room_id'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $certify = isset($_POST['certify_warning']);

    if($room_id <= 0) {
        $error = "Invalid room selected for decommission.";
    } elseif(!$certify) {
        $error = "You must acknowledge the Institutional Audit Warning before submitting.";
    } elseif(strlen($reason) < 20) {
        $error = "Please provide an extensive, verifiable reason (minimum 20 characters) for institutional audit.";
    } else {
        // Fetch room info
        $r_stmt = mysqli_prepare($conn, "SELECT id, room_number FROM rooms WHERE id = ?");
        mysqli_stmt_bind_param($r_stmt, "i", $room_id);
        mysqli_stmt_execute($r_stmt);
        $room_res = mysqli_stmt_get_result($r_stmt);
        $room_data = mysqli_fetch_assoc($room_res);

        if(!$room_data) {
            $error = "Room not found or already decommissioned.";
        } else {
            $room_num = $room_data['room_number'];

            // Check if a pending petition already exists
            $pending_chk = mysqli_query($conn, "SELECT id FROM room_decommission_requests WHERE room_id = $room_id AND status = 'pending'");
            if(mysqli_num_rows($pending_chk) > 0) {
                $error = "A decommission petition for Room '$room_num' is already pending Supreme Admin review.";
            } else {
                // Fetch active residents
                $st_stmt = mysqli_prepare($conn, "
                    SELECT u.id, u.full_name, u.first_name, u.last_name, u.email
                    FROM room_allocations ra
                    JOIN users u ON ra.user_id = u.id
                    WHERE ra.room_id = ? AND ra.status = 'active'
                ");
                mysqli_stmt_bind_param($st_stmt, "i", $room_id);
                mysqli_stmt_execute($st_stmt);
                $st_res = mysqli_stmt_get_result($st_stmt);

                $affected = [];
                while($st = mysqli_fetch_assoc($st_res)) {
                    $name = trim($st['full_name'] ?? '');
                    if(empty($name)) $name = trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
                    if(empty($name)) $name = $st['email'];
                    $affected[] = [
                        'id' => intval($st['id']),
                        'name' => $name,
                        'email' => $st['email']
                    ];
                }
                $affected_json = json_encode($affected);

                // Insert decommission request
                $req_stmt = mysqli_prepare($conn, "
                    INSERT INTO room_decommission_requests (room_id, room_number, warden_id, reason, affected_students, status)
                    VALUES (?, ?, ?, ?, ?, 'pending')
                ");
                mysqli_stmt_bind_param($req_stmt, "isiss", $room_id, $room_num, $warden_id, $reason, $affected_json);

                if(mysqli_stmt_execute($req_stmt)) {
                    // Alert Admin
                    $admin_title = "Action Required: Room $room_num Decommission Petition";
                    $admin_msg = "Warden has submitted a formal petition to decommission Room $room_num. " . count($affected) . " resident(s) will be affected. Reason: $reason. Review on the Decommission Desk.";
                    mysqli_query($conn, "INSERT INTO notifications (sender_id, target_role, title, message, is_read) VALUES ($warden_id, 'admin', '" . mysqli_real_escape_string($conn, $admin_title) . "', '" . mysqli_real_escape_string($conn, $admin_msg) . "', 0)");

                    $_SESSION['action_type'] = 'warden_room_decommission_request';
                    header("Location: ../thank_you.php");
                    exit();
                } else {
                    $error = "Failed to record petition: " . mysqli_error($conn);
                }
            }
        }
    }
}

// Fetch all rooms
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_number");
$rooms = [];
while($row = mysqli_fetch_assoc($result)){
    $rooms[] = $row;
}

// Fetch pending decommission request IDs for fast lookup
$pending_reqs = [];
try {
    $p_res = mysqli_query($conn, "SELECT room_id FROM room_decommission_requests WHERE status = 'pending'");
    if($p_res) {
        while($pr = mysqli_fetch_assoc($p_res)) {
            $pending_reqs[$pr['room_id']] = true;
        }
    }
} catch (Throwable $e) {
    error_log("Pending reqs query error: " . $e->getMessage());
}

// Fetch warden's past petitions log
$my_petitions = [];
try {
    $pet_res = mysqli_query($conn, "SELECT * FROM room_decommission_requests WHERE warden_id = $warden_id ORDER BY created_at DESC LIMIT 10");
    if($pet_res) {
        while($prow = mysqli_fetch_assoc($pet_res)) {
            $my_petitions[] = $prow;
        }
    }
} catch (Throwable $e) {
    error_log("Petitions query error: " . $e->getMessage());
}

$pageTitle = 'Manage Rooms - HostelERP';
$pageDesc = 'Configure room tiers, oversee occupancy, and submit decommission petitions.';
include("../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl); max-width: 950px; margin: 0 auto;">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-door-closed text-primary me-2"></i> Manage Rooms</h3>
        </div>
        <p class="text-muted" style="margin:0;">Configure room tiers, seating capacities, comfort levels, and oversee overall occupancy.</p>
    </div>

    <div class="glass-card-light reveal mb-4" style="max-width:950px; margin:0 auto; padding: var(--space-xl);">
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

        <form method="POST" class="row g-3 mb-4 align-items-end">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-semibold">Room Number</label>
                <input name="room_number" placeholder="e.g. 101, A-12" class="form-control border-secondary" required>
            </div>
            <div class="col-12 col-md-5">
                <label class="form-label text-muted small fw-semibold">Room Configuration (Type & Comfort)</label>
                <select name="room_config" class="form-select border-secondary" required>
                    <option value="2-Seater|AC">2-Seater (AC)</option>
                    <option value="2-Seater|Air-Cooled">2-Seater (Air-Cooled)</option>
                    <option value="2-Seater|Normal" selected>2-Seater (Normal)</option>
                    <option value="3-Seater|AC">3-Seater (AC)</option>
                    <option value="3-Seater|Air-Cooled">3-Seater (Air-Cooled)</option>
                    <option value="3-Seater|Normal">3-Seater (Normal)</option>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <button type="submit" name="add" class="btn btn-primary w-100 fw-bold"><i class="bi bi-plus-circle me-1"></i> Add Configured Room</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0 table-sticky" style="min-width: 720px;">
                <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Comfort Tier</th>
                        <th>Capacity</th>
                        <th>Occupied</th>
                        <th>Available</th>
                        <th>Authorization Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($rooms as $r): ?>
                        <?php 
                        $is_pending = isset($pending_reqs[$r['id']]); 
                        // Fetch active students for modal display
                        $res_names = [];
                        $st_sub = mysqli_query($conn, "SELECT u.full_name, u.first_name, u.last_name FROM room_allocations ra JOIN users u ON ra.user_id = u.id WHERE ra.room_id = {$r['id']} AND ra.status = 'active'");
                        while($s_row = mysqli_fetch_assoc($st_sub)) {
                            $res_names[] = !empty(trim($s_row['full_name'] ?? '')) ? trim($s_row['full_name']) : trim(($s_row['first_name'] ?? '') . ' ' . ($s_row['last_name'] ?? ''));
                        }
                        ?>
                        <tr>
                            <td class="fw-bold fs-6">Room <?= htmlspecialchars($r['room_number']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['room_type'] ?? '2-Seater') ?></span></td>
                            <td>
                                <?php if(($r['comfort_tier'] ?? '') == 'AC'): ?>
                                    <span class="badge bg-info text-dark">AC</span>
                                <?php elseif(($r['comfort_tier'] ?? '') == 'Air-Cooled'): ?>
                                    <span class="badge bg-warning text-dark">Air-Cooled</span>
                                <?php else: ?>
                                    <span class="badge">Normal</span>
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
                                <?php if($is_pending): ?>
                                    <span class="badge bg-warning text-dark py-2 px-2">
                                        <i class="bi bi-hourglass-split me-1"></i> Admin Approval Pending
                                    </span>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#decomModal_<?= $r['id'] ?>">
                                        <i class="bi bi-shield-exclamation me-1"></i> Request Decommission
                                    </button>
                                <?php endif; ?>
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

    <!-- DECOMMISSION WARNING MODALS (Rendered cleanly outside table & cards to prevent backdrop z-index trapping) -->
    <?php foreach($rooms as $r): 
        $st_stmt = mysqli_prepare($conn, "
            SELECT u.full_name, u.first_name, u.last_name, u.email
            FROM room_allocations ra
            JOIN users u ON ra.user_id = u.id
            WHERE ra.room_id = ? AND ra.status = 'active'
        ");
        mysqli_stmt_bind_param($st_stmt, "i", $r['id']);
        mysqli_stmt_execute($st_stmt);
        $st_res = mysqli_stmt_get_result($st_stmt);
        $res_names = [];
        while($st = mysqli_fetch_assoc($st_res)) {
            $name = trim($st['full_name'] ?? '');
            if(empty($name)) $name = trim(($st['first_name'] ?? '') . ' ' . ($st['last_name'] ?? ''));
            if(empty($name)) $name = $st['email'];
            $res_names[] = $name;
        }
    ?>
        <div class="modal fade" id="decomModal_<?= $r['id'] ?>" tabindex="-1" aria-labelledby="decomModalLabel_<?= $r['id'] ?>" aria-hidden="true" style="z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg border border-danger border-opacity-50" style="background: var(--inner-card-bg, #1a2234); color: var(--inner-heading, #fff); border-radius: 16px;">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                        <h5 class="modal-title text-danger fw-bold" id="decomModalLabel_<?= $r['id'] ?>">
                            <i class="bi bi-shield-exclamation me-2"></i> Supreme Decommission Petition: Room <?= htmlspecialchars($r['room_number']) ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body pt-3">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="room_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="action" value="request_decommission">

                            <!-- SEVERE AUDIT WARNING ALERT -->
                            <div class="alert alert-danger border-start border-4 border-danger py-3 px-3 mb-3">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-exclamation-triangle-fill fs-4 me-2 text-danger"></i>
                                    <strong class="text-danger fs-6">INSTITUTIONAL AUDIT & LEGAL NOTICE</strong>
                                </div>
                                <p class="mb-0 small" style="line-height: 1.5;">
                                    Decommissioning a room immediately impacts student residency rights. <strong>If this petition is determined to be unnecessary, frivolous, retaliatory, or lacking verifiable structural/hazard grounds upon institutional audit, strict disciplinary proceedings and termination of Warden service will be initiated under hostel bylaws.</strong>
                                </p>
                            </div>

                            <div class="p-3 bg-secondary bg-opacity-10 rounded mb-3">
                                <div class="row small g-2">
                                    <div class="col-sm-6"><strong>Room:</strong> Room <?= htmlspecialchars($r['room_number']) ?> (<?= htmlspecialchars($r['room_type'] ?? '2-Seater') ?> - <?= htmlspecialchars($r['comfort_tier'] ?? 'Normal') ?>)</div>
                                    <div class="col-sm-6"><strong>Current Occupancy:</strong> <?= count($res_names) ?> / <?= intval($r['capacity']) ?> students</div>
                                    <div class="col-12 mt-2">
                                        <strong>Active Residents Impacted:</strong>
                                        <?php if(empty($res_names)): ?>
                                            <span class="text-muted">None (Room is currently unoccupied)</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-semibold"><?= htmlspecialchars(implode(', ', $res_names)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">Verifiable Ground / Operational Justification <span class="text-danger">*</span></label>
                                <textarea name="reason" class="form-control border-secondary" rows="3" placeholder="Provide complete technical explanation (e.g., Major ceiling leakage, electrical fault, planned renovation)..." required minlength="20"></textarea>
                                <div class="form-text small text-muted">This explanation will be permanently recorded in the institutional audit registry and reviewed by Supreme Admin.</div>
                            </div>

                            <div class="form-check p-3 bg-danger bg-opacity-10 rounded border border-danger border-opacity-25">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="certify_warning" id="certify_<?= $r['id'] ?>" required>
                                <label class="form-check-label small fw-semibold text-danger-emphasis" for="certify_<?= $r['id'] ?>">
                                    I solemnly affirm that this petition is based solely on genuine operational grounds and understand that Supreme Admin authorization is mandatory before any physical room closure.
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-25 pt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="request_decommission" class="btn btn-danger fw-bold btn-sm">
                                <i class="bi bi-send-check me-1"></i> Submit to Admin for Supreme Approval
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- WARDEN'S PETITIONS HISTORY -->
    <?php if(!empty($my_petitions)): ?>
        <div class="glass-card-light reveal" style="max-width:950px; margin:0 auto; padding: var(--space-xl);">
            <h5 class="fw-bold mb-3"><i class="bi bi-journal-bookmark text-primary me-2"></i> My Decommission Petitions Log</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-sticky">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Submitted On</th>
                            <th>Justification</th>
                            <th>Status</th>
                            <th>Admin Review Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($my_petitions as $mp): ?>
                            <tr>
                                <td class="fw-bold">Room <?= htmlspecialchars($mp['room_number']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($mp['created_at']))) ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 280px;" title="<?= htmlspecialchars($mp['reason']) ?>">
                                        <?= htmlspecialchars($mp['reason']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($mp['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending Review</span>
                                    <?php elseif($mp['status'] == 'approved'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Authorized</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> Rejected</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($mp['admin_remarks']) ? htmlspecialchars($mp['admin_remarks']) : '<span class="text-muted">—</span>' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include("../footer.php"); ?>