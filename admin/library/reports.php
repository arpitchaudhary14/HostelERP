<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../library/includes/library_functions.php";
$stats = get_library_stats($conn);
$category_stats = mysqli_query($conn, "SELECT c.name, COUNT(b.id) as count 
                                        FROM library_categories c 
                                        LEFT JOIN library_books b ON c.id = b.category_id 
                                        GROUP BY c.id");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <h2 class="fw-bold text-gradient mb-0">Library Insights & Reports</h2>
        <p class="text-muted mb-0">Visual data on inventory, circulation, and revenue.</p>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3 reveal">
            <div class="glass-card-light text-center p-4">
                <h3 class="fw-bold mb-0 text-primary"><?= $stats['total_books'] ?></h3>
                <small class="text-muted">TOTAL BOOKS</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="glass-card-light text-center p-4">
                <h3 class="fw-bold mb-0 text-success"><?= $stats['active_borrows'] ?></h3>
                <small class="text-muted">ACTIVE ISSUES</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="glass-card-light text-center p-4">
                <h3 class="fw-bold mb-0 text-warning"><?= $stats['pending_requests'] ?></h3>
                <small class="text-muted">OPEN REQUESTS</small>
            </div>
        </div>
        <div class="col-md-3 reveal">
            <div class="glass-card-light text-center p-4">
                <h3 class="fw-bold mb-0 text-danger">₹<?= number_format($stats['total_fines'], 2) ?></h3>
                <small class="text-muted">TOTAL REVENUE</small>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6 reveal">
            <div class="glass-card-light p-4 h-100">
                <h5 class="fw-bold mb-4">Category Distribution</h5>
                <div class="chart-container" style="position: relative; height:300px;">
                    <div class="d-flex align-items-end justify-content-around h-100 pb-4 border-bottom">
                        <?php while($cat = mysqli_fetch_assoc($category_stats)): 
                            $height = ($cat['count'] / max(1, $stats['total_books'])) * 100;
                        ?>
                        <div class="text-center" style="width: 40px;">
                            <div class="bg-primary rounded-top" style="height: <?= $height ?>px; min-height: 5px;" title="<?= $cat['count'] ?> books"></div>
                            <small class="text-xs d-block mt-2" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $cat['name'] ?></small>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 reveal">
            <div class="glass-card-light p-4 h-100">
                <h5 class="fw-bold mb-4">Recent Transactions</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Book</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent = mysqli_query($conn, "SELECT b.*, bk.title FROM library_borrows b JOIN library_books bk ON b.book_id = bk.id ORDER BY b.created_at DESC LIMIT 6");
                            while($r = mysqli_fetch_assoc($recent)):
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill"><?= ucfirst($r['status']) ?></span>
                                </td>
                                <td class="small text-truncate" style="max-width: 150px;"><?= htmlspecialchars($r['title']) ?></td>
                                <td class="small text-muted"><?= date('d M', strtotime($r['created_at'])) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.text-xs { font-size: 0.7rem; }
.chart-container { background: rgba(0,0,0,0.02); border-radius: 10px; padding: 20px; }
</style>
<?php include("../../footer.php"); ?>