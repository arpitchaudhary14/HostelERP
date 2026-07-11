<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit();
}
$user_id = $_SESSION['user_id'];
$sub_query = "SELECT s.*, p.name as plan_name, p.clothes_limit FROM laundry_subscriptions s JOIN laundry_plans p ON s.plan_id = p.id WHERE s.user_id = ? AND s.status = 'Active' LIMIT 1";
$stmt = $conn->prepare($sub_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();
$req_query = "SELECT * FROM laundry_requests WHERE user_id = ? AND status != 'Delivered' ORDER BY created_at DESC";
$stmt = $conn->prepare($req_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$active_requests = $stmt->get_result();
$total_washes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laundry_requests WHERE user_id = $user_id"))['total'] ?? 0;
$total_clothes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(clothes_count) as total FROM laundry_requests WHERE user_id = $user_id"))['total'] ?? 0;
include '../../header.php';
?>
<div class="container mt-5 pt-4"> <div class="glass-card-light mb-5 reveal p-4 border-0 shadow-lg hero-gradient" style="border-left: 6px solid #0ea5e9 !important; border-radius: 24px;"> <div class="row align-items-center"> <div class="col-md-7"> <div class="d-flex align-items-center mb-3"> <img src="/assets/images/Cleanly_Logo.jpeg" height="60" class="me-3 rounded-4 shadow-sm"> <div> <h2 class="fw-bold mb-0">Cleanly Dashboard</h2> <span class="badge bg-info-subtle text-info px-3 rounded-pill small">Student Premium Account</span> </div> </div> <p class="text-muted lead mb-4">Effortless laundry management for a fresher hostel life. Track, request, and manage your subscriptions in one place.</p> <div class="d-flex gap-3"> <a href="request.php" class="btn btn-primary px-4 py-2 rounded-pill fw-bold shadow-sm"> <i class="bi bi-plus-lg me-2"></i> New Request </a> <a href="member_card.php" class="btn btn-outline-info px-4 py-2 rounded-pill fw-bold"> <i class="bi bi-card-heading me-2"></i> My Pass </a> </div> </div> <div class="col-md-5 d-none d-md-block text-end"> <div class="stat-main-circle"> <div class="stat-inner"> <h1 class="fw-900 text-primary mb-0"><?= $total_washes ?></h1> <small class="text-muted text-uppercase fw-bold">Total Washes</small> </div> </div> </div> </div> </div> <div class="row g-4 mb-5"> <div class="col-md-3 reveal"> <div class="stat-card-modern p-4"> <div class="icon-box bg-primary-soft text-primary mb-3"> <i class="bi bi-water"></i> </div> <h3 class="fw-bold mb-1"><?= $total_clothes ?></h3> <p class="text-muted small mb-0 font-weight-600">Total Clothes Washed</p> </div> </div> <div class="col-md-3 reveal"> <div class="stat-card-modern p-4"> <div class="icon-box bg-success-soft text-success mb-3"> <i class="bi bi-check2-all"></i> </div> <h3 class="fw-bold mb-1"><?= $subscription ? $subscription['remaining_clothes'] : '0' ?></h3> <p class="text-muted small mb-0 font-weight-600">Clothes Balance</p> </div> </div> <div class="col-md-3 reveal"> <div class="stat-card-modern p-4"> <div class="icon-box bg-warning-soft text-warning mb-3"> <i class="bi bi-clock-history"></i> </div> <h3 class="fw-bold mb-1"><?= $active_requests->num_rows ?></h3> <p class="text-muted small mb-0 font-weight-600">Active Requests</p> </div> </div> <div class="col-md-3 reveal"> <div class="stat-card-modern p-4"> <div class="icon-box bg-info-soft text-info mb-3"> <i class="bi bi-shield-check"></i> </div> <h3 class="fw-bold mb-1">Active</h3> <p class="text-muted small mb-0 font-weight-600">Hygiene Status</p> </div> </div> </div> <div class="row g-4 mb-5"> <div class="col-md-4 reveal"> <div class="glass-card-light h-100 p-4 border-0 shadow-sm rounded-4"> <div class="d-flex justify-content-between align-items-center mb-4"> <h5 class="fw-bold mb-0">Usage Journey</h5> <i class="bi bi-graph-up-arrow text-primary"></i> </div> <?php if ($subscription): ?> <div class="text-center mb-4"> <?php $percent = ($subscription['remaining_clothes'] / $subscription['clothes_limit']) * 100; ?> <div class="circular-progress-box mx-auto mb-3" style="--percent: <?= 100 - $percent ?>%"> <div class="inner-circle"> <h4 class="fw-bold mb-0 text-primary"><?= round(100 - $percent) ?>%</h4> </div> </div> <h6 class="fw-bold"><?= htmlspecialchars($subscription['plan_name']); ?></h6> <p class="text-muted small">Subscription ends on <?= date('d M, Y', strtotime($subscription['end_date'])) ?></p> </div> <div class="d-flex justify-content-between small text-muted mb-2"> <span>Used: <?= $subscription['clothes_limit'] - $subscription['remaining_clothes'] ?></span> <span>Total: <?= $subscription['clothes_limit'] ?></span> </div> <div class="progress custom-progress"> <div class="progress-bar bg-primary" style="width: <?= 100 - $percent ?>%"></div> </div> <?php else: ?> <div class="text-center py-5"> <div class="mb-3 text-muted opacity-25"><i class="bi bi-droplet-half" style="font-size: 4rem;"></i></div> <p class="text-muted">No active subscription found.</p> <a href="plans.php" class="btn btn-sm btn-primary px-4 rounded-pill">View Plans</a> </div> <?php endif; ?> </div> </div> <div class="col-md-8 reveal"> <div class="glass-card-light p-4 h-100 border-0 shadow-sm rounded-4"> <div class="d-flex justify-content-between align-items-center mb-4"> <h5 class="fw-bold mb-0">Active Wash Requests</h5> <a href="history.php" class="text-primary text-decoration-none small fw-bold">View History <i class="bi bi-arrow-right"></i></a> </div> <?php if ($active_requests->num_rows > 0): ?> <div class="table-responsive"> <table class="table table-hover align-middle table-sticky"> <thead> <tr class="text-muted small border-0"> <th class="border-0">ORDER ID</th> <th class="border-0">CLOTHES</th> <th class="border-0">STATUS</th> <th class="border-0">EST. RETURN</th> </tr> </thead> <tbody> <?php while($req = $active_requests->fetch_assoc()): ?> <tr> <td class="fw-bold">#CL-<?= $req['id']; ?></td> <td><span class="badge border px-3"><?= $req['clothes_count']; ?> Items</span></td> <td> <?php $status_class = 'bg-info'; $status_icon = 'bi-gear'; if($req['status'] == 'Ready') { $status_class = 'bg-success'; $status_icon = 'bi-check2-circle'; } if($req['status'] == 'Washing') { $status_class = 'bg-primary'; $status_icon = 'bi-droplet-half'; } ?> <span class="badge <?= $status_class; ?> px-3 py-2 rounded-pill"> <i class="bi <?= $status_icon; ?> me-1"></i> <?= $req['status']; ?> </span> </td> <td class="text-muted small fw-600"><?= $req['pickup_date'] ? date('d M', strtotime($req['pickup_date'])) : 'In Progress'; ?></td> </tr> <?php endwhile; ?> </tbody> </table> </div> <?php else: ?> <div class="text-center py-5"> <div class="mb-3 text-muted opacity-25"><i class="bi bi-box2-heart" style="font-size: 3.5rem;"></i></div> <p class="text-muted italic">Everything is clean! No active requests.</p> </div> <?php endif; ?> </div> </div> </div> <div class="row reveal mb-5"> <div class="col-md-12"> <div class="glass-card-light p-4 rounded-4 shadow-sm border-0 d-flex align-items-center justify-content-between help-card"> <div class="d-flex align-items-center"> <div class="icon-box-sm me-3"><i class="bi bi-info-circle"></i></div> <div> <h6 class="fw-bold mb-0">Laundry Guidelines & Tips</h6> <p class="text-muted small mb-0">Learn how to separate clothes and use the digital pass at collection.</p> </div> </div> <a href="../../laundry/guidelines.php" class="btn btn-dark btn-sm px-4 rounded-pill">Read Rules</a> </div> </div> </div>
</div>
<style>
.fw-900 { font-weight: 900; }
.fw-600 { font-weight: 600; }
.hero-gradient { background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(224,242,254,0.9)); }
[data-theme='dark'] .hero-gradient { background: linear-gradient(135deg, rgba(30,41,59,0.5), rgba(15,23,42,0.8)); }
.stat-main-circle { width: 160px; height: 160px; border: 8px solid #0ea5e9; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 15px 35px rgba(14, 165, 233, 0.1); position: relative;
}
[data-theme='dark'] .stat-main-circle { background: #1e293b; }
.stat-main-circle::after { content: ''; position: absolute; inset: -15px; border: 2px dashed #0ea5e9; border-radius: 50%; opacity: 0.3; animation: rotate 20s linear infinite;
}
.stat-card-modern { border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: all 0.3s ease;
}
[data-theme='dark'] .stat-card-modern { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); }
.stat-card-modern:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
.icon-box { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.icon-box-sm { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.bg-primary-soft { background: #eff6ff; }
.bg-success-soft { background: #f0fdf4; }
.bg-warning-soft { beb; }
.bg-info-soft { background: #f0f9ff; }
[data-theme='dark'] .bg-primary-soft { background: rgba(13,110,253,0.15); }
[data-theme='dark'] .bg-success-soft { background: rgba(25,135,84,0.15); }
[data-theme='dark'] .bg-warning-soft { background: rgba(255,193,7,0.15); }
[data-theme='dark'] .bg-info-soft { background: rgba(13,202,240,0.15); }
.circular-progress-box { width: 120px; height: 120px; background: conic-gradient(#0ea5e9 var(--percent), #eef2ff 0); border-radius: 50%; display: flex; align-items: center; justify-content: center;
}
[data-theme='dark'] .circular-progress-box { background: conic-gradient(#0ea5e9 var(--percent), rgba(255,255,255,0.1) 0); }
.inner-circle { width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
[data-theme='dark'] .inner-circle { background: #0f172a; }
.custom-progress { height: 8px; border-radius: 10px; background: #eef2ff; }
[data-theme='dark'] .custom-progress { background: rgba(255,255,255,0.1); }
.help-card { background: #f8fafc; }
[data-theme='dark'] .help-card { background: rgba(255,255,255,0.05); }
@keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
<?php include '../../footer.php'; ?>