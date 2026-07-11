<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit();
}
$plans_result = $conn->query("SELECT * FROM laundry_plans ORDER BY price ASC");
include '../../header.php';
?>
<div class="container mt-5 pt-4"> <div class="text-center mb-5 reveal"> <img src="/assets/images/Cleanly_Logo.jpeg" height="70" class="mb-3 rounded-3 shadow-sm"> <h2 class="fw-bold text-gradient">Choose Your Cleanly Plan</h2> <p class="text-muted">High-quality laundry services delivered right to your hostel wing.</p> </div> <div class="row g-4 justify-content-center"> <?php while($plan = $plans_result->fetch_assoc()): ?> <div class="col-md-4 reveal"> <div class="glass-card-light h-100 p-4 text-center d-flex flex-column" style="transition: transform 0.3s ease;"> <div class="mb-3"> <i class="bi bi-box-seam fs-1 text-primary"></i> </div> <h4 class="fw-bold"><?php echo htmlspecialchars($plan['name']); ?></h4> <div class="my-3"> <span class="fs-1 fw-bold text-primary">₹<?php echo number_format($plan['price'], 0); ?></span> <span class="text-muted">/ <?php echo $plan['duration_days']; ?> Days</span> </div> <ul class="list-unstyled mb-4 flex-grow-1 text-start px-3"> <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> <?php echo $plan['clothes_limit']; ?> Total Clothes</li> <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Free Doorstep Pickup</li> <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Premium Detergent Wash</li> <?php if($plan['price'] > 500): ?> <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Express 24h Delivery</li> <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> Steam Ironing Included</li> <?php endif; ?> </ul> <button class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 10px;" onclick="subscribePlan(<?php echo $plan['id']; ?>, '<?php echo $plan['name']; ?>', <?php echo $plan['price']; ?>)"> Get Started </button> </div> </div> <?php endwhile; ?> </div>
</div>
<div class="modal fade" id="paymentModal" tabindex="-1"> <div class="modal-dialog modal-dialog-centered"> <div class="modal-content border-0 glass-card-light p-4 text-center"> <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div> <h5 class="fw-bold">Initializing Secure Checkout...</h5> <p class="text-muted mb-0">Redirecting you to HostelERP Secure Pay gateway.</p> </div> </div>
</div>
<script>
function subscribePlan(planId, planName, price) { const modal = new bootstrap.Modal(document.getElementById('paymentModal')); modal.show(); setTimeout(() => { window.location.href = `../payment_gateway.php?type=laundry&id=${planId}&amount=${price}&item=${encodeURIComponent(planName)}`; }, 1500);
}
</script>
<style>
.glass-card-light:hover { transform: translateY(-10px); border: 1px solid var(--accent-primary) !important;
}
</style>
<?php include '../../footer.php'; ?>