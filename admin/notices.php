<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'warden'){
    header("Location: ../dashboard.php");
    exit();
}
if(isset($_POST['add_notice'])){
    validate_csrf();
    $title   = trim($_POST['title']);
    $message = trim($_POST['message']);
    $user_id = intval($_SESSION['user_id']);
    $role    = $_SESSION['role'];
    $stmt = mysqli_prepare($conn, "INSERT INTO notices (title, message, posted_by, role) VALUES (?,?,?,?)");
    mysqli_stmt_bind_param($stmt, "ssis", $title, $message, $user_id, $role);
    if(mysqli_stmt_execute($stmt)){
        $success = "Notice posted successfully.";
    } else {
        $error = "Something went wrong.";
    }
}
$notices = mysqli_fetch_all(mysqli_query($conn, "SELECT * FROM notices ORDER BY created_at DESC"), MYSQLI_ASSOC);
include("../header.php");
?>
<div class="container mt-4" style="max-width:800px;">
<h4 class="mb-3" style="font-weight:700; color:#1a1a2e;">Post Notice</h4>
<?php if(isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
<?php if(isset($error))   echo "<div class='alert alert-danger'>$error</div>"; ?>
<div class="glass-card-light mb-4">
<form method="POST">
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
<div class="mb-3">
<label style="font-weight:500; color:#555;">Title</label>
<input type="text" name="title" class="form-control" required>
</div>
<div class="mb-3">
<label style="font-weight:500; color:#555;">Message</label>
<textarea name="message" class="form-control" rows="4" required></textarea>
</div>
<button type="submit" name="add_notice" class="btn btn-primary">Post Notice</button>
</form>
</div>
<h5 class="mb-3" style="font-weight:600; color:#1a1a2e;">All Notices</h5>
<?php foreach($notices as $n): ?>
<div class="glass-card-light mb-2">
    <div class="d-flex justify-content-between">
        <h6 class="mb-0"><?= htmlspecialchars($n['title']) ?></h6>
        <div>
            <small class="text-muted me-2"><?= htmlspecialchars($n['created_at']) ?></small>
            <a href="../delete_notice.php?id=<?= $n['id'] ?>" 
               class="btn btn-sm btn-outline-danger py-0"
               onclick="return confirm('Are you sure you want to delete this notice?')">Delete</a>
        </div>
    </div>
    <p class="mb-0 mt-2"><?= htmlspecialchars($n['message']) ?></p>
</div>
<?php endforeach; ?>
</div>
<?php include("../footer.php"); ?>