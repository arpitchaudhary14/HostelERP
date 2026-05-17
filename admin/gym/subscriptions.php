<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$message = "";
$error = "";
if (isset($_POST['assign_subscription'])) {
    $target_user_id = intval($_POST['user_id']);
    $plan_id = intval($_POST['plan_id']);    
    if (process_subscription($conn, $target_user_id, $plan_id)) {
        $message = "Membership activated successfully!";
    } else {
        $error = "Failed to activate membership. Please try again.";
    }
}
$user_id_param = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$users = mysqli_query($conn, "SELECT id, full_name, email FROM users ORDER BY full_name ASC");
$plans = get_gym_plans($conn);
$subs_query = "SELECT s.*, u.full_name, u.role, p.name as plan_name 
               FROM gym_subscriptions s 
               JOIN users u ON s.user_id = u.id 
               JOIN gym_plans p ON s.plan_id = p.id 
               ORDER BY s.created_at DESC";
$all_subs = mysqli_query($conn, $subs_query);
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row g-4">
        <div class="col-md-4 reveal">
            <div class="glass-card-light">
                <h4 class="mb-4" style="font-weight:700;">Assign Membership</h4>
                <?php if($message){ ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php } ?>
                <?php if($error){ ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php } ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select User</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Choose a user...</option>
                            <?php while($u = mysqli_fetch_assoc($users)){ ?>
                                <option value="<?= $u['id'] ?>" <?= ($u['id'] == $user_id_param ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($u['full_name']) ?> (<?= htmlspecialchars($u['email']) ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Plan</label>
                        <select name="plan_id" class="form-select" required>
                            <option value="">Choose a plan...</option>
                            <?php 
                            mysqli_data_seek($plans, 0);
                            while($p = mysqli_fetch_assoc($plans)){ ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= htmlspecialchars($p['name']) ?> - ₹<?= number_format($p['price'], 0) ?> (<?= $p['duration_months'] ?> mo)
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <button type="submit" name="assign_subscription" class="btn btn-gradient w-100 mt-2">Activate Membership</button>
                </form>
            </div>
        </div>
        <div class="col-md-8 reveal">
            <div class="glass-card-light">
                <h4 class="mb-4" style="font-weight:700;">Subscription History</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Plan</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($all_subs)){ ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></div>
                                    <small class="text-muted"><?= ucfirst($row['role']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($row['plan_name']) ?></td>
                                <td><?= date('d M, Y', strtotime($row['start_date'])) ?></td>
                                <td><?= date('d M, Y', strtotime($row['end_date'])) ?></td>
                                <td>
                                    <?php 
                                    $status_class = 'secondary';
                                    if($row['status'] == 'active') $status_class = 'success';
                                    if($row['status'] == 'expired') $status_class = 'danger';
                                    ?>
                                    <span class="badge bg-<?= $status_class ?>"><?= ucfirst($row['status']) ?></span>
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
<?php include("../../footer.php"); ?>