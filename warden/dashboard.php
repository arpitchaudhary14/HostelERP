<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
$notice_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM notices")
)['total'] ?? 0;
$complaint_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints")
)['total'] ?? 0;
$leave_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests")
)['total'] ?? 0;
if ($role === "student") {
    $pending_complaints = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE student_id='$user_id' AND status='Pending'")
    )['total'] ?? 0;
    $pending_leaves = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests WHERE student_id='$user_id' AND status='Pending'")
    )['total'] ?? 0;
} else {
    $pending_complaints = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) as total FROM complaints WHERE status='Pending'")
    )['total'] ?? 0;
    $pending_leaves = mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) as total FROM leave_requests WHERE status='Pending'")
    )['total'] ?? 0;
}
$paid = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Paid'")
)['total'] ?? 0;
$pending_fees = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(amount) as total FROM fees WHERE status='Pending'")
)['total'] ?? 0;
$complaints = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
    SUM(status='Pending') as pending,
    SUM(status='Approved') as approved,
    SUM(status='Rejected') as rejected
    FROM complaints
"));
$attendance = mysqli_fetch_assoc(mysqli_query($conn,"
    SELECT 
    SUM(status='present') as present,
    SUM(status='absent') as absent,
    SUM(status='leave') as leave_days
    FROM attendance
"));
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
<div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl) var(--space-xl);">
    <h3 style="font-weight:700; color:#1a1a2e;">Welcome, <?= htmlspecialchars($full_name) ?> 👋</h3>
    <p style="color:#666; margin:0;">System analytics overview &mdash; here's what's happening today.</p>
</div>
<div class="row g-4 mb-4">
<div class="col-md-4 reveal">
<div class="stat-card stat-primary">
<h5>Leaves</h5>
<h2 class="text-gradient" data-count="<?= $leave_count ?>"><?= $leave_count ?></h2>
</div>
</div>
<div class="col-md-4 reveal">
<div class="stat-card stat-danger">
<h5>Complaints</h5>
<h2 style="color:var(--accent-danger);" data-count="<?= $complaint_count ?>"><?= $complaint_count ?></h2>
</div>
</div>
<div class="col-md-4 reveal">
<div class="stat-card stat-success">
<h5>Notices</h5>
<h2 style="color:var(--accent-secondary);" data-count="<?= $notice_count ?>"><?= $notice_count ?></h2>
</div>
</div>
</div>
<div class="row g-4 mb-4">
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<h6 class="text-center" style="font-weight:600; color:#1a1a2e;">Fees Collection</h6>
<canvas id="feesChart"></canvas>
</div>
</div>
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<h6 class="text-center" style="font-weight:600; color:#1a1a2e;">Complaint Stats</h6>
<canvas id="complaintChart"></canvas>
</div>
</div>
<div class="col-md-4 reveal">
<div class="glass-card-light" style="padding:var(--space-lg);">
<h6 class="text-center" style="font-weight:600; color:#1a1a2e;">Attendance</h6>
<canvas id="attendanceChart"></canvas>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.plugins.legend.labels.padding = 16;
new Chart(document.getElementById('feesChart'), {
    type: 'bar',
    data: {
        labels: ['Paid','Pending'],
        datasets: [{
            data: [<?= $paid ?>, <?= $pending_fees ?>],
            backgroundColor: [
                'rgba(0, 230, 118, 0.75)',
                'rgba(255, 82, 82, 0.75)'
            ],
            borderRadius: 8,
            borderSkipped: false
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
            x: { grid: { display: false } }
        }
    }
});
new Chart(document.getElementById('complaintChart'), {
    type: 'doughnut',
    data: {
        labels: ['Pending','Approved','Rejected'],
        datasets: [{
            data: [
                <?= $complaints['pending'] ?? 0 ?>,
                <?= $complaints['approved'] ?? 0 ?>,
                <?= $complaints['rejected'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgba(255, 171, 64, 0.8)',
                'rgba(0, 230, 118, 0.8)',
                'rgba(255, 82, 82, 0.8)'
            ],
            borderWidth: 0,
            cutout: '65%'
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});
new Chart(document.getElementById('attendanceChart'), {
    type: 'doughnut',
    data: {
        labels: ['Present','Absent','Leave'],
        datasets: [{
            data: [
                <?= $attendance['present'] ?? 0 ?>,
                <?= $attendance['absent'] ?? 0 ?>,
                <?= $attendance['leave_days'] ?? 0 ?>
            ],
            backgroundColor: [
                'rgba(0, 230, 118, 0.8)',
                'rgba(255, 82, 82, 0.8)',
                'rgba(64, 196, 255, 0.8)'
            ],
            borderWidth: 0,
            cutout: '65%'
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});
</script>
<?php include("../footer.php"); ?>