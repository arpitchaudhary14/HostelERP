<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
require_once "../../library/includes/library_functions.php";
$fines = mysqli_query($conn, "SELECT b.*, bk.title, u.full_name, u.username FROM library_borrows b JOIN library_books bk ON b.book_id = bk.id JOIN users u ON b.user_id = u.id WHERE b.fine_amount > 0 ORDER BY b.fine_amount DESC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <h2 class="fw-bold text-gradient mb-0">Fine Management</h2> <p class="text-muted mb-0">Track and manage outstanding library penalties.</p> </div> <div class="glass-card-light reveal"> <div class="table-responsive"> <table class="table table-hover align-middle table-sticky"> <thead> <tr> <th>Student</th> <th>Book</th> <th>Status</th> <th>Fine Amount</th> <th>Actions</th> </tr> </thead> <tbody> <?php while($f = mysqli_fetch_assoc($fines)): ?> <tr> <td> <div class="fw-bold"><?= htmlspecialchars($f['full_name']) ?></div> <small class="text-muted">@<?= htmlspecialchars($f['username']) ?></small> </td> <td><?= htmlspecialchars($f['title']) ?></td> <td> <span class="badge <?= $f['status'] == 'returned' ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3"> <?= ucfirst($f['status']) ?> </span> </td> <td><span class="fw-bold text-danger">₹<?= number_format($f['fine_amount'], 2) ?></span></td> <td> <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="alert('Payment module integration coming soon!')">Mark Paid</button> </td> </tr> <?php endwhile; ?> <?php if(mysqli_num_rows($fines) == 0): ?> <tr><td colspan="5" class="text-center py-4 text-muted">No outstanding fines! Great job.</td></tr> <?php endif; ?> </tbody> </table> </div> </div>
</div>
<?php include("../../footer.php"); ?>