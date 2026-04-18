<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $role = mysqli_real_escape_string($conn, $_POST['target_role']);
    $stmt = mysqli_prepare($conn, "INSERT INTO notifications (title, message, target_role, sender_id) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssi", $title, $message, $role, $user_id);   
    if(mysqli_stmt_execute($stmt)){
         $msg = "Notification broadcasted successfully!";
    } else {
         $error = "Failed to broadcast notification.";
    }
}
$all_notifications = mysqli_query($conn, "SELECT n.*, u.first_name, u.last_name, u.role FROM notifications n JOIN users u ON n.sender_id = u.id ORDER BY n.created_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card-light p-4 reveal">
                <h4 style="font-weight:700; color:#1a1a2e;">Broadcast Notification</h4>
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Target Audience</label>
                        <select name="target_role" class="form-select" required>
                            <option value="global">Global (Everyone)</option>
                            <option value="student">All Students</option>
                            <option value="warden">All Wardens</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Message</label>
                        <textarea name="message" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Send Notification</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
                <h4 style="font-weight:700; color:#1a1a2e;">Notification Log</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Title</th>
                                <th>Target</th>
                                <th>Sender</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($all_notifications)): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('M d, H:i', strtotime($row['created_at']))) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                                    <div class="small text-muted text-truncate" style="max-width: 200px;"><?= htmlspecialchars($row['message']) ?></div>
                                </td>
                                <td><span class="badge bg-<?= $row['target_role'] == 'global' ? 'success' : 'primary' ?>"><?= ucfirst($row['target_role']) ?></span></td>
                                <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($all_notifications) == 0): ?>
                            <tr><td colspan="4" class="text-center">No notifications found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>