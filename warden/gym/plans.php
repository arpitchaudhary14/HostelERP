<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$user_id = $_SESSION['user_id'];
$message = "";
$error = "";
if (isset($_POST['join_plan'])) {
    $plan_id = intval($_POST['plan_id']);
    if (process_subscription($conn, $user_id, $plan_id)) {
        $message = "Welcome to MatrixFit! Your membership is now active.";
    } else {
        $error = "Something went wrong. Please contact administration.";
    }
}
$plans = get_gym_plans($conn);
$active_sub = get_active_membership($conn, $user_id);
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal text-center" style="padding:var(--space-xl);">
        <img src="/WebTechProject/assets/images/MatrixFit_Logo.jpeg" height="50" class="mb-3" style="border-radius: 10px;">
        <h2 style="font-weight:800; ">Warden Wellness Plans</h2>
        <p >MatrixFit is open to all staff. Choose your plan and start your journey.</p>
    </div>
    <?php if($message){ ?>
        <div class="alert alert-success text-center reveal"><?= $message ?></div>
    <?php } ?>
    <?php if($error){ ?>
        <div class="alert alert-danger text-center reveal"><?= $error ?></div>
    <?php } ?>
    <div class="row g-4 justify-content-center">
        <?php while($plan = mysqli_fetch_assoc($plans)){ ?>
        <div class="col-md-4 reveal">
            <div class="glass-card-light h-100 d-flex flex-column text-center">
                <h4 class="fw-bold mb-3"><?= htmlspecialchars($plan['name']) ?></h4>
                <div class="mb-4">
                    <span class="h1 fw-800 text-gradient">₹<?= number_format($plan['price'], 0) ?></span>
                    <span class="text-muted"> / <?= $plan['duration_months'] ?> mo</span>
                </div>
                <hr>
                <div class="text-center">
                    <ul class="list-unstyled mb-auto py-3 d-inline-block text-start">
                    <?php 
                    $features = explode(',', $plan['features']);
                    foreach($features as $f){
                        echo "<li class='mb-2 text-muted'><i class='bi bi-check2 text-primary me-2'></i>" . htmlspecialchars(trim($f)) . "</li>";
                    }
                    ?>
                    </ul>
                </div>
                <form method="POST" class="mt-4">
                    <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                    <button type="submit" name="join_plan" class="btn <?= ($active_sub && $active_sub['plan_id'] == $plan['id'] ? 'btn-success disabled' : 'btn-gradient') ?> w-100">
                        <?= ($active_sub && $active_sub['plan_id'] == $plan['id'] ? 'Active Plan' : 'Join Now') ?>
                    </button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php include("../../footer.php"); ?>