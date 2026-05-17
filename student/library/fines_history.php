<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$payments = mysqli_query($conn, "SELECT p.*, bk.title 
                                  FROM library_fine_payments p 
                                  JOIN library_borrows b ON p.borrow_id = b.id 
                                  JOIN library_books bk ON b.book_id = bk.id 
                                  WHERE p.user_id = $user_id 
                                  ORDER BY p.paid_at DESC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <h2 class="fw-bold text-gradient mb-0">Paid Fine List</h2>
        <p class="text-muted mb-0">Record of all your settled library fines.</p>
    </div>
    <div class="glass-card-light reveal">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Receipt ID</th>
                        <th>Book Title</th>
                        <th>Amount Paid</th>
                        <th>Payment Method</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($payments)): ?>
                    <tr>
                        <td><span class="badge bg-light text-dark border">#IX-PAY-<?= $p['id'] ?></span></td>
                        <td><div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div></td>
                        <td><span class="fw-bold text-success">₹<?= number_format($p['amount'], 2) ?></span></td>
                        <td><?= htmlspecialchars($p['payment_method']) ?></td>
                        <td><?= date('d M, Y | h:i A', strtotime($p['paid_at'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($payments) == 0): ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No past payments found. Keep up the good reading habits!</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../footer.php"); ?>