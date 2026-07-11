<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['warden', 'admin'])) { header("Location: ../../login.php"); exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deliver'])) { $req_id = $_POST['req_id']; $stmt = $conn->prepare("UPDATE laundry_requests SET status = 'Delivered' WHERE id = ?"); $stmt->bind_param("i", $req_id); if ($stmt->execute()) { $message = "Order #CL-$req_id marked as Delivered."; }
}
$requests = $conn->query("SELECT r.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name, u.email FROM laundry_requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'Ready' ORDER BY r.pickup_date ASC");
include '../../header.php';
?>
<div class="container mt-5 pt-4"> <div class="row mb-4 reveal"> <div class="col-12"> <h2 class="fw-bold"><i class="bi bi-check-all me-2 text-success"></i> Ready for Pickup</h2> <p class="text-muted">Orders that are ready to be returned to students.</p> </div> </div> <?php if($message): ?> <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $message; ?></div> <?php endif; ?> <div class="glass-card-light p-0 overflow-hidden"> <table class="table table-hover align-middle mb-0 table-sticky"> <thead class=""> <tr class="small text-muted"> <th class="ps-4">Student</th> <th>Order Info</th> <th>Ready Since</th> <th class="text-end pe-4">Actions</th> </tr> </thead> <tbody> <?php while($req = $requests->fetch_assoc()): ?> <tr> <td class="ps-4"> <div class="fw-bold"><?php echo htmlspecialchars($req['full_name']); ?></div> <div class="small text-muted"><?php echo $req['email']; ?></div> </td> <td> <span class="badge bg-success-subtle text-success px-3"><?php echo $req['clothes_count']; ?> Pieces</span> <div class="small text-primary fw-bold mt-1"><?php echo $req['service_type']; ?></div> </td> <td class="small text-muted"><?php echo $req['pickup_date'] ? date('d M', strtotime($req['pickup_date'])) : 'N/A'; ?></td> <td class="text-end pe-4"> <form method="POST"> <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>"> <button type="submit" name="deliver" class="btn btn-sm btn-success px-3">Mark Delivered</button> </form> </td> </tr> <?php endwhile; ?> <?php if($requests->num_rows == 0): ?> <tr><td colspan="4" class="text-center py-5 text-muted">No orders are ready for delivery right now.</td></tr> <?php endif; ?> </tbody> </table> </div>
</div>
<?php include '../../footer.php'; ?>