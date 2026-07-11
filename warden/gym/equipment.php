<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$equipment = get_gym_equipment($conn);
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <div class="d-flex justify-content-between align-items-center flex-wrap"> <div> <h2 class="fw-bold text-gradient mb-1">MatrixFit Asset Status</h2> <p class="text-muted">Live equipment health and maintenance tracking.</p> </div> <div class="d-flex gap-2"> <span class="badge bg-success-subtle text-success rounded-pill px-3">Operational</span> <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Service Required</span> </div> </div> </div> <div class="row g-4"> <?php if(mysqli_num_rows($equipment) > 0): ?> <?php while($e = mysqli_fetch_assoc($equipment)): ?> <div class="col-md-3 reveal"> <div class="glass-card-light h-100 p-4 text-center"> <div class="equipment-icon mb-3"> <i class="bi bi-cpu h2"></i> </div> <h5 class="fw-bold mb-3"><?= htmlspecialchars($e['name']) ?></h5> <?php $status_class = "bg-success"; $status_text = "Functional"; if($e['status'] == 'under_maintenance') { $status_class = "bg-warning text-dark"; $status_text = "In Service"; } if($e['status'] == 'out_of_order') { $status_class = "bg-danger"; $status_text = "Down"; } ?> <span class="badge <?= $status_class ?> w-100 py-2 rounded-pill"><?= $status_text ?></span> <div class="mt-3 small text-start"> <div class="text-muted"><i class="bi bi-calendar-check me-2"></i>Last: <?= $e['last_maintenance'] ?: 'Never' ?></div> <div class="text-muted"><i class="bi bi-calendar-event me-2"></i>Next: <?= $e['next_maintenance'] ?: 'N/A' ?></div> </div> </div> </div> <?php endwhile; ?> <?php else: ?> <div class="col-12 text-center py-5 reveal"> <div class="glass-card-light"> <i class="bi bi-tools h1 text-muted"></i> <p class="mt-3 text-muted">No assets found in registry.</p> </div> </div> <?php endif; ?> </div>
</div>
<style>
.equipment-icon { width: 60px; height: 60px; margin: 0 auto; background: var(--glass-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-info);
}
</style>
<?php include("../../footer.php"); ?>