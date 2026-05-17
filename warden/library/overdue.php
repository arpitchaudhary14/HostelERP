<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../library/includes/library_functions.php";
calculate_overdue_fines($conn);
$overdue_list = mysqli_query($conn, "SELECT b.*, bk.title, u.full_name, u.username 
                                      FROM library_borrows b 
                                      JOIN library_books bk ON b.book_id = bk.id 
                                      JOIN users u ON b.user_id = u.id 
                                      WHERE b.status = 'overdue' OR (b.status = 'borrowed' AND b.due_date < CURDATE())
                                      ORDER BY b.due_date ASC");
include("../../header.php");
?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <h2 class="fw-bold text-gradient mb-0">Overdue Monitor</h2>
        <p class="text-muted mb-0">Identify and follow up with students who have late returns.</p>
    </div>
    <div class="glass-card-light reveal">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Student Details</th>
                        <th>Book Title</th>
                        <th>Due Date</th>
                        <th>Days Late</th>
                        <th>Fine Accrued</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($o = mysqli_fetch_assoc($overdue_list)): 
                        $due = new DateTime($o['due_date']);
                        $now = new DateTime();
                        $diff = $now->diff($due)->days;
                    ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($o['full_name']) ?></div>
                            <small class="text-muted">@<?= htmlspecialchars($o['username']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($o['title']) ?></td>
                        <td><span class="text-danger fw-bold"><?= date('d M, Y', strtotime($o['due_date'])) ?></span></td>
                        <td><span class="badge bg-danger rounded-pill px-3"><?= $diff ?> Days</span></td>
                        <td><span class="fw-bold text-danger">₹<?= number_format($o['fine_amount'], 2) ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($overdue_list) == 0): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No overdue books in your records.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../footer.php"); ?>