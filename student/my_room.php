<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = intval($_SESSION['user_id']);
$check_rooms = mysqli_query($conn, "SHOW COLUMNS FROM rooms LIKE 'room_type'");
if(mysqli_num_rows($check_rooms) == 0) {
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN room_type VARCHAR(20) DEFAULT '2-Seater'");
    mysqli_query($conn, "ALTER TABLE rooms ADD COLUMN comfort_tier VARCHAR(20) DEFAULT 'AC'");
    mysqli_query($conn, "UPDATE rooms SET room_type = IF(id % 2 = 0, '3-Seater', '2-Seater'), comfort_tier = IF(id % 3 = 0, 'AC', IF(id % 3 = 1, 'Air-Cooled', 'Non-AC'))");
}
$check_alloc = mysqli_query($conn, "SHOW COLUMNS FROM room_allocations LIKE 'expires_at'");
if(mysqli_num_rows($check_alloc) == 0) {
    mysqli_query($conn, "ALTER TABLE room_allocations ADD COLUMN expires_at TIMESTAMP NULL");
    mysqli_query($conn, "ALTER TABLE room_allocations ADD COLUMN is_verified TINYINT DEFAULT 1");
}
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS roommate_invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
)");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS room_swaps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
)");
$msg = "";
$msg_type = "success";
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    validate_csrf();
    $action = $_POST['action'] ?? '';
    if($action == 'confirm_allotment') {
        mysqli_query($conn, "UPDATE room_allocations SET is_verified = 1, expires_at = NULL WHERE user_id = $user_id AND status = 'active'");
        $msg = "Room Allotment Confirmed Successfully! Your room is now permanently locked.";
    }
    if($action == 'send_invite') {
        $search_term = mysqli_real_escape_string($conn, $_POST['roommate_search'] ?? '');
        $res = mysqli_query($conn, "SELECT id FROM users WHERE role='student' AND id != $user_id AND (email = '$search_term' OR id = '$search_term') LIMIT 1");
        if($row = mysqli_fetch_assoc($res)) {
            $receiver_id = intval($row['id']);
            $chk = mysqli_query($conn, "SELECT id FROM roommate_invites WHERE (sender_id = $user_id AND receiver_id = $receiver_id) AND status = 'pending'");
            if(mysqli_num_rows($chk) > 0) {
                $msg = "Invitation already pending for this student.";
                $msg_type = "warning";
            } else {
                mysqli_query($conn, "INSERT INTO roommate_invites (sender_id, receiver_id, status) VALUES ($user_id, $receiver_id, 'pending')");
                $msg = "Roommate invitation sent successfully!";
            }
        } else {
            $msg = "No student found with that ID or Email.";
            $msg_type = "danger";
        }
    }
    if($action == 'accept_invite') {
        $invite_id = intval($_POST['invite_id']);
        mysqli_query($conn, "UPDATE roommate_invites SET status = 'accepted' WHERE id = $invite_id AND receiver_id = $user_id");
        $msg = "Roommate Invitation Accepted!";
    }
    if($action == 'decline_invite') {
        $invite_id = intval($_POST['invite_id']);
        mysqli_query($conn, "UPDATE roommate_invites SET status = 'declined' WHERE id = $invite_id AND receiver_id = $user_id");
        $msg = "Roommate Invitation Declined.";
        $msg_type = "warning";
    }
    if($action == 'cancel_invite') {
        $invite_id = intval($_POST['invite_id']);
        mysqli_query($conn, "DELETE FROM roommate_invites WHERE id = $invite_id AND sender_id = $user_id");
        $msg = "Invitation recalled successfully.";
        $msg_type = "info";
    }
    if($action == 'request_swap') {
        $search_term = mysqli_real_escape_string($conn, $_POST['swap_target'] ?? '');
        $res = mysqli_query($conn, "SELECT id FROM users WHERE role='student' AND id != $user_id AND (email = '$search_term' OR id = '$search_term') LIMIT 1");
        if($row = mysqli_fetch_assoc($res)) {
            $receiver_id = intval($row['id']);
            $chk_room = mysqli_query($conn, "SELECT id FROM room_allocations WHERE user_id = $receiver_id AND status = 'active' AND is_verified = 1");
            if(mysqli_num_rows($chk_room) == 0) {
                $msg = "Target student must have a confirmed, active room allocation to perform a swap.";
                $msg_type = "danger";
            } else {
                mysqli_query($conn, "INSERT INTO room_swaps (sender_id, receiver_id, status) VALUES ($user_id, $receiver_id, 'pending')");
                $msg = "Room swap request sent successfully! Waiting for roommate's consent.";
            }
        } else {
            $msg = "No student found with that ID or Email.";
            $msg_type = "danger";
        }
    }
    if($action == 'accept_swap') {
        $swap_id = intval($_POST['swap_id']);
        mysqli_query($conn, "UPDATE room_swaps SET status = 'accepted_by_receiver' WHERE id = $swap_id AND receiver_id = $user_id");
        $msg = "Room swap request accepted! Now awaiting Warden's final approval.";
    }
    if($action == 'decline_swap') {
        $swap_id = intval($_POST['swap_id']);
        mysqli_query($conn, "UPDATE room_swaps SET status = 'declined' WHERE id = $swap_id AND receiver_id = $user_id");
        $msg = "Room swap request declined.";
        $msg_type = "warning";
    }
}
$stmt = mysqli_prepare($conn,
    "SELECT r.id as room_id, r.room_number, r.capacity, r.current_occupancy, r.room_type, r.comfort_tier, ra.allocated_at, ra.expires_at, ra.is_verified
     FROM room_allocations ra
     JOIN rooms r ON ra.room_id = r.id
     WHERE ra.user_id = ? AND ra.status = 'active'
     LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$room = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$roommates = [];
if($room) {
    $room_id = intval($room['room_id']);
    $res = mysqli_query($conn, "
        SELECT u.id, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name, u.email, u.role
        FROM room_allocations ra
        JOIN users u ON ra.user_id = u.id
        WHERE ra.room_id = $room_id AND ra.status = 'active' AND ra.user_id != $user_id AND ra.is_verified = 1
    ");
    while($row = mysqli_fetch_assoc($res)) {
        $roommates[] = $row;
    }
}
$sent_invites = [];
$res = mysqli_query($conn, "
    SELECT ri.id, ri.status, ri.created_at, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as name, u.email
    FROM roommate_invites ri
    JOIN users u ON ri.receiver_id = u.id
    WHERE ri.sender_id = $user_id
");
while($row = mysqli_fetch_assoc($res)) $sent_invites[] = $row;
$received_invites = [];
$res = mysqli_query($conn, "
    SELECT ri.id, ri.status, ri.created_at, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as name, u.email
    FROM roommate_invites ri
    JOIN users u ON ri.sender_id = u.id
    WHERE ri.receiver_id = $user_id
");
while($row = mysqli_fetch_assoc($res)) $received_invites[] = $row;
$my_swaps = [];
$res = mysqli_query($conn, "
    SELECT rs.id, rs.status, rs.created_at, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as name, u.email, rs.sender_id
    FROM room_swaps rs
    JOIN users u ON IF(rs.sender_id = $user_id, rs.receiver_id = u.id, rs.sender_id = u.id)
    WHERE rs.sender_id = $user_id OR rs.receiver_id = $user_id
    ORDER BY rs.created_at DESC
");
while($row = mysqli_fetch_assoc($res)) $my_swaps[] = $row;
include("../header.php");
?>
<div class="container mt-4 page-fade-in">
    <?php if($msg): ?>
        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i> <?= htmlspecialchars($msg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl);">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-house-door text-primary me-2"></i> Housing & Roommates</h3>
        </div>
        <p class="text-muted" style="margin:0;">Manage your room allocation, sign roommate agreements, and submit swap requests.</p>
    </div>
    <?php if($room): ?>
        <?php if($room['is_verified'] == 0): ?>
            <div class="card bg-warning-subtle border-warning border-start border-4 mb-4 reveal">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h5 class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Provisional Allotment Verification Lock</h5>
                            <p class="text-dark-emphasis mb-0 small">You must confirm this room allotment within 24 hours of issue, otherwise it will automatically cancel.</p>
                        </div>
                        <div class="mt-2 mt-md-0 text-center">
                            <span class="badge bg-danger fs-6 py-2 px-3 mb-2 d-block" id="countdown-timer">Checking time...</span>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="confirm_allotment">
                                <button type="submit" class="btn btn-sm btn-success fw-bold me-2"><i class="bi bi-check-lg"></i> Confirm Allotment</button>
                            </form>
                            <button class="btn btn-sm btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i> Print Receipt</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', () => {
                let expiresAt = new Date("<?= $room['expires_at'] ?>").getTime();
                let timer = setInterval(() => {
                    let now = new Date().getTime();
                    let diff = expiresAt - now;
                    if(diff <= 0) {
                        clearInterval(timer);
                        document.getElementById('countdown-timer').innerText = "Expired";
                        window.location.reload();
                    } else {
                        let h = Math.floor(diff / (1000 * 60 * 60));
                        let m = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        let s = Math.floor((diff % (1000 * 60)) / 1000);
                        document.getElementById('countdown-timer').innerHTML = `<i class="bi bi-clock-history"></i> Expires in: ${h}h ${m}m ${s}s`;
                    }
                }, 1000);
            });
            </script>
        <?php endif; ?>
        <div class="row g-4 mb-4">
            <div class="col-md-5 reveal">
                <div class="glass-card-light h-100" style="border-top: 4px solid var(--bs-info);">
                    <h5 class="fw-bold mb-3"><i class="bi bi-box-seam text-info me-2"></i> Room Specifications</h5>
                    <div class="d-flex justify-content-between text-muted small mb-2 border-bottom pb-2">
                        <span>Room Number:</span>
                        <span class="fw-bold text-white fs-6">Room <?= htmlspecialchars($room['room_number']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2 border-bottom pb-2">
                        <span>Room Configuration:</span>
                        <span class="fw-bold text-white"><?= htmlspecialchars($room['room_type']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2 border-bottom pb-2">
                        <span>Comfort Tier:</span>
                        <span class="fw-bold text-info"><?= htmlspecialchars($room['comfort_tier']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>Occupancy Status:</span>
                        <span class="fw-bold text-white"><?= intval($room['current_occupancy']) ?> / <?= intval($room['capacity']) ?> Occupied</span>
                    </div>
                    <?php
                        $percent = ($room['capacity'] > 0) ? ($room['current_occupancy'] / $room['capacity']) * 100 : 0;
                    ?>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: <?= round($percent) ?>%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 reveal">
                <div class="glass-card-light h-100" style="border-top: 4px solid var(--bs-success);">
                    <h5 class="fw-bold mb-3"><i class="bi bi-people text-success me-2"></i> My Roommates</h5>
                    <?php if(count($roommates) > 0): ?>
                        <div class="row g-3">
                            <?php foreach($roommates as $rm): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-secondary bg-opacity-25 rounded d-flex align-items-center">
                                        <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:40px; height:40px; font-weight:700;">
                                            <?= strtoupper(substr($rm['full_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-white"><?= htmlspecialchars($rm['full_name']) ?></h6>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;"><?= htmlspecialchars($rm['email']) ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-person-badge text-muted fs-2 mb-2 d-block"></i>
                            <p class="text-muted small">No verified roommates in this room yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="glass-card-light mb-4 reveal" style="border-top: 4px solid var(--bs-warning);">
            <h5 class="fw-bold mb-3 text-warning"><i class="bi bi-arrow-left-right me-2"></i> Roommate Swap Request Desk</h5>
            <div class="row g-4">
                <div class="col-md-5">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="request_swap">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Target Student (Roll Number/ID or Email)</label>
                            <input type="text" name="swap_target" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 23 or student@college.com" required>
                            <div class="form-text text-muted small mt-1">Both students must have dynamic room allotments to request a room swap.</div>
                        </div>
                        <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="bi bi-arrow-left-right"></i> Initiate Swap Invitation</button>
                    </form>
                </div>  
                <div class="col-md-7 border-start border-secondary border-opacity-25">
                    <h6 class="fw-bold text-white mb-3">Active Swap Requests</h6>
                    <?php if(count($my_swaps) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($my_swaps as $sw): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($sw['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($sw['email']) ?></small>
                                            </td>
                                            <td>
                                                <?php if($sw['status'] == 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Awaiting Consent</span>
                                                <?php elseif($sw['status'] == 'accepted_by_receiver'): ?>
                                                    <span class="badge bg-info">Consented (Pending Warden)</span>
                                                <?php elseif($sw['status'] == 'approved'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger"><?= htmlspecialchars($sw['status']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($sw['status'] == 'pending' && $sw['sender_id'] != $user_id): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="invite_id" value="<?= $sw['id'] ?>">
                                                        <input type="hidden" name="swap_id" value="<?= $sw['id'] ?>">
                                                        <input type="hidden" name="action" value="accept_swap">
                                                        <button type="submit" class="btn btn-xs btn-success me-1">Accept</button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="swap_id" value="<?= $sw['id'] ?>">
                                                        <input type="hidden" name="action" value="decline_swap">
                                                        <button type="submit" class="btn btn-xs btn-danger">Decline</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-info-circle text-muted fs-3 mb-2 d-block"></i>
                            <p class="text-muted small mb-0">No active roommate swap requests recorded.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info border-info border-start border-4 mb-4 reveal">
            <h5 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i> No active room allocation found!</h5>
            <p class="mb-0 small">Please create or join a roommate group below, or select the **Solo Seeker** preference. The Warden will prioritize allocations based on College Attendance scores.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 reveal">
                <div class="glass-card-light h-100" style="border-top: 4px solid var(--bs-primary);">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-medical me-2"></i> Roommate Agreement Desk</h5>
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="send_invite">
                        <div class="mb-3">
                            <label class="form-label text-muted small">Search roommate by Roll Number/ID or Email</label>
                            <div class="input-group">
                                <input type="text" name="roommate_search" class="form-control bg-dark text-white border-secondary" placeholder="e.g. 21 or roommate@college.com" required>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send Invitation</button>
                            </div>
                        </div>
                    </form>
                    <h6 class="fw-bold text-white mt-4 mb-3 border-bottom pb-2">Pending Invites Sent</h6>
                    <?php if(count($sent_invites) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle small mb-0">
                                <tbody>
                                    <?php foreach($sent_invites as $si): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($si['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($si['email']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $si['status'] == 'pending' ? 'warning text-dark' : ($si['status'] == 'accepted' ? 'success' : 'danger') ?>-subtle text-<?= $si['status'] == 'pending' ? 'warning' : ($si['status'] == 'accepted' ? 'success' : 'danger') ?>"><?= htmlspecialchars($si['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php if($si['status'] == 'pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="invite_id" value="<?= $si['id'] ?>">
                                                        <input type="hidden" name="action" value="cancel_invite">
                                                        <button type="submit" class="btn btn-xs btn-outline-danger"><i class="bi bi-x-circle"></i> Recall</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small">No active outbound roommate invitations.</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6 reveal">
                <div class="glass-card-light h-100" style="border-top: 4px solid var(--bs-warning);">
                    <h5 class="fw-bold text-warning mb-3"><i class="bi bi-envelope-open me-2"></i> Roommate Requests Received</h5>
                    <?php if(count($received_invites) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle small mb-0">
                                <thead>
                                    <tr>
                                        <th>Sender</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($received_invites as $ri): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($ri['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($ri['email']) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?= $ri['status'] == 'pending' ? 'warning text-dark' : ($ri['status'] == 'accepted' ? 'success' : 'danger') ?>-subtle text-<?= $ri['status'] == 'pending' ? 'warning' : ($ri['status'] == 'accepted' ? 'success' : 'danger') ?>"><?= htmlspecialchars($ri['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php if($ri['status'] == 'pending'): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="invite_id" value="<?= $ri['id'] ?>">
                                                        <input type="hidden" name="action" value="accept_invite">
                                                        <button type="submit" class="btn btn-xs btn-success me-1">Accept</button>
                                                    </form>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="invite_id" value="<?= $ri['id'] ?>">
                                                        <input type="hidden" name="action" value="decline_invite">
                                                        <button type="submit" class="btn btn-xs btn-danger">Decline</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-envelope-open-fill text-muted fs-2 mb-2 d-block"></i>
                            <p class="text-muted small mb-0">No roommate agreements received yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<style>
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.2rem;
}
</style>
<?php include("../footer.php"); ?>