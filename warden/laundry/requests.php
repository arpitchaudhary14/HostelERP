<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['warden', 'admin'])) {
    header("Location: ../../login.php");
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['collect'])) {
    $req_id = $_POST['req_id'];
    $stmt = $conn->prepare("UPDATE laundry_requests SET status = 'Collected' WHERE id = ?");
    $stmt->bind_param("i", $req_id);
    if ($stmt->execute()) {
        $message = "Order #CL-$req_id marked as Collected.";
    }
}
if ($message) {
}
$requests = $conn->query("SELECT r.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name, u.email 
                          FROM laundry_requests r 
                          JOIN users u ON r.user_id = u.id 
                          ORDER BY r.created_at DESC");

include '../../header.php';
?>
<div class="container mt-5 pt-4">
    <div class="row mb-4 reveal">
        <div class="col-12">
            <h2 class="fw-bold"><i class="bi bi-basket-fill me-2 text-primary"></i> Laundry Collection</h2>
            <p class="text-muted">Receive and verify clothes from students.</p>
        </div>
    </div>
    <div class="glass-card-light p-0 overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted">
                    <th class="ps-4">Student</th>
                    <th>Order Info</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($req = $requests->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold"><?php echo htmlspecialchars($req['full_name']); ?></div>
                        <div class="small text-muted"><?php echo $req['email']; ?></div>
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary"><?php echo $req['clothes_count']; ?> Pieces</span>
                        <div class="small text-primary fw-bold mt-1"><?php echo $req['service_type']; ?></div>
                    </td>
                    <td><span class="badge bg-info px-3"><?php echo $req['status']; ?></span></td>
                    <td class="text-end pe-4">
                        <?php if($req['status'] == 'Collected'): ?>
                             <button class="btn btn-sm btn-success px-3 disabled">Collected</button>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="req_id" value="<?php echo $req['id']; ?>">
                                <button type="submit" name="collect" class="btn btn-sm btn-primary px-3">Mark Collected</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($requests->num_rows == 0): ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">No pending laundry requests.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../footer.php'; ?>