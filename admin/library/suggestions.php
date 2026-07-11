<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$message = '';
$message_type = '';
if (isset($_POST['action'])) { $id = $_POST['suggestion_id']; $status = $_POST['action'] == 'approve' ? 'approved' : 'rejected'; if (mysqli_query($conn, "UPDATE library_suggestions SET status = '$status' WHERE id = $id")) { $message = "Suggestion " . ucfirst($status) . "!"; $message_type = $status == 'approved' ? 'success' : 'info'; }
}
$suggestions = mysqli_query($conn, "SELECT s.*, u.full_name FROM library_suggestions s JOIN users u ON s.user_id = u.id WHERE s.status = 'pending' ORDER BY s.created_at ASC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <h2 class="fw-bold text-gradient mb-0">Suggestion Approval Desk</h2> <p class="text-muted mb-0">Review student book requests and decide what to add next.</p> </div> <?php if($message): ?> <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert"> <?= $message ?> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div> <?php endif; ?> <div class="row g-4"> <?php while($s = mysqli_fetch_assoc($suggestions)): ?> <div class="col-md-6 reveal"> <div class="glass-card-light p-4"> <div class="d-flex justify-content-between align-items-start mb-3"> <div> <h5 class="fw-bold mb-1"><?= htmlspecialchars($s['title']) ?></h5> <p class="text-muted small">By <?= htmlspecialchars($s['author'] ?: 'Unknown') ?></p> </div> <small class="text-muted"><?= date('d M, Y', strtotime($s['created_at'])) ?></small> </div> <div class="p-3 rounded-3 mb-4"> <p class="small mb-0"><strong>Student Note:</strong> "<?= htmlspecialchars($s['reason']) ?>"</p> </div> <div class="d-flex justify-content-between align-items-center"> <span class="small text-muted">Suggested by: <strong><?= htmlspecialchars($s['full_name']) ?></strong></span> <form method="POST"> <input type="hidden" name="suggestion_id" value="<?= $s['id'] ?>"> <button type="submit" name="action" value="approve" class="btn btn-sm btn-success rounded-pill px-3 me-2">Approve</button> <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger rounded-pill px-3">Reject</button> </form> </div> </div> </div> <?php endwhile; ?> <?php if(mysqli_num_rows($suggestions) == 0): ?> <div class="col-12 text-center py-5"> <p class="text-muted">No pending suggestions.</p> </div> <?php endif; ?> </div>
</div>
<?php include("../../footer.php"); ?>