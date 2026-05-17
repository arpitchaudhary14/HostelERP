<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
$stats = [];
$stats['total_orders'] = $conn->query("SELECT COUNT(*) as total FROM laundry_requests")->fetch_assoc()['total'];
$stats['active_orders'] = $conn->query("SELECT COUNT(*) as total FROM laundry_requests WHERE status NOT IN ('Delivered')")->fetch_assoc()['total'];
$stats['active_subs'] = $conn->query("SELECT COUNT(*) as total FROM laundry_subscriptions WHERE status = 'Active'")->fetch_assoc()['total'];
$stats['total_revenue'] = $conn->query("SELECT SUM(amount) as total FROM laundry_payments WHERE status = 'Completed'")->fetch_assoc()['total'] ?? 0;
$status_counts = $conn->query("SELECT status, COUNT(*) as count FROM laundry_requests GROUP BY status");
$chart_labels = []; $chart_counts = [];
$status_colors = [
    'Collected' => '#6c63ff',
    'Washing' => '#29b6f6',
    'Drying' => '#ab47bc',
    'Ironing' => '#ffab40',
    'Ready' => '#00e676',
    'Delivered' => '#94a3b8'
];
$actual_colors = [];
while ($row = $status_counts->fetch_assoc()) {
    $chart_labels[] = $row['status'];
    $chart_counts[] = $row['count'];
    $actual_colors[] = $status_colors[$row['status']] ?? '#cccccc';
}
$recent_activity = $conn->query("SELECT r.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name FROM laundry_requests r JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 5");
include '../../header.php';
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal" style="padding:var(--space-xl);">
        <div class="d-flex align-items-center mb-2">
            <img src="/WebTechProject/assets/images/Cleanly_Logo.jpeg" height="45" class="me-3 rounded shadow-sm">
            <div>
                <h3 style="font-weight:700; color:var(--inner-heading); margin:0;">Cleanly Admin Dashboard</h3>
                <p class="text-muted" style="margin:0;">Laundary operations, subscription metrics & revenue tracking.</p>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3 reveal">
            <div class="stat-card stat-primary">
                <h5>Total Orders</h5>
                <h2 class="text-gradient"><?= $stats['total_orders'] ?></h2>
                <small class="text-muted">Lifetime requests</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="stat-card stat-success">
                <h5>Active Orders</h5>
                <h2 style="color:var(--accent-secondary);"><?= $stats['active_orders'] ?></h2>
                <small class="text-muted">Processing now</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="stat-card stat-danger">
                <h5>Active Subs</h5>
                <h2 style="color:var(--accent-danger);"><?= $stats['active_subs'] ?></h2>
                <small class="text-muted">Current subscribers</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="stat-card stat-info">
                <h5>Revenue</h5>
                <h2 style="color:var(--accent-info);">₹<?= number_format($stats['total_revenue'], 2) ?></h2>
                <small class="text-muted">Subscription earnings</small>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-8 reveal">
            <div class="glass-card-light h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 style="font-weight:600; margin:0;">Recent Activity</h5>
                    <a href="requests.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">Manage Orders</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="small text-muted">
                                <th>STUDENT</th>
                                <th>SERVICE</th>
                                <th>STATUS</th>
                                <th>TIME</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_activity->num_rows > 0): ?>
                                <?php while($row = $recent_activity->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['full_name']) ?></div>
                                        <small class="text-muted">#CL-<?= $row['id'] ?></small>
                                    </td>
                                    <td><?= $row['service_type'] ?></td>
                                    <td>
                                        <?php 
                                        $badge_class = 'bg-primary-subtle text-primary';
                                        if($row['status'] == 'Ready') $badge_class = 'bg-success-subtle text-success';
                                        if($row['status'] == 'Collected') $badge_class = 'bg-info-subtle text-info';
                                        ?>
                                        <span class="badge <?= $badge_class ?> px-3 py-2 rounded-pill small">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d M, H:i', strtotime($row['created_at'])) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">No recent orders found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4 reveal">
            <div class="glass-card-light h-100">
                <h5 class="mb-4" style="font-weight:600;">Order Status</h5>
                <div style="position: relative; height: 220px;">
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="mt-4">
                    <?php foreach($chart_labels as $index => $label): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2 small">
                        <span class="text-muted">
                            <i class="bi bi-circle-fill me-2" style="color: <?= $actual_colors[$index] ?>; font-size: 8px;"></i>
                            <?= $label ?>
                        </span>
                        <span class="fw-bold"><?= $chart_counts[$index] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 reveal">
            <div class="glass-card-light">
                <h5 class="mb-4" style="font-weight:600;">⚡ Quick Actions</h5>
                <div class="row g-3">
                    <div class="col-6">
                        <a href="plans.php" class="btn btn-gradient w-100 py-3 rounded-4 shadow-sm">
                            <i class="bi bi-plus-circle me-2"></i> Create Plan
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="requests.php" class="btn btn-outline-primary w-100 py-3 rounded-4">
                            <i class="bi bi-basket me-2"></i> Manage Orders
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="payments.php" class="btn btn-outline-info w-100 py-3 rounded-4">
                            <i class="bi bi-currency-rupee me-2"></i> Payment Logs
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="../../laundry/guidelines.php" class="btn btn-outline-secondary w-100 py-3 rounded-4">
                            <i class="bi bi-journal-text me-2"></i> Policy View
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 reveal">
            <div class="glass-card-light h-100 d-flex flex-column justify-content-center text-center p-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                <div class="mb-3">
                    <i class="bi bi-stars text-primary" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold">Ready to scale Cleanly?</h5>
                <p class="text-muted mb-4">View detailed analytics, student feedback, and service performance reports.</p>
                <button class="btn btn-primary rounded-pill px-5 py-2 fw-bold mx-auto">Generate Report</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            data: <?= json_encode($chart_counts) ?>,
            backgroundColor: <?= json_encode($actual_colors) ?>,
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        cutout: '75%'
    }
});
</script>
<style>
.stat-card small { font-weight: 600; letter-spacing: 0.5px; }
.btn-gradient {
    background: linear-gradient(135deg, var(--accent-primary), #4f46e5);
    color: white;
    border: none;
}
.btn-gradient:hover {
    filter: brightness(1.1);
    color: white;
}
</style>
<?php include '../../footer.php'; ?>