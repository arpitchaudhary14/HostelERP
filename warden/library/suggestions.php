<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$suggestions = mysqli_query($conn, "SELECT s.*, u.full_name, u.username FROM library_suggestions s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal"> <h2 class="fw-bold text-gradient mb-0">Student Interest Hub</h2> <p class="text-muted mb-0">See what books your students are recommending for the library.</p> </div> <div class="row g-4"> <?php while($s = mysqli_fetch_assoc($suggestions)): ?> <div class="col-md-6 col-lg-4 reveal"> <div class="glass-card-light h-100 p-4"> <div class="d-flex justify-content-between align-items-start mb-3"> <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Suggestion</span> <small class="text-muted"><?= date('d M, Y', strtotime($s['created_at'])) ?></small> </div> <h5 class="fw-bold mb-1"><?= htmlspecialchars($s['title']) ?></h5> <p class="text-muted small mb-3">By <?= htmlspecialchars($s['author'] ?: 'Unknown') ?></p> <div class="p-3 rounded-3 mb-3"> <p class="small mb-0">"<?= htmlspecialchars($s['reason']) ?: 'No reason provided.' ?>"</p> </div> <div class="d-flex align-items-center mt-auto"> <div class="avatar-mini me-2"><?= substr($s['full_name'], 0, 1) ?></div> <div class="small"> <div class="fw-bold"><?= htmlspecialchars($s['full_name']) ?></div> <div class="text-muted text-xs">@<?= htmlspecialchars($s['username']) ?></div> </div> </div> </div> </div> <?php endwhile; ?> <?php if(mysqli_num_rows($suggestions) == 0): ?> <div class="col-12 text-center py-5"> <p class="text-muted">No student suggestions yet.</p> </div> <?php endif; ?> </div>
</div>
<style>
.avatar-mini { width: 30px; height: 30px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;
}
.text-xs { font-size: 0.75rem; }
</style>
<?php include("../../footer.php"); ?>