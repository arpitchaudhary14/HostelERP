<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
$payments = $conn->query("SELECT p.*, CONCAT(u.first_name, ' ', COALESCE(u.last_name, '')) as full_name, u.email 
                          FROM laundry_payments p 
                          JOIN users u ON p.user_id = u.id 
                          ORDER BY p.created_at DESC");
include '../../header.php';
?>
<div class="container mt-5 pt-4">
    <div class="row mb-4 reveal">
        <div class="col-12">
            <h2 class="fw-bold text-gradient">Cleanly Payment Records</h2>
            <p class="text-muted">History of all laundry subscription transactions.</p>
        </div>
    </div>
    <div class="glass-card-light p-0 overflow-hidden shadow-sm">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-uppercase text-muted">
                    <th class="ps-4">Student</th>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($pay = $payments->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold"><?php echo htmlspecialchars($pay['full_name']); ?></div>
                        <div class="text-muted small"><?php echo $pay['email']; ?></div>
                    </td>
                    <td><code class="small"><?php echo $pay['transaction_id'] ?: 'N/A'; ?></code></td>
                    <td class="fw-bold text-primary">₹<?php echo number_format($pay['amount'], 2); ?></td>
                    <td><?php echo $pay['payment_method']; ?></td>
                    <td>
                        <?php 
                        $badge = 'bg-warning';
                        if($pay['status'] == 'Completed') $badge = 'bg-success';
                        if($pay['status'] == 'Failed') $badge = 'bg-danger';
                        ?>
                        <span class="badge <?php echo $badge; ?> px-3"><?php echo $pay['status']; ?></span>
                    </td>
                    <td class="text-end pe-4 text-muted small">
                        <?php echo date('d M Y, H:i', strtotime($pay['created_at'])); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($payments->num_rows == 0): ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted italic">No payment records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../../footer.php'; ?>