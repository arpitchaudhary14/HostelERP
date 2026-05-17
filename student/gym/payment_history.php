<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$payments = mysqli_query($conn, "SELECT s.*, p.name, p.price 
                                  FROM gym_subscriptions s 
                                  JOIN gym_plans p ON s.plan_id = p.id 
                                  WHERE s.user_id = $user_id 
                                  ORDER BY s.start_date DESC");

include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <h2 class="fw-bold text-gradient mb-0">Gym Payment History</h2>
        <p class="text-muted mb-0">Record of all your membership subscriptions and payments.</p>
    </div>
    <div class="glass-card-light reveal">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Plan Name</th>
                        <th>Amount Paid</th>
                        <th>Period</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($payments)): ?>
                    <tr>
                        <td><span class="badge bg-light text-dark border">#MF-PAY-<?= $p['id'] ?></span></td>
                        <td><div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div></td>
                        <td><span class="fw-bold text-success">₹<?= number_format($p['price'], 2) ?></span></td>
                        <td>
                            <small class="text-muted">
                                <?= date('d M, Y', strtotime($p['start_date'])) ?> - 
                                <?= date('d M, Y', strtotime($p['end_date'])) ?>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-<?= ($p['status']=='active'?'success':'secondary') ?> rounded-pill px-3">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($payments) == 0): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No gym payment history found. Start your fitness journey today!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../footer.php"); ?>