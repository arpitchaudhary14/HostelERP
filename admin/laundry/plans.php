<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_plan'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $limit = $_POST['clothes_limit'];
    $duration = $_POST['duration_days'];
    $desc = $_POST['description'];
    if (isset($_POST['plan_id']) && !empty($_POST['plan_id'])) {
        $stmt = $conn->prepare("UPDATE laundry_plans SET name=?, price=?, clothes_limit=?, duration_days=?, description=? WHERE id=?");
        $stmt->bind_param("sdiisi", $name, $price, $limit, $duration, $desc, $_POST['plan_id']);
    } else {
        $stmt = $conn->prepare("INSERT INTO laundry_plans (name, price, clothes_limit, duration_days, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdiis", $name, $price, $limit, $duration, $desc);
    }
    if ($stmt->execute()) {
        $message = "Plan saved successfully!";
    }
}
$plans = $conn->query("SELECT * FROM laundry_plans ORDER BY price ASC");
include '../../header.php';
?>
<div class="container mt-5 pt-4">
    <div class="row mb-4 reveal">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-gradient">Cleanly Service Plans</h2>
                <p class="text-muted">Manage laundry subscription tiers and pricing.</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal" onclick="clearForm()">
                <i class="bi bi-plus-lg me-2"></i> Create New Plan
            </button>
        </div>
    </div>
    <?php if($message): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $message; ?></div>
    <?php endif; ?>
    <div class="row g-4">
        <?php while($plan = $plans->fetch_assoc()): ?>
            <div class="col-md-4 reveal">
                <div class="glass-card-light h-100 p-4 shadow-sm border-0" style="border-top: 4px solid var(--accent-primary) !important;">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h4 class="fw-bold mb-0"><?php echo htmlspecialchars($plan['name']); ?></h4>
                        <button class="btn btn-sm btn-outline-secondary" onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan)); ?>)">Edit</button>
                    </div>
                    <div class="mb-3">
                        <span class="fs-2 fw-bold text-primary">₹<?php echo number_format($plan['price'], 0); ?></span>
                        <span class="text-muted">/ <?php echo $plan['duration_days']; ?> Days</span>
                    </div>
                    <ul class="list-unstyled mb-4 small">
                        <li class="mb-2"><i class="bi bi-check2-circle text-success me-2"></i> <?php echo $plan['clothes_limit']; ?> Clothes Limit</li>
                        <li class="text-muted"><?php echo htmlspecialchars($plan['description']); ?></li>
                    </ul>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card-light border-0 p-3">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="planForm">
                    <input type="hidden" name="plan_id" id="plan_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Plan Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Price (₹)</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold">Duration (Days)</label>
                            <input type="number" name="duration_days" id="duration" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Clothes Limit</label>
                        <input type="number" name="clothes_limit" id="limit" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" name="save_plan" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 10px;">Save Plan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function clearForm() {
    document.getElementById('planForm').reset();
    document.getElementById('plan_id').value = '';
    document.getElementById('modalTitle').innerText = 'Create New Plan';
}
function editPlan(plan) {
    document.getElementById('plan_id').value = plan.id;
    document.getElementById('name').value = plan.name;
    document.getElementById('price').value = plan.price;
    document.getElementById('duration').value = plan.duration_days;
    document.getElementById('limit').value = plan.clothes_limit;
    document.getElementById('description').value = plan.description;
    document.getElementById('modalTitle').innerText = 'Edit Plan';
    new bootstrap.Modal(document.getElementById('planModal')).show();
}
</script>
<?php include '../../footer.php'; ?>