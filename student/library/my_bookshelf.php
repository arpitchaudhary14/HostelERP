<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../library/includes/library_functions.php";
$user_id = $_SESSION['user_id'];
$sql = "SELECT b.*, bk.title, bk.author, bk.cover_image, r.id as review_id, r.rating, r.review 
        FROM library_borrows b 
        JOIN library_books bk ON b.book_id = bk.id 
        LEFT JOIN library_reviews r ON (r.book_id = b.book_id AND r.user_id = b.user_id)
        WHERE b.user_id = $user_id 
        ORDER BY b.borrow_date DESC";
$borrows = mysqli_query($conn, $sql);
$message = '';
$message_type = '';
if (isset($_POST['submit_review'])) {
    $book_id = $_POST['book_id'];
    $rating = intval($_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $sql = "INSERT INTO library_reviews (book_id, user_id, rating, review) 
            VALUES ($book_id, $user_id, $rating, '$review') 
            ON DUPLICATE KEY UPDATE rating = $rating, review = '$review'";
    if (mysqli_query($conn, $sql)) {
        $message = "Review updated successfully!";
        $message_type = "success";
        header("Refresh: 2");
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="fw-bold text-gradient mb-0">My Bookshelf</h2>
                <p class="text-muted mb-0">Manage your active borrows and reading history.</p>
            </div>
            <a href="catalog.php" class="btn btn-gradient rounded-pill px-4">Browse More</a>
        </div>
    </div>
    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-12 reveal">
            <div class="glass-card-light">
                <div class="table-responsive">
                    <table class="table table-hover align-middle custom-table">
                        <thead>
                            <tr>
                                <th>Book Details</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Fine</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $modal_data = []; 
                            while($b = mysqli_fetch_assoc($borrows)): 
                                if($b['status'] == 'returned') $modal_data[] = $b;
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="book-mini-cover me-3" style="background-image: url('/WebTechProject/assets/images/<?= $b['cover_image'] ?>')"></div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($b['title']) ?></div>
                                            <small class="text-muted">By <?= htmlspecialchars($b['author']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('d M, Y', strtotime($b['borrow_date'])) ?></td>
                                <td>
                                    <span class="<?= (strtotime($b['due_date']) < time() && $b['status'] == 'borrowed') ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('d M, Y', strtotime($b['due_date'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="<?= $b['fine_amount'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">₹<?= number_format($b['fine_amount'], 2) ?></span>
                                </td>
                                <td>
                                    <?php 
                                    $badge = 'bg-secondary';
                                    if($b['status'] == 'borrowed') $badge = 'bg-primary';
                                    if($b['status'] == 'returned') $badge = 'bg-success';
                                    if($b['status'] == 'overdue') $badge = 'bg-danger';
                                    if($b['status'] == 'pending') $badge = 'bg-warning text-dark';
                                    ?>
                                    <span class="badge <?= $badge ?> rounded-pill px-3"><?= ucfirst($b['status']) ?></span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php if($b['fine_amount'] > 0): ?>
                                            <a href="../payment_gateway.php?type=library_fine&id=<?= $b['id'] ?>&amount=<?= $b['fine_amount'] ?>&item=Fine for <?= urlencode($b['title']) ?>" class="btn btn-sm btn-danger rounded-pill px-3">Pay Fine</a>
                                        <?php endif; ?>

                                        <?php if($b['status'] == 'pending'): ?>
                                            <a href="../cancel_borrow.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Cancel this request?')">Cancel</a>
                                        <?php elseif($b['status'] == 'borrowed' || $b['status'] == 'overdue'): ?>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="alert('Please visit the library desk to return this book.')">Return</button>
                                        <?php elseif($b['status'] == 'returned'): ?>
                                            <button class="btn btn-sm btn-warning fw-bold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#reviewModal<?= $b['book_id'] ?>">
                                                <i class="bi bi-star-fill me-1"></i> <?= $b['review_id'] ? 'Edit Review' : 'Review' ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php foreach($modal_data as $m): ?>
<div class="modal fade" id="reviewModal<?= $m['book_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="background: var(--bg-card, #fff); border: 1px solid rgba(255,255,255,0.1) !important;">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--text-main, #333);">Rate "<?= htmlspecialchars($m['title']) ?>"</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST">
                    <input type="hidden" name="book_id" value="<?= $m['book_id'] ?>">
                    <div class="mb-3 text-center">
                        <label class="form-label d-block small fw-bold mb-3" style="color: var(--text-muted, #666);">How many stars?</label>
                        <div class="rating-stars h2 text-warning">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="r<?= $i ?>-<?= $m['book_id'] ?>" <?= ($m['rating'] == $i) ? 'checked' : '' ?> required> 
                                <label for="r<?= $i ?>-<?= $m['book_id'] ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold" style="color: var(--text-main, #333);">Your Experience</label>
                        <textarea name="review" class="form-control rounded-3 border-0 shadow-sm" rows="4" style="background: rgba(0,0,0,0.05); color: var(--text-main);" placeholder="Tell us more about the book..."><?= htmlspecialchars($m['review'] ?? '') ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit_review" class="btn btn-gradient rounded-pill py-2 fw-bold">Save My Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<style>
.custom-table tr { background: transparent; transition: background 0.3s; }
.custom-table tbody tr:hover { background: rgba(255,255,255,0.05); }
.book-mini-cover { 
    width: 45px; 
    height: 65px; 
    background-size: cover; 
    background-position: center;
    border-radius: 6px; 
    box-shadow: 0 3px 10px rgba(0,0,0,0.1); 
    background-color: #222; 
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.book-mini-cover::after {
    content: '📚';
    font-size: 1.2rem;
    opacity: 0.3;
}
.rating-stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; }
.rating-stars input { display: none; }
.rating-stars label { cursor: pointer; font-size: 35px; color: #ccc; transition: all 0.2s; }
.rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #ffc107; transform: scale(1.1); }
.modal-content { backdrop-filter: blur(15px); border-radius: 20px; }
</style>
<?php include("../../footer.php"); ?>