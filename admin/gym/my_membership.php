<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$user_id = $_SESSION['user_id'];
$active_sub = get_active_membership($conn, $user_id);
$history = get_membership_history($conn, $user_id);
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row g-4">
        <div class="col-md-5 reveal">
            <div class="glass-card-light h-100">
                <h4 class="mb-4 fw-bold">My MatrixFit Status (Admin)</h4>
                <?php if($active_sub){ ?>
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <span class="badge bg-success px-3 py-2 rounded-pill">Active Member</span>
                        </div>
                        <h2 class="text-gradient fw-800 mb-1"><?= htmlspecialchars($active_sub['plan_name']) ?></h2>
                        <p class="text-muted">ID: #MF-A-<?= $active_sub['id'] ?></p>
                        
                        <div class="mt-4 bg-light p-3 rounded-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Start Date</span>
                                <span class="fw-bold"><?= date('d M, Y', strtotime($active_sub['start_date'])) ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Expiry Date</span>
                                <span class="fw-bold text-danger"><?= date('d M, Y', strtotime($active_sub['end_date'])) ?></span>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3">No Personal Membership</h5>
                        <p class="text-muted small">You haven't assigned a gym plan to yourself yet.</p>
                        <a href="subscriptions.php?user_id=<?= $user_id ?>" class="btn btn-gradient mt-2">Assign to Me</a>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="col-md-7 reveal">
            <div class="glass-card-light h-100">
                <h4 class="mb-4 fw-bold">Personal Subscription History</h4>
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
                                    <span class="badge bg-<?= ($row['status']=='active'?'success':'secondary') ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php include("../../footer.php"); ?>