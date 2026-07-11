<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$payments = mysqli_query($conn, "SELECT p.*, u.full_name, pl.name as plan_name FROM gym_payments p JOIN gym_subscriptions s ON p.subscription_id = s.id JOIN users u ON s.user_id = u.id JOIN gym_plans pl ON s.plan_id = pl.id ORDER BY p.payment_date DESC");
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <h3 style="font-weight:700; margin:0;">MatrixFit Payments</h3> <p style="margin:0;">Track all gym revenue and membership transactions.</p> </div> <div class="glass-card-light reveal"> <div class="table-responsive"> <table class="table table-hover align-middle table-sticky"> <thead> <tr> <th>Transaction ID</th> <th>Member</th> <th>Plan</th> <th>Amount</th> <th>Date</th> <th>Method</th> <th>Status</th> </tr> </thead> <tbody> <?php while($row = mysqli_fetch_assoc($payments)){ ?> <tr> <td class="text-muted small">#TXN-GYM-<?= $row['id'] ?></td> <td class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></td> <td><?= htmlspecialchars($row['plan_name']) ?></td> <td class="fw-bold">₹<?= number_format($row['amount'], 2) ?></td> <td><?= date('d M, Y', strtotime($row['payment_date'])) ?></td> <td><span class="badge"><?= htmlspecialchars($row['payment_method']) ?></span></td> <td> <span class="badge bg-<?= $row['status'] == 'completed' ? 'success' : 'warning' ?>"> <?= ucfirst($row['status']) ?> </span> </td> </tr> <?php } ?> </tbody> </table> </div> </div>
</div>
<?php include("../../footer.php"); ?>