<?php
include("../session_check.php");
include("../db.php");

if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_dispute'])) {
    validate_csrf();
    $room_number = trim($_POST['room_number'] ?? '');
    $claim_type = $_POST['claim_type'] ?? 'fee_refund';
    $details = trim($_POST['details'] ?? '');

    $allowed_types = ['fee_refund', 'equivalent_room', 'both'];
    if(!in_array($claim_type, $allowed_types)) {
        $claim_type = 'fee_refund';
    }

    if(empty($room_number) || empty($details)) {
        $error = "Please fill in all required fields.";
    } elseif(strlen($details) < 20) {
        $error = "Please provide complete details (minimum 20 characters) explaining your 5-day SLA breach.";
    } else {
        try {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO room_decommission_disputes (student_id, room_number, claim_type, details, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            mysqli_stmt_bind_param($stmt, "isss", $user_id, $room_number, $claim_type, $details);

            if(mysqli_stmt_execute($stmt)) {
                // High-priority notification directly to Admin (guarded against schema mismatches)
                try {
                    $admin_title = "URGENT SLA DISPUTE: Decommissioned Room $room_number";
                    $admin_msg = "Student User (ID: $user_id) has lodged a 5-Day SLA Claim for decommissioned Room $room_number requesting " . strtoupper(str_replace('_', ' ', $claim_type)) . ". Review immediately under 'Necessary Actions' on the Admin Dashboard.";
                    
                    $chk_tr = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'target_role'");
                    if($chk_tr && mysqli_num_rows($chk_tr) > 0) {
                        mysqli_query($conn, "
                            INSERT INTO notifications (user_id, sender_id, target_role, title, message, is_read)
                            VALUES (NULL, $user_id, 'admin', '" . mysqli_real_escape_string($conn, $admin_title) . "', '" . mysqli_real_escape_string($conn, $admin_msg) . "', 0)
                        ");
                    } else {
                        mysqli_query($conn, "
                            INSERT INTO notifications (user_id, title, message, is_read)
                            VALUES (NULL, '" . mysqli_real_escape_string($conn, $admin_title) . "', '" . mysqli_real_escape_string($conn, $admin_msg) . "', 0)
                        ");
                    }
                } catch (Throwable $ne) {
                    error_log("Failed to dispatch admin SLA claim notification: " . $ne->getMessage());
                }

                $_SESSION['action_type'] = 'student_decommission_dispute';
                redirect_thank_you();
            } else {
                $error = "Failed to submit dispute: " . mysqli_error($conn);
            }
        } catch (Throwable $e) {
            error_log("Student dispute claim submission error: " . $e->getMessage());
            $error = "Could not record dispute claim: " . $e->getMessage();
        }
    }
}

// Fetch past claims by this student
$my_claims = [];
$c_res = mysqli_query($conn, "SELECT * FROM room_decommission_disputes WHERE student_id = $user_id ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($c_res)) {
    $my_claims[] = $row;
}

$pageTitle = '5-Day SLA Room Decommission Grievance - HostelERP';
$pageDesc = 'Escalate room closure compensation or alternative allotment disputes directly to the Supreme Admin.';
include("../header.php");
?>
<div class="container mt-4 page-fade-in" style="max-width: 850px;">
    <div class="glass-card-light mb-4 reveal p-4">
        <div class="d-flex align-items-center mb-2">
            <h3 style="font-weight:700; margin:0;"><i class="bi bi-shield-shaded text-danger me-2"></i> Supreme Admin Escalation Portal</h3>
        </div>
        <p class="text-muted mb-0">Under institutional guidelines, students displaced by room decommissionings are legally guaranteed an equivalent room or 100% proportional fee refund within 5 business days.</p>
    </div>

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

    <div class="glass-card-light p-4 reveal mb-4">
        <div class="alert alert-warning py-3 px-3 mb-4 small border-start border-4 border-warning">
            <div class="d-flex align-items-center mb-1">
                <i class="bi bi-clock-history fs-5 me-2 text-warning"></i>
                <strong class="text-warning-emphasis fs-6">5-Day SLA Breach Protection</strong>
            </div>
            If your room was decommissioned by the administration and you have not received an alternative room allotment or proportional fee credit within 5 business days, this claim will be directly highlighted on the Supreme Admin's dashboard under <strong>"Necessary Actions"</strong> for mandatory redressal.
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Decommissioned Room Number <span class="text-danger">*</span></label>
                    <input type="text" name="room_number" class="form-control" placeholder="e.g. 501, 102" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Redressal Remedy Requested <span class="text-danger">*</span></label>
                    <select name="claim_type" class="form-select" required>
                        <option value="fee_refund" selected>Immediate 100% Proportional Fee Refund</option>
                        <option value="equivalent_room">Emergency Equivalent Room Allotment</option>
                        <option value="both">Administrative Discretion (Refund or Room)</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Circumstances & Breach Details <span class="text-danger">*</span></label>
                <textarea name="details" class="form-control" rows="4" placeholder="Detail when you were unallocated, how many days have passed without housing, and any communication with the Warden..." required minlength="20"></textarea>
                <div class="form-text small text-muted">Your claim will be delivered directly to Supreme Admin with timestamp verification.</div>
            </div>

            <button type="submit" name="submit_dispute" class="btn btn-danger fw-bold w-100 py-2">
                <i class="bi bi-send-exclamation me-1"></i> Submit Urgent Claim to Supreme Admin
            </button>
        </form>
    </div>

    <!-- MY PAST CLAIMS -->
    <?php if(!empty($my_claims)): ?>
        <div class="glass-card-light p-4 reveal">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i> My Lodged Decommission Claims</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-sticky">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Claim Type</th>
                            <th>Submitted On</th>
                            <th>Status</th>
                            <th>Admin Resolution Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($my_claims as $mc): ?>
                            <tr>
                                <td class="fw-bold">Room <?= htmlspecialchars($mc['room_number']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $mc['claim_type']))) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($mc['created_at']))) ?></td>
                                <td>
                                    <?php if($mc['status'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Awaiting Admin Action</span>
                                    <?php elseif($mc['status'] == 'resolved'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Resolved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> Dismissed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= !empty($mc['admin_remarks']) ? htmlspecialchars($mc['admin_remarks']) : '<span class="text-muted">Under Review</span>' ?>
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
