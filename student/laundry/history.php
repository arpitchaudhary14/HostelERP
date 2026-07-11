<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['user_id'])) { header("Location: ../../login.php"); exit();
}
$user_id = $_SESSION['user_id'];
$history = $conn->query("SELECT * FROM laundry_requests WHERE user_id = $user_id ORDER BY created_at DESC");
include '../../header.php';
?>
<div class="container mt-5 pt-4"> <div class="row mb-4 reveal"> <div class="col-12"> <h2 class="fw-bold text-gradient">Wash History</h2> <p class="text-muted">Review your past laundry requests and tracking.</p> </div> </div> <div class="glass-card-light p-0 overflow-hidden shadow-sm"> <table class="table table-hover align-middle mb-0 table-sticky"> <thead class=""> <tr class="small text-uppercase text-muted"> <th class="ps-4">Order ID</th> <th>Date</th> <th>Clothes</th> <th>Service</th> <th>Status</th> <th class="text-end pe-4">Collected/Return</th> </tr> </thead> <tbody> <?php while($row = $history->fetch_assoc()): ?> <tr> <td class="ps-4 fw-bold">#CL-<?php echo $row['id']; ?></td> <td class="small text-muted"><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td> <td><span class="badge bg-secondary-subtle text-secondary px-3"><?php echo $row['clothes_count']; ?> Items</span></td> <td><?php echo $row['service_type']; ?></td> <td> <?php $badge = 'bg-info'; if($row['status'] == 'Ready') $badge = 'bg-success'; if($row['status'] == 'Delivered') $badge = 'bg-secondary'; ?> <span class="badge <?php echo $badge; ?> px-3"><?php echo $row['status']; ?></span> </td> <td class="text-end pe-4 small"> <?php echo $row['pickup_date'] ? 'Returned: '.date('d M', strtotime($row['pickup_date'])) : 'Pending Pickup'; ?> </td> </tr> <?php endwhile; ?> <?php if($history->num_rows == 0): ?> <tr><td colspan="6" class="text-center py-5 text-muted italic">You haven't made any laundry requests yet.</td></tr> <?php endif; ?> </tbody> </table> </div>
</div>
<?php include '../../footer.php'; ?>