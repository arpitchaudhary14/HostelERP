<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$reservations = mysqli_query($conn, "SELECT r.*, bk.title, bk.author, bk.available_copies FROM library_reservations r JOIN library_books bk ON r.book_id = bk.id WHERE r.user_id = $user_id ORDER BY r.created_at DESC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <h2 class="fw-bold text-gradient mb-0">My Booking Requests</h2> <p class="text-muted mb-0">Track your reserved books and waitlist status.</p> </div> <div class="glass-card-light reveal"> <div class="table-responsive"> <table class="table table-hover align-middle table-sticky"> <thead> <tr> <th>Book Title</th> <th>Current Availability</th> <th>Requested On</th> <th>Status</th> </tr> </thead> <tbody> <?php while($r = mysqli_fetch_assoc($reservations)): ?> <tr> <td> <div class="fw-bold"><?= htmlspecialchars($r['title']) ?></div> <small class="text-muted"><?= htmlspecialchars($r['author']) ?></small> </td> <td> <?php if($r['available_copies'] > 0): ?> <span class="badge bg-success rounded-pill px-3"><?= $r['available_copies'] ?> Available</span> <?php else: ?> <span class="badge bg-danger rounded-pill px-3">Out of Stock</span> <?php endif; ?> </td> <td><?= date('d M, Y', strtotime($r['created_at'])) ?></td> <td> <?php $badge = 'bg-warning text-dark'; if($r['status'] == 'fulfilled') $badge = 'bg-success'; if($r['status'] == 'cancelled') $badge = 'bg-secondary'; ?> <span class="badge <?= $badge ?> rounded-pill px-3"><?= ucfirst($r['status']) ?></span> </td> </tr> <?php endwhile; ?> <?php if(mysqli_num_rows($reservations) == 0): ?> <tr><td colspan="4" class="text-center py-5 text-muted">No booking requests found.</td></tr> <?php endif; ?> </tbody> </table> </div> </div>
</div>
<?php include("../../footer.php"); ?>