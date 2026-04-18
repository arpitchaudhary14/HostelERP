<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$notifications = mysqli_query($conn, "SELECT n.*, u.first_name, u.last_name, u.role FROM notifications n JOIN users u ON n.sender_id = u.id WHERE n.target_role IN ('global', 'student') ORDER BY n.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
        <h4 style="font-weight:700; color:#1a1a2e;">Notifications</h4>
        <div class="mt-4">
            <?php while($row = mysqli_fetch_assoc($notifications)): ?>
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title" style="color:var(--accent-primary); font-weight:600;"><?= htmlspecialchars($row['title']) ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">
                        From: <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?> (<?= ucfirst($row['role']) ?>) | 
                        <small><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['created_at']))) ?></small>
                    </h6>
                    <p class="card-text mt-2"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
            <?php if(mysqli_num_rows($notifications) == 0): ?>
            <p class="text-muted">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>