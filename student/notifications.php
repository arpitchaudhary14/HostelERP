<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { 
    header("Location: /login.php"); 
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id'] ?? 0);
try {
    $chk_tr = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'target_role'");
    if($chk_tr && mysqli_num_rows($chk_tr) > 0) {
        $notifications = mysqli_query($conn, "SELECT n.*, COALESCE(u.first_name, 'System') as first_name, COALESCE(u.last_name, 'Notice') as last_name, COALESCE(u.role, 'HostelERP') as role FROM notifications n LEFT JOIN users u ON n.sender_id = u.id WHERE n.target_role IN ('global', 'student') OR n.user_id = $user_id ORDER BY n.created_at DESC");
    } else {
        $notifications = mysqli_query($conn, "SELECT n.*, 'System' as first_name, 'Notice' as last_name, 'HostelERP' as role FROM notifications n WHERE n.user_id IS NULL OR n.user_id = $user_id ORDER BY n.created_at DESC");
    }
} catch (Throwable $e) {
    $notifications = mysqli_query($conn, "SELECT n.*, 'System' as first_name, 'Notice' as last_name, 'HostelERP' as role FROM notifications n ORDER BY n.created_at DESC");
}

$pageTitle = 'Notifications - HostelERP';
$pageDesc = 'Access your Notifications on HostelERP.';
include("../header.php"); 
?>
<div class="container mt-4 page-fade-in"> 
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;"> 
        <h4 style="font-weight:700; color:var(--inner-heading);">Notifications</h4> 
        <div class="mt-4"> 
            <?php while($row = mysqli_fetch_assoc($notifications)): ?> 
                <div class="card mb-3 border-0 shadow-sm" style="background: rgba(255,255,255,0.05); border-radius: 12px;"> 
                    <div class="card-body"> 
                        <h5 class="card-title" style="color:var(--accent-primary); font-weight:600;"><?= htmlspecialchars($row['title']) ?></h5> 
                        <h6 class="card-subtitle mb-2 text-muted"> 
                            From: <?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?> (<?= ucfirst($row['role']) ?>) | <small><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['created_at']))) ?></small> 
                        </h6> 
                        <p class="card-text mt-2" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($row['message'])) ?></p> 
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