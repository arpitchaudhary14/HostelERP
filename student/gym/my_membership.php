<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$user_id = $_SESSION['user_id'];
$active_sub = get_active_membership($conn, $user_id);
$history = get_membership_history($conn, $user_id);
$review_res = mysqli_query($conn, "SELECT * FROM gym_reviews WHERE user_id = $user_id");
$my_review = mysqli_fetch_assoc($review_res);
$message = '';
$message_type = '';
if (isset($_POST['submit_gym_review'])) {
    $rating = intval($_POST['rating']);
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $sql = "INSERT INTO gym_reviews (user_id, rating, review) 
            VALUES ($user_id, $rating, '$review') 
            ON DUPLICATE KEY UPDATE rating = $rating, review = '$review'";           
    if (mysqli_query($conn, $sql)) {
        $message = "Thank you for your feedback on MatrixFit Gym!";
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
    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-md-5 reveal">
            <div class="glass-card-light mb-4">
                <h4 class="mb-4 fw-bold">Current Membership</h4>
                <?php if($active_sub){ ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <span class="badge bg-success px-3 py-2 rounded-pill">Active</span>
                        </div>
                        <h2 class="text-gradient fw-800 mb-1"><?= htmlspecialchars($active_sub['plan_name']) ?></h2>
                        <p class="text-muted">Membership ID: #MF-<?= $active_sub['id'] ?></p>                        
                        <div class="mt-4 bg-light p-3 rounded-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Started On</span>
                                <span class="fw-bold"><?= date('d M, Y', strtotime($active_sub['start_date'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ends On</span>
                                <span class="fw-bold text-danger"><?= date('d M, Y', strtotime($active_sub['end_date'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="bi bi-shield-lock text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">No Active Membership</h5>
                        <p class="text-muted small">You don't have any active gym plan at the moment.</p>
                        <a href="plans.php" class="btn btn-gradient mt-2">View Plans</a>
                    </div>
                <?php } ?>
            </div>
            <div class="glass-card-light">
                <h5 class="fw-bold mb-3">Gym Feedback ⭐</h5>
                <p class="text-muted small mb-4">How's your training going? Share your thoughts with us.</p>  
                <?php if($my_review): ?>
                    <div class="p-3 rounded-4 bg-light mb-3">
                        <div class="text-warning mb-2">
                            <?php for($i=0; $i<$my_review['rating']; $i++) echo "★"; ?>
                        </div>
                        <p class="small text-dark italic mb-0">"<?= htmlspecialchars($my_review['review']) ?>"</p>
                    </div>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#gymReviewModal">Update Feedback</button>
                <?php else: ?>
                    <button class="btn btn-gradient w-100 rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#gymReviewModal">Write a Review</button>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-7 reveal">
            <div class="glass-card-light h-100">
                <h4 class="mb-4 fw-bold">Membership History</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Plan</th>
                                <th>Period</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($history)){ ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($row['plan_name']) ?></td>
                                <td>
                                    <small class="d-block text-muted">
                                        <?= date('d M, Y', strtotime($row['start_date'])) ?> - 
                                        <?= date('d M, Y', strtotime($row['end_date'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-<?= ($row['status']=='active'?'success':'secondary') ?> opacity-75">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                            <?php if(mysqli_num_rows($history) == 0){ ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No membership history found.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="gymReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden" style="background: var(--bg-card, #fff);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--text-main, #333);">MatrixFit Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form method="POST">
                    <div class="mb-3 text-center">
                        <label class="form-label d-block small fw-bold mb-3">Overall Rating</label>
                        <div class="rating-stars h2 text-warning">
                            <?php for($i=5; $i>=1; $i--): ?>
                                <input type="radio" name="rating" value="<?= $i ?>" id="gr<?= $i ?>" <?= (isset($my_review['rating']) && $my_review['rating'] == $i) ? 'checked' : '' ?> required> 
                                <label for="gr<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-bold">What can we improve?</label>
                        <textarea name="review" class="form-control rounded-3 border-0 shadow-sm" rows="4" style="background: rgba(0,0,0,0.05); color: var(--text-main);" placeholder="Equipment quality, trainer support, or cleanliness..."><?= htmlspecialchars($my_review['review'] ?? '') ?></textarea>
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="submit_gym_review" class="btn btn-gradient rounded-pill py-2 fw-bold">Submit My Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
.rating-stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 5px; }
.rating-stars input { display: none; }
.rating-stars label { cursor: pointer; font-size: 35px; color: #ccc; transition: all 0.2s; }
.rating-stars input:checked ~ label, .rating-stars label:hover, .rating-stars label:hover ~ label { color: #ffc107; transform: scale(1.1); }
.modal-content { backdrop-filter: blur(15px); }
</style>
<?php include("../../footer.php"); ?>