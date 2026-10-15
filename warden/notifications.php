<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') { 
    header("Location: /login.php"); 
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = ""; 
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) { 
    $title = mysqli_real_escape_string($conn, $_POST['title']); 
    $message = mysqli_real_escape_string($conn, $_POST['message']); 
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (title, message, target_role, sender_id) VALUES (?, ?, 'student', ?)"); 
    mysqli_stmt_bind_param($stmt, "ssi", $title, $message, $user_id); 
    if(mysqli_stmt_execute($stmt)){ 
        $msg = "Notification sent to all students successfully!"; 
    } else { 
        $error = "Failed to send notification."; 
    }
}

try {
    $chk_tr = mysqli_query($conn, "SHOW COLUMNS FROM notifications LIKE 'target_role'");
    if($chk_tr && mysqli_num_rows($chk_tr) > 0) {
        $my_notifications = mysqli_query($conn, "SELECT n.*, COALESCE(u.first_name, 'System') as first_name, COALESCE(u.last_name, 'Notice') as last_name, COALESCE(u.role, 'HostelERP') as role FROM notifications n LEFT JOIN users u ON n.sender_id = u.id WHERE n.target_role IN ('global', 'warden') OR n.user_id = $user_id ORDER BY n.created_at DESC");
    } else {
        $my_notifications = mysqli_query($conn, "SELECT n.*, 'System' as first_name, 'Notice' as last_name, 'HostelERP' as role FROM notifications n WHERE n.user_id IS NULL OR n.user_id = $user_id ORDER BY n.created_at DESC");
    }
} catch (Throwable $e) {
    $my_notifications = mysqli_query($conn, "SELECT n.*, 'System' as first_name, 'Notice' as last_name, 'HostelERP' as role FROM notifications n ORDER BY n.created_at DESC");
}

$sent_notifications = mysqli_query($conn, "SELECT * FROM notifications WHERE sender_id = $user_id ORDER BY created_at DESC");

$pageTitle = 'Notifications - HostelERP';
$pageDesc = 'Access your Notifications on HostelERP.';
include("../header.php"); 
?>
<div class="container mt-4 page-fade-in"> 
    <div class="row"> 
        <div class="col-md-4"> 
            <div class="glass-card-light p-4 reveal"> 
                <h4 style="font-weight:700; color:var(--inner-heading);">Send to Students</h4> 
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?> 
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?> 
                <form method="POST"> 
                    <div class="mb-3"> 
                        <label class="form-label text-muted small fw-semibold">Title</label> 
                        <input type="text" name="title" class="form-control" required> 
                    </div> 
                    <div class="mb-3"> 
                        <label class="form-label text-muted small fw-semibold">Message</label> 
                        <textarea name="message" class="form-control" rows="4" required></textarea> 
                    </div> 
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Broadcast to Students</button> 
                </form> 
                <h5 class="mt-4" style="font-weight:700; color:var(--inner-heading);">Sent History</h5> 
                <ul class="list-group mt-3" style="background: transparent;"> 
                    <?php while($srow = mysqli_fetch_assoc($sent_notifications)): ?> 
                        <li class="list-group-item" style="background: rgba(255,255,255,0.03); color: var(--inner-heading); border-color: rgba(255,255,255,0.1);"> 
                            <strong><?= htmlspecialchars($srow['title']) ?></strong><br> 
                            <small class="text-muted"><?= htmlspecialchars(date('M d, Y H:i', strtotime($srow['created_at']))) ?> (To: <?= ucfirst($srow['target_role'] ?? 'students') ?>)</small> 
                        </li> 
                    <?php endwhile; ?> 
                </ul> 
            </div> 
        </div> 
        <div class="col-md-8"> 
            <div class="glass-card-light p-4 reveal" style="min-height: 400px;"> 
                <h4 style="font-weight:700; color:var(--inner-heading);">My Notifications</h4> 
                <div class="mt-4"> 
                    <?php while($row = mysqli_fetch_assoc($my_notifications)): ?> 
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
                    <?php if(mysqli_num_rows($my_notifications) == 0): ?> 
                        <p class="text-muted">No new notifications.</p> 
                    <?php endif; ?> 
                </div> 
            </div> 
        </div> 
    </div>
</div>
<?php include("../footer.php"); ?>