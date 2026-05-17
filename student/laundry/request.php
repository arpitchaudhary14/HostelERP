<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$sub_query = "SELECT * FROM laundry_subscriptions WHERE user_id = ? AND status = 'Active' LIMIT 1";
$stmt = $conn->prepare($sub_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$subscription = $stmt->get_result()->fetch_assoc();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clothes_count = intval($_POST['clothes_count']);
    $service_type = $_POST['service_type'];
    $notes = $_POST['notes'];
    if (!$subscription) {
        $error = "No active subscription found. Please subscribe to a plan first.";
    } elseif ($clothes_count <= 0) {
        $error = "Please enter a valid number of clothes.";
    } elseif ($subscription['remaining_clothes'] < $clothes_count) {
        $error = "Insufficient balance! You only have " . $subscription['remaining_clothes'] . " clothes left in your plan.";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO laundry_requests (user_id, clothes_count, service_type, notes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $user_id, $clothes_count, $service_type, $notes);
            $stmt->execute();
            $stmt = $conn->prepare("UPDATE laundry_subscriptions SET remaining_clothes = remaining_clothes - ? WHERE id = ?");
            $stmt->bind_param("ii", $clothes_count, $subscription['id']);
            $stmt->execute();
            $conn->commit();
            $_SESSION['req_success'] = "Laundry request submitted! Please drop your clothes at the wing collection point.";
            header("Location: dashboard.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to submit request. Please try again.";
        }
    }
}
include '../../header.php';
?>
<div class="container mt-5 pt-4">
    <div class="row justify-content-center reveal">
        <div class="col-md-6">
            <div class="glass-card-light p-4">
                <div class="d-flex align-items-center mb-4">
                    <img src="/WebTechProject/assets/images/Cleanly_Logo.jpeg" height="60" class="me-3 rounded-3 shadow-sm">
                    <div>
                        <h3 class="fw-bold mb-0">Request New Wash</h3>
                        <p class="text-muted mb-0 small">Balance: <span class="fw-bold text-primary"><?php echo $subscription ? $subscription['remaining_clothes'] : 0; ?> clothes remaining</span></p>
                    </div>
                </div>
                <?php if($error): ?>
                    <div class="alert alert-danger border-0 small"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" class="mt-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">HOW MANY CLOTHES?</label>
                        <div class="input-group">
                            <input type="number" name="clothes_count" class="form-control form-control-lg" placeholder="0" required>
                            <span class="input-group-text">Pieces</span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">SERVICE TYPE</label>
                        <select name="service_type" class="form-select form-select-lg" required>
                            <option value="Wash & Fold">Wash & Fold (Standard)</option>
                            <option value="Wash & Iron">Wash & Iron (Premium)</option>
                            <option value="Dry Clean">Dry Clean (Extra Credits)</option>
                            <option value="Only Iron">Only Ironing</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-muted">SPECIAL NOTES (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Use cold water for the white shirt..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow" style="border-radius: 12px;">
                        <i class="bi bi-send-fill me-2"></i> Submit Request
                    </button>
                    <a href="dashboard.php" class="btn btn-link w-100 mt-2 text-muted text-decoration-none small">Cancel & Go Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
.icon-box-md {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.5rem;
}
</style>
<?php include '../../footer.php'; ?>