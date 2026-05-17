<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'] ?? '';
$stmt = mysqli_prepare($conn, "SELECT first_name, last_name, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$full_name = trim($user['first_name'] . " " . $user['last_name']);
$library_fine = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(fine_amount) as total FROM library_borrows WHERE user_id = $user_id"))['total'] ?? 0;
$overdue_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM library_borrows WHERE user_id = $user_id AND status = 'overdue'"))['total'] ?? 0;
$borrowed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM library_borrows WHERE user_id = $user_id AND status = 'borrowed'"))['total'] ?? 0;
$gym_sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM gym_subscriptions WHERE user_id = $user_id AND status = 'active'"));
$laundry_sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.*, p.name FROM laundry_subscriptions s JOIN laundry_plans p ON s.plan_id = p.id WHERE s.user_id = $user_id AND s.status = 'Active'"));
$notice_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM notices"))['total'] ?? 0;
$complaint_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE student_id='$user_id'"))['total'] ?? 0;
$leave_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests WHERE student_id='$user_id'"))['total'] ?? 0;
$paid = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Paid'"))['total'] ?? 0;
$pending_fees = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Pending'"))['total'] ?? 0;
include("../header.php"); 
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="section-header-elite p-0">
                <h3>Welcome, <?= htmlspecialchars($full_name) ?> 👋</h3>
                <p class="mb-0">Your HostelERP experience is fully integrated and secured.</p>
            </div>
            <div class="mt-2">
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4 reveal">
            <?php if($library_fine > 0 || $overdue_count > 0): ?>
            <div class="glass-card-light border-start border-4 border-danger h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-danger-soft text-danger me-3 h2 mb-0">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Indexia Action</h6>
                        <p class="small text-muted mb-0">
                            <?= $overdue_count ?> book(s) overdue. Fine: <span class="fw-bold text-danger">₹<?= number_format($library_fine, 2) ?></span>
                        </p>
                    </div>
                    <a href="library/my_bookshelf.php" class="btn btn-sm btn-danger rounded-pill px-3">Pay Fine</a>
                </div>
            </div>
            <?php else: ?>
            <div class="glass-card-light border-start border-4 border-info h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-info-soft text-info me-3 h2 mb-0" style="background: rgba(13, 202, 240, 0.1);">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Indexia Library</h6>
                        <p class="small text-muted mb-0">
                            <?= $borrowed_count > 0 ? $borrowed_count . ' active book(s)' : 'No active books' ?>
                        </p>
                    </div>
                    <a href="library/dashboard.php" class="btn btn-sm btn-info text-white rounded-pill px-3">Status</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 reveal">
            <div class="glass-card-light border-start border-4 border-<?= $laundry_sub ? 'primary' : 'secondary' ?> h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-<?= $laundry_sub ? 'primary' : 'secondary' ?>-soft text-<?= $laundry_sub ? 'primary' : 'secondary' ?> me-3 h2 mb-0">
                        <i class="bi bi-droplet"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">Cleanly Laundry</h6>
                        <p class="small text-muted mb-0">
                            <?= $laundry_sub ? $laundry_sub['remaining_clothes'] . ' clothes left' : 'No active plan.' ?>
                        </p>
                    </div>
                    <a href="laundry/<?= $laundry_sub ? 'dashboard.php' : 'plans.php' ?>" class="btn btn-sm btn-<?= $laundry_sub ? 'primary' : 'secondary' ?> rounded-pill px-3">
                        <?= $laundry_sub ? 'Status' : 'Plans' ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="glass-card-light border-start border-4 border-<?= $gym_sub ? 'success' : 'warning' ?> h-100">
                <div class="d-flex align-items-center">
                    <div class="icon-circle bg-<?= $gym_sub ? 'success' : 'warning' ?>-soft text-<?= $gym_sub ? 'success' : 'warning' ?> me-3 h2 mb-0">
                        <i class="bi bi-activity"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">MatrixFit Status</h6>
                        <p class="small text-muted mb-0">
                            <?= $gym_sub ? 'Active Plan' : 'No active plan.' ?>
                        </p>
                    </div>
                    <a href="gym/<?= $gym_sub ? 'my_membership.php' : 'plans.php' ?>" class="btn btn-sm btn-<?= $gym_sub ? 'success' : 'warning text-dark' ?> rounded-pill px-3">
                        <?= $gym_sub ? 'Stats' : 'Join' ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4 reveal">
            <div class="stat-card-premium text-center">
                <h5>My Leaves</h5>
                <h2 class="text-gradient" style="color:var(--accent-primary);"><?= $leave_count ?></h2>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card-premium text-center">
                <h5>My Complaints</h5>
                <h2 style="color:var(--accent-danger);"><?= $complaint_count ?></h2>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="stat-card-premium text-center">
                <h5>System Notices</h5>
                <h2 style="color:var(--accent-secondary);"><?= $notice_count ?></h2>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4 reveal">
            <div class="glass-card-light" style="padding:var(--space-lg);">
                <h6 class="text-center fw-bold">Fees Status</h6>
                <canvas id="feesChart"></canvas>
            </div>
        </div>
        <div class="col-md-8 reveal">
            <div class="glass-card-light h-100" style="padding:var(--space-lg);">
                <h6 class="fw-bold mb-4">Quick Shortcuts</h6>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <a href="library/member_card.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-card-heading h2 text-primary d-block"></i>
                            <span class="small fw-bold text-body">Lib Card</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="gym/member_card.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-person-badge h2 text-success d-block"></i>
                            <span class="small fw-bold text-body">Gym Card</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="library/catalog.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-search h2 text-warning d-block"></i>
                            <span class="small fw-bold text-body">Books</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="laundry/member_card.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-card-list h2 text-info d-block"></i>
                            <span class="small fw-bold text-body">Wash Pass</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-2">
                        <a href="laundry/dashboard.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-droplet-half h2 text-primary d-block"></i>
                            <span class="small fw-bold text-body">Cleanly</span>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="attendance.php" class="shortcut-btn text-center p-3 rounded-4 d-block text-decoration-none">
                            <i class="bi bi-calendar-check h2 text-danger d-block"></i>
                            <span class="small fw-bold text-body">Attendance</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.bg-danger-soft { background: rgba(255, 82, 82, 0.1); }
.bg-success-soft { background: rgba(0, 230, 118, 0.1); }
.bg-warning-soft { background: rgba(255, 171, 64, 0.1); }
.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.bg-secondary-soft { background: rgba(108, 117, 125, 0.1); }
.icon-circle { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; border-radius: 50%; }
.shortcut-btn { background: rgba(0,0,0,0.03); border: 1px solid transparent; transition: all 0.2s; }
.shortcut-btn:hover { background: rgba(0,0,0,0.08); transform: translateY(-3px); }
[data-theme='dark'] .shortcut-btn { background: rgba(255,255,255,0.06); border-color: rgba(255,255,255,0.1); }
[data-theme='dark'] .shortcut-btn:hover { background: rgba(255,255,255,0.12); }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('feesChart'), {
    type: 'pie',
    data: {
        labels: ['Paid','Pending'],
        datasets: [{
            data: [<?= $paid ?>, <?= $pending_fees ?>],
            backgroundColor: ['rgba(0, 230, 118, 0.8)', 'rgba(255, 82, 82, 0.8)'],
            borderWidth: 0
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>
<?php include("../footer.php"); ?>