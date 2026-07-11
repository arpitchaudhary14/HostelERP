<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') { header("Location: /login.php"); exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$user_id = $_SESSION['user_id'];
$message = "";
$error = "";
if (isset($_POST['join_plan'])) { $plan_id = intval($_POST['plan_id']); if (process_subscription($conn, $user_id, $plan_id)) { $message = "Welcome to MatrixFit! Your membership is now active."; } else { $error = "Something went wrong. Please contact administration."; }
}
$plans = get_gym_plans($conn);
$active_sub = get_active_membership($conn, $user_id);
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in"> <div class="glass-card-light mb-4 reveal text-center" style="padding:var(--space-2xl);"> <img src="/assets/images/MatrixFit_Logo.jpeg" height="60" class="mb-3" style="border-radius: 12px;"> <h2 style="font-weight:800; color:#1a1a2e;">Level Up Your Fitness</h2> <p style="color:#666; max-width: 600px; margin: 0 auto;">Choose a membership plan that fits your goals and join the MatrixFit community today.</p> </div> <?php if($message){ ?> <div class="alert alert-success text-center reveal"><?= $message ?></div> <?php } ?> <?php if($error){ ?> <div class="alert alert-danger text-center reveal"><?= $error ?></div> <?php } ?> <?php if($active_sub){ ?> <div class="alert alert-info text-center reveal"> You already have an active <strong><?= htmlspecialchars($active_sub['plan_name']) ?></strong> membership. <br><small>Expires on: <?= date('d M, Y', strtotime($active_sub['end_date'])) ?></small> </div> <?php } ?> <div class="row g-4 justify-content-center"> <?php while($plan = mysqli_fetch_assoc($plans)){ ?> <div class="col-md-4 reveal"> <div class="glass-card-light h-100 d-flex flex-column text-center"> <h4 class="fw-bold mb-3"><?= htmlspecialchars($plan['name']) ?></h4> <div class="mb-4"> <span class="h1 fw-800 text-gradient">₹<?= number_format($plan['price'], 0) ?></span> <span class="text-muted"> / <?= $plan['duration_months'] ?> mo</span> </div> <hr> <div class="text-center"> <ul class="list-unstyled mb-auto py-3 d-inline-block text-start"> <?php $features = explode(',', $plan['features']); foreach($features as $f){ echo "<li class='mb-2 text-muted'><i class='bi bi-check2 text-primary me-2'></i>" . htmlspecialchars(trim($f)) . "</li>"; } ?> </ul> </div> <div class="mt-4"> <?php if($active_sub && $active_sub['plan_id'] == $plan['id']){ ?> <button class="btn btn-success w-100 rounded-pill py-2 fw-bold" disabled>Active Plan</button> <?php } else { ?> <a href="../payment_gateway.php?type=gym_sub&id=<?= $plan['id'] ?>&amount=<?= $plan['price'] ?>&item=<?= urlencode($plan['name']) ?> Membership" class="btn btn-gradient w-100 rounded-pill py-2 fw-bold">Pay & Join</a> <?php } ?> </div> </div> </div> <?php } ?> </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php include("../../footer.php"); ?>