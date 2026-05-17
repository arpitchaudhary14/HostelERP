<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$message = '';
$message_type = '';
if (isset($_POST['submit_suggestion'])) {
    $user_id = $_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $author = mysqli_real_escape_string($conn, $_POST['author']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $sql = "INSERT INTO library_suggestions (user_id, title, author, reason) VALUES ($user_id, '$title', '$author', '$reason')";
    if (mysqli_query($conn, $sql)) {
        $message = "Your suggestion has been submitted! Our librarians will review it.";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
$my_suggestions = mysqli_query($conn, "SELECT * FROM library_suggestions WHERE user_id = {$_SESSION['user_id']} ORDER BY created_at DESC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <h2 class="fw-bold text-gradient mb-0">Suggest a Book</h2>
        <p class="text-muted mb-0">Help us expand our collection with your favorite titles.</p>
    </div>
    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-md-5 reveal">
            <div class="glass-card-light h-100 p-4">
                <h5 class="fw-bold mb-4">New Recommendation</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Book Title</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. The Psychology of Money" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Author (Optional)</label>
                        <input type="text" name="author" class="form-control rounded-3" placeholder="e.g. Morgan Housel">
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Why should we add it?</label>
                        <textarea name="reason" class="form-control rounded-3" rows="4" placeholder="Mention why this book is useful for students..."></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit_suggestion" class="btn btn-gradient rounded-pill py-2">Submit Suggestion</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-md-7 reveal">
            <div class="glass-card-light h-100">
                <h5 class="fw-bold mb-4">My Suggestions Status</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s = mysqli_fetch_assoc($my_suggestions)): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($s['title']) ?></div>
                                    <small class="text-muted">By <?= htmlspecialchars($s['author'] ?: 'Unknown') ?></small>
                                </td>
                                <td><?= date('d M, Y', strtotime($s['created_at'])) ?></td>
                                <td>
                                    <?php 
                                    $badge = 'bg-secondary';
                                    if($s['status'] == 'approved') $badge = 'bg-success';
                                    if($s['status'] == 'rejected') $badge = 'bg-danger';
                                    if($s['status'] == 'pending') $badge = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badge ?> rounded-pill px-3"><?= ucfirst($s['status']) ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($my_suggestions) == 0): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">You haven't made any suggestions yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../../footer.php"); ?>