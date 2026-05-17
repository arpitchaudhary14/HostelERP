<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$role = $_SESSION['role'];
$trainers = get_gym_trainers($conn);
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold text-gradient mb-1">Meet Our Trainers</h2>
                <p class="text-muted">The experts behind MatrixFit's fitness programs.</p>
            </div>
            <a href="plans.php" class="btn btn-gradient rounded-pill px-4">View Plans</a>
        </div>
    </div>
    <div class="row g-4">
        <?php if(mysqli_num_rows($trainers) > 0): ?>
            <?php while($t = mysqli_fetch_assoc($trainers)): ?>
            <div class="col-md-4 reveal">
                <div class="glass-card-light h-100 text-center trainer-card">
                    <div class="trainer-avatar mb-3 mx-auto">
                        <span class="h2"><?= strtoupper(substr($t['full_name'], 0, 1)) ?></span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill mb-2 px-3">
                        <?= htmlspecialchars($t['specialization']) ?>
                    </span>
                    <?php if(!empty($t['bio'])): ?>
                        <p class="small text-muted fst-italic mb-3 px-2">"<?= htmlspecialchars($t['bio']) ?>"</p>
                    <?php endif; ?>
                    <hr class="mt-0">
                    <div class="text-start px-3">
                        <p class="small mb-2"><strong><i class="bi bi-clock me-2"></i>Schedule:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($t['schedule']) ?></span></p>
                        <p class="small mb-0"><strong><i class="bi bi-envelope me-2"></i>Contact:</strong><br>
                        <span class="text-muted"><?= htmlspecialchars($t['email']) ?></span></p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5 reveal">
                <div class="glass-card-light">
                    <i class="bi bi-person-badge h1 text-muted"></i>
                    <p class="mt-3 text-muted">Our coaching staff is currently being updated. Check back soon!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
.trainer-avatar {
    width: 80px;
    height: 80px;
    background: var(--accent-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.trainer-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.trainer-card:hover {
    transform: translateY(-5px);
}
</style>
<?php include("../../footer.php"); ?>