<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit;
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    validate_csrf();
    $title     = trim($_POST['message'] ?? '');  
    $msg       = trim($_POST['content'] ?? '');  
    $posted_by = intval($_SESSION['user_id']);
    if($title && $msg){
        $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, message, posted_by) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssi", $title, $msg, $posted_by);
        mysqli_stmt_execute($stmt);
        $success = "Notice posted successfully.";
    } else {
        $error = "Please fill in both title and message.";
    }
}
$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC");
$notices = mysqli_fetch_all($result, MYSQLI_ASSOC);
include("../header.php");
?>
<div class="container mt-4">
<div class="glass-card-light mb-4" style="max-width:700px;">
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">Post Notice</h3>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555;">Title</label>
<input name="message" class="form-control" placeholder="Notice Title" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555;">Message</label>
<textarea name="content" class="form-control mb-2" rows="4" required></textarea>
</div>
<button type="submit" name="post" class="btn btn-primary">Post Notice</button>
</form>
</div>
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">All Notices</h4>
<?php foreach($notices as $n): ?>
    <div class="d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><?= htmlspecialchars($n['title']) ?></h5>
        <?php if($n['posted_by'] == $_SESSION['user_id']): ?>
            <a href="../delete_notice.php?id=<?= $n['id'] ?>" 
               class="btn btn-sm btn-outline-danger py-0"
               onclick="return confirm('Delete this notice?')">Delete</a>
        <?php endif; ?>
    </div>
    <p class="mb-0 mt-2"><?= htmlspecialchars($n['message']) ?></p>
    <small class="text-muted"><?= htmlspecialchars($n['created_at']) ?></small>
</div>
<?php endforeach; ?>
</div>
<?php include("../footer.php"); ?>