<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$equipment = get_gym_equipment($conn);
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <div class="d-flex justify-content-between align-items-center flex-wrap"> <div> <h2 class="fw-bold text-gradient mb-1">Equipment Status</h2> <p class="text-muted">Check if your favorite machines are ready for use.</p> </div> <div class="d-flex gap-2"> <span class="badge bg-success-subtle text-success rounded-pill px-3">Available</span> <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Maintenance</span> <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Out of Order</span> </div> </div> </div> <div class="row g-4"> <?php if(mysqli_num_rows($equipment) > 0): ?> <?php while($e = mysqli_fetch_assoc($equipment)): ?> <div class="col-md-3 reveal"> <div class="glass-card-light h-100 p-4 text-center"> <div class="equipment-icon mb-3"> <?php $icon = "bi-gear"; if(stripos($e['name'], 'treadmill') !== false) $icon = "bi-speedometer2"; if(stripos($e['name'], 'cycle') !== false) $icon = "bi-bicycle"; if(stripos($e['name'], 'bench') !== false) $icon = "bi-horizontal-distribute"; if(stripos($e['name'], 'dumb') !== false) $icon = "bi-p-circle"; ?> <i class="bi <?= $icon ?> h2"></i> </div> <h5 class="fw-bold mb-3"><?= htmlspecialchars($e['name']) ?></h5> <?php $status_class = "bg-success"; $status_text = "Available"; if($e['status'] == 'under_maintenance') { $status_class = "bg-warning text-dark"; $status_text = "Maintenance"; } if($e['status'] == 'out_of_order') { $status_class = "bg-danger"; $status_text = "Out of Order"; } ?> <span class="badge <?= $status_class ?> w-100 py-2 rounded-pill"><?= $status_text ?></span> <?php if($e['next_maintenance']): ?> <div class="mt-3 small text-muted"> Next Service: <?= date('d M', strtotime($e['next_maintenance'])) ?> </div> <?php endif; ?> </div> </div> <?php endwhile; ?> <?php else: ?> <div class="col-12 text-center py-5 reveal"> <div class="glass-card-light"> <i class="bi bi-tools h1 text-muted"></i> <p class="mt-3 text-muted">Equipment list is being initialized.</p> </div> </div> <?php endif; ?> </div>
</div>
<style>
.equipment-icon { width: 60px; height: 60px; margin: 0 auto; background: var(--glass-bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-primary);
}
</style>
<?php include("../../footer.php"); ?>