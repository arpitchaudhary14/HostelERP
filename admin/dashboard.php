<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: /login.php"); exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) { session_start();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
$stmt = mysqli_prepare($conn, "SELECT first_name, last_name FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$full_name = trim($user['first_name'] . " " . $user['last_name']);
$notice_count = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM notices")
)['total'] ?? 0;
$complaint_count = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints")
)['total'] ?? 0;
$leave_count = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests")
)['total'] ?? 0;
if ($role === "student") { $pending_complaints = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE student_id='$user_id' AND status='Pending'") )['total'] ?? 0; $pending_leaves = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests WHERE student_id='$user_id' AND status='Pending'") )['total'] ?? 0;
} else { $pending_complaints = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE status='Pending'") )['total'] ?? 0; $pending_leaves = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests WHERE status='Pending'") )['total'] ?? 0;
}
$paid = mysqli_fetch_assoc( mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Paid'")
)['total'] ?? 0;
$pending_fees = mysqli_fetch_assoc( mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Pending'")
)['total'] ?? 0;
$complaints = mysqli_fetch_assoc(mysqli_query($conn," SELECT SUM(status='Pending') as pending, SUM(status='Approved') as approved, SUM(status='Rejected') as rejected FROM complaints
"));
$attendance = mysqli_fetch_assoc(mysqli_query($conn," SELECT SUM(status='present') as present, SUM(status='absent') as absent, SUM(status='leave') as leave_days FROM attendance WHERE user_id IN (SELECT id FROM users WHERE role='student')
"));
$warden_attendance = mysqli_fetch_assoc(mysqli_query($conn," SELECT SUM(status='present') as present, SUM(status='absent') as absent, SUM(status='leave') as leave_days FROM attendance WHERE user_id IN (SELECT id FROM users WHERE role='warden')
"));
$pending_corrections = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance_corrections WHERE status='Pending'")
)['total'] ?? 0;
$pending_warden_leaves = mysqli_fetch_assoc( mysqli_query($conn, "SELECT COUNT(*) as total FROM warden_leave_requests WHERE status='Pending'")
)['total'] ?? 0;

// Governance: Decommission Petitions & Student 5-Day SLA Disputes
$dash_msg = "";
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_dispute'])) {
    validate_csrf();
    $dispute_id = intval($_POST['dispute_id'] ?? 0);
    $resolution = $_POST['resolution_type'] ?? 'refund_processed';
    $remarks = trim($_POST['admin_remarks'] ?? '');

    $d_res = mysqli_query($conn, "SELECT * FROM room_decommission_disputes WHERE id = $dispute_id LIMIT 1");
    if($d = mysqli_fetch_assoc($d_res)) {
        $st_id = intval($d['student_id']);
        $r_num = $d['room_number'];
        $status = ($resolution === 'rejected') ? 'rejected' : 'resolved';
        $stmt = mysqli_prepare($conn, "UPDATE room_decommission_disputes SET status = ?, resolution_type = ?, admin_remarks = ?, resolved_at = NOW() WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sssi", $status, $resolution, $remarks, $dispute_id);
        mysqli_stmt_execute($stmt);

        // Notify student
        $n_title = "Supreme Admin Action: Room $r_num 5-Day SLA Claim";
        $n_msg = "Your 5-Day SLA room decommission claim has been resolved by Supreme Admin.\nResolution: " . strtoupper(str_replace('_', ' ', $resolution)) . "\nRemarks: $remarks";
        mysqli_query($conn, "INSERT INTO notifications (user_id, sender_id, target_role, title, message, is_read) VALUES ($st_id, $user_id, 'student', '" . mysqli_real_escape_string($conn, $n_title) . "', '" . mysqli_real_escape_string($conn, $n_msg) . "', 0)");

        $dash_msg = "Dispute for Room '$r_num' resolved successfully!";
    }
}

$pending_petitions_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM room_decommission_requests WHERE status='pending'"))['t'] ?? 0;
$pending_disputes_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM room_decommission_disputes WHERE status='pending'"))['t'] ?? 0;
$pending_disputes_res = mysqli_query($conn, "
    SELECT rdd.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as student_name, u.email, u.phone 
    FROM room_decommission_disputes rdd 
    JOIN users u ON rdd.student_id = u.id 
    WHERE rdd.status='pending' 
    ORDER BY rdd.created_at ASC
");
?>
<?php $pageTitle = 'Dashboard (Admin) - HostelERP';
$pageDesc = 'Access your Dashboard (Admin) on HostelERP.';
include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
<div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl);"> <div class="dash-welcome"> <div class="dash-welcome-icon">👋</div> <div> <h3>Welcome back, <?= htmlspecialchars($full_name) ?></h3> <p>System analytics overview &mdash; here's what's happening today across all modules.</p> </div> </div>
</div>

<?php if(!empty($dash_msg)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($dash_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- NECESSARY ACTIONS / URGENT ESCALATIONS DESK -->
<div class="card border-0 mb-4 reveal" style="border-radius: var(--radius-md); <?= ($pending_petitions_count > 0 || $pending_disputes_count > 0) ? 'background: rgba(239, 68, 68, 0.05); border-left: 5px solid var(--accent-danger) !important;' : 'background: rgba(16, 185, 129, 0.05); border-left: 5px solid #10b981 !important;' ?>">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1 <?= ($pending_petitions_count > 0 || $pending_disputes_count > 0) ? 'text-danger' : 'text-success' ?>">
                    <i class="bi <?= ($pending_petitions_count > 0 || $pending_disputes_count > 0) ? 'bi-shield-exclamation' : 'bi-shield-check' ?> me-2"></i>
                    Necessary Actions / Urgent Governance Desk
                </h5>
                <p class="text-muted small mb-0">High-priority compliance items requiring executive Supreme Admin resolution.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="manage_rooms.php" class="btn btn-sm <?= $pending_petitions_count > 0 ? 'btn-danger' : 'btn-outline-secondary' ?> fw-semibold">
                    <i class="bi bi-door-closed me-1"></i> Warden Decommission Petitions: <strong><?= $pending_petitions_count ?></strong>
                </a>
                <span class="badge <?= $pending_disputes_count > 0 ? 'bg-danger fs-6' : 'bg-success py-2' ?> align-self-center">
                    <i class="bi bi-patch-exclamation me-1"></i> Student 5-Day SLA Claims: <?= $pending_disputes_count ?>
                </span>
            </div>
        </div>

        <?php if($pending_disputes_count > 0): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>SLA BREACH ALERT:</strong> Displaced students have reported unfulfilled relocation/refund beyond 5 business days. Resolve immediately below to ensure full institutional compliance:
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0 table-sticky">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Room Closed</th>
                            <th>Claim Remedy</th>
                            <th>Circumstances</th>
                            <th>Elapsed</th>
                            <th>Supreme Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($dispute = mysqli_fetch_assoc($pending_disputes_res)): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($dispute['student_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($dispute['email']) ?></small>
                                </td>
                                <td class="fw-bold">Room <?= htmlspecialchars($dispute['room_number']) ?></td>
                                <td>
                                    <span class="badge bg-danger">
                                        <?= htmlspecialchars(strtoupper(str_replace('_', ' ', $dispute['claim_type']))) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 260px;" title="<?= htmlspecialchars($dispute['details']) ?>">
                                        <?= htmlspecialchars($dispute['details']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= htmlspecialchars(date('M d, H:i', strtotime($dispute['created_at']))) ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-xs btn-danger fw-bold" data-bs-toggle="modal" data-bs-target="#resolveModal_<?= $dispute['id'] ?>">
                                        <i class="bi bi-check-circle me-1"></i> Resolve Claim
                                    </button>
                                </td>
                            </tr>

                            <!-- RESOLUTION MODAL -->
                            <div class="modal fade" id="resolveModal_<?= $dispute['id'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content glass-card-light" style="border: 2px solid var(--accent-danger);">
                                        <div class="modal-header border-0">
                                            <h5 class="modal-title fw-bold text-danger">Resolve 5-Day SLA Claim #<?= $dispute['id'] ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body pt-0">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="dispute_id" value="<?= $dispute['id'] ?>">
                                                <input type="hidden" name="resolve_dispute" value="1">

                                                <div class="p-3 bg-secondary bg-opacity-10 rounded mb-3 small">
                                                    <div><strong>Student:</strong> <?= htmlspecialchars($dispute['student_name']) ?> (<?= htmlspecialchars($dispute['email']) ?>)</div>
                                                    <div><strong>Room Number:</strong> <?= htmlspecialchars($dispute['room_number']) ?></div>
                                                    <div class="mt-1"><strong>Breach Notes:</strong> <?= htmlspecialchars($dispute['details']) ?></div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Redressal Action</label>
                                                    <select name="resolution_type" class="form-select" required>
                                                        <option value="refund_processed" selected>100% Proportional Fee Refund Issued</option>
                                                        <option value="room_allotted">Emergency Equivalent Room Allotted</option>
                                                        <option value="rejected">Dismiss Claim (Duplicate / Unsubstantiated)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-muted">Official Resolution Remarks</label>
                                                    <textarea name="admin_remarks" class="form-control" rows="2" placeholder="e.g. Refund of ₹6,500 credited to student account. Case closed." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-0 pt-0">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger btn-sm fw-bold">
                                                    <i class="bi bi-shield-check me-1"></i> Finalize Resolution
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif($pending_petitions_count == 0 && $pending_disputes_count == 0): ?>
            <div class="d-flex align-items-center text-success small">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <span>Institutional compliance optimal: <strong>0 pending Warden decommission requests</strong> and <strong>0 student SLA claims</strong>. All housing operations regular.</span>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="row g-4 mb-4 reveal"> <div class="col-md-4"> <div class="glass-card-light d-flex align-items-center p-3 h-100" style="border-left: 5px solid var(--accent-primary);"> <img src="/assets/images/Indexia_Logo.jpeg" height="40" class="me-3 rounded shadow-sm" alt="HostelERP Graphic"> <div class="flex-grow-1"> <h6 class="fw-bold mb-0">Indexia Library</h6> <small class="text-muted text-xxs text-uppercase">Books & Borrows</small> </div> <a href="library/dashboard.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Dashboard</a> </div> </div> <div class="col-md-4"> <div class="glass-card-light d-flex align-items-center p-3 h-100" style="border-left: 5px solid var(--accent-secondary);"> <img src="/assets/images/MatrixFit_Logo.jpeg" height="40" class="me-3 rounded shadow-sm" alt="HostelERP Graphic"> <div class="flex-grow-1"> <h6 class="fw-bold mb-0">MatrixFit Gym</h6> <small class="text-muted text-xxs text-uppercase">Members & Plans</small> </div> <a href="gym/dashboard.php" class="btn btn-sm btn-outline-success rounded-pill px-3">Dashboard</a> </div> </div> <div class="col-md-4"> <div class="glass-card-light d-flex align-items-center p-3 h-100" style="border-left: 5px solid #0ea5e9;"> <img src="/assets/images/Cleanly_Logo.jpeg" height="40" class="me-3 rounded shadow-sm" alt="HostelERP Graphic"> <div class="flex-grow-1"> <h6 class="fw-bold mb-0">Cleanly Laundry</h6> <small class="text-muted text-xxs text-uppercase">Service & Orders</small> </div> <a href="laundry/dashboard.php" class="btn btn-sm btn-outline-info rounded-pill px-3">Dashboard</a> </div> </div>
</div>
<p class="dash-section-label"><i class="bi bi-bar-chart-fill"></i>Key Metrics</p>
<div class="row g-4 mb-4">
<div class="col-md-4 reveal">
<div class="stat-card stat-primary">
<i class="bi bi-calendar-check stat-icon"></i>
<h5>Leave Requests</h5>
<h2 class="text-gradient" data-count="<?= $leave_count ?>"><?= $leave_count ?></h2>
<span class="stat-sub">Total submitted</span>
<a class="stat-link" href="manage_warden_leaves.php" style="color:var(--accent-primary);">View all <i class="bi bi-arrow-right"></i></a>
</div>
</div>
<div class="col-md-4 reveal">
<div class="stat-card stat-danger">
<i class="bi bi-exclamation-triangle stat-icon"></i>
<h5>Complaints</h5>
<h2 style="color:var(--accent-danger);" data-count="<?= $complaint_count ?>"><?= $complaint_count ?></h2>
<span class="stat-sub"><?= $pending_complaints ?> pending</span>
<a class="stat-link" href="../warden/manage_complaints.php" style="color:var(--accent-danger);">Manage <i class="bi bi-arrow-right"></i></a>
</div>
</div>
<div class="col-md-4 reveal">
<div class="stat-card stat-success">
<i class="bi bi-megaphone stat-icon"></i>
<h5>Notices</h5>
<h2 style="color:var(--accent-secondary);" data-count="<?= $notice_count ?>"><?= $notice_count ?></h2>
<span class="stat-sub">Published</span>
<a class="stat-link" href="notices.php" style="color:var(--accent-secondary);">Post new <i class="bi bi-arrow-right"></i></a>
</div>
</div>
</div>
<div class="row g-4 mb-4">
<div class="col-md-3 reveal">
<div class="stat-card stat-primary">
<i class="bi bi-person-check stat-icon"></i>
<h5>Warden Leaves</h5>
<h2 class="text-gradient" data-count="<?= $pending_warden_leaves ?>"><?= $pending_warden_leaves ?></h2>
<span class="stat-sub">Awaiting approval</span>
</div>
</div>
<div class="col-md-3 reveal">
<div class="stat-card stat-danger">
<i class="bi bi-pencil-square stat-icon"></i>
<h5>Corrections</h5>
<h2 style="color:var(--accent-danger);" data-count="<?= $pending_corrections ?>"><?= $pending_corrections ?></h2>
<span class="stat-sub">Awaiting review</span>
</div>
</div>
</div>
<p class="dash-section-label"><i class="bi bi-pie-chart-fill"></i>Analytics</p>
<div class="row g-4 mb-4">
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<div class="chart-card-header"><i class="bi bi-currency-rupee text-success"></i><span>Fees Collection</span></div>
<canvas id="feesChart"></canvas>
</div>
</div>
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<div class="chart-card-header"><i class="bi bi-exclamation-triangle text-warning"></i><span>Complaint Stats</span></div>
<canvas id="complaintChart"></canvas>
</div>
</div>
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<div class="chart-card-header"><i class="bi bi-people text-info"></i><span>Student Attendance</span></div>
<canvas id="attendanceChart"></canvas>
</div>
</div>
</div>
<div class="row g-4 mb-4">
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<div class="chart-card-header"><i class="bi bi-shield-person text-primary"></i><span>Warden Attendance</span></div>
<canvas id="wardenAttendanceChart"></canvas>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.plugins.legend.labels.padding = 16;
const _isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const _gridColor = _isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.05)';
Chart.defaults.color = _isDark ? 'rgba(255,255,255,0.65)' : 'rgba(0,0,0,0.6)';
new Chart(document.getElementById('feesChart'), { type: 'bar', data: { labels: ['Paid','Pending'], datasets: [{ data: [<?= $paid ?>, <?= $pending_fees ?>], backgroundColor: [ 'rgba(0, 230, 118, 0.75)', 'rgba(255, 82, 82, 0.75)' ], borderRadius: 8, borderSkipped: false }] }, options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: _gridColor } }, x: { grid: { display: false } } } }
});
new Chart(document.getElementById('complaintChart'), { type: 'doughnut', data: { labels: ['Pending','Approved','Rejected'], datasets: [{ data: [ <?= $complaints['pending'] ?? 0 ?>, <?= $complaints['approved'] ?? 0 ?>, <?= $complaints['rejected'] ?? 0 ?> ], backgroundColor: [ 'rgba(255, 171, 64, 0.8)', 'rgba(0, 230, 118, 0.8)', 'rgba(255, 82, 82, 0.8)' ], borderWidth: 0, cutout: '65%' }] }, options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('attendanceChart'), { type: 'doughnut', data: { labels: ['Present','Absent','Leave'], datasets: [{ data: [ <?= $attendance['present'] ?? 0 ?>, <?= $attendance['absent'] ?? 0 ?>, <?= $attendance['leave_days'] ?? 0 ?> ], backgroundColor: [ 'rgba(0, 230, 118, 0.8)', 'rgba(255, 82, 82, 0.8)', 'rgba(64, 196, 255, 0.8)' ], borderWidth: 0, cutout: '65%' }] }, options: { plugins: { legend: { position: 'bottom' } } }
});
new Chart(document.getElementById('wardenAttendanceChart'), { type: 'doughnut', data: { labels: ['Present','Absent','Leave'], datasets: [{ data: [ <?= $warden_attendance['present'] ?? 0 ?>, <?= $warden_attendance['absent'] ?? 0 ?>, <?= $warden_attendance['leave_days'] ?? 0 ?> ], backgroundColor: [ 'rgba(108, 99, 255, 0.8)', 'rgba(255, 171, 64, 0.8)', 'rgba(0, 230, 118, 0.8)' ], borderWidth: 0, cutout: '65%' }] }, options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php include("../footer.php"); ?>