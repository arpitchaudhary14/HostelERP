<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$message = '';
$message_type = '';
if (isset($_POST['add_category'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    if (mysqli_query($conn, "INSERT INTO library_categories (name) VALUES ('$name')")) {
        $message = "Category added!";
        $message_type = "success";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
$categories = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM library_books WHERE category_id = c.id) as book_count 
                                    FROM library_categories c ORDER BY name ASC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="fw-bold text-gradient mb-0">Category Manager</h2>
            <form class="d-flex gap-2" method="POST">
                <input type="text" name="name" class="form-control rounded-pill px-4" placeholder="New Category Name" required>
                <button type="submit" name="add_category" class="btn btn-gradient rounded-pill px-4">Add</button>
            </form>
        </div>
    </div>
    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <?php while($c = mysqli_fetch_assoc($categories)): ?>
        <div class="col-md-3 reveal">
            <div class="glass-card-light text-center p-4">
                <div class="stat-icon bg-primary-subtle text-primary mb-3 mx-auto">
                    <i class="bi bi-tags h3"></i>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($c['name']) ?></h5>
                <p class="text-muted small mb-0"><?= $c['book_count'] ?> Books</p>
                <hr class="my-3">
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-sm btn-outline-primary rounded-circle"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger rounded-circle"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<style>
.stat-icon { width: 60px; height: 60px; border-radius: 15px; display: flex; align-items: center; justify-content: center; }
</style>
<?php include("../../footer.php"); ?>