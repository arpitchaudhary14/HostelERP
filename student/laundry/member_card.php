<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));
$sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.*, p.name as plan_name FROM laundry_subscriptions s JOIN laundry_plans p ON s.plan_id = p.id WHERE s.user_id = $user_id AND s.status = 'Active' LIMIT 1"));
$full_name = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
if (empty($full_name)) $full_name = $user['full_name'] ?? 'Student';
include("../../header.php");
?>
<div class="container mt-5 page-fade-in"> <div class="text-center mb-5 reveal"> <h2 class="fw-bold text-gradient">Cleanly Access Pass</h2> <p class="text-muted">Your digital identity for hostel laundry services.</p> </div> <div class="d-flex justify-content-center reveal"> <div class="laundry-card-wrapper shadow-2xl"> <div class="laundry-card-main"> <div class="laundry-card-header text-center"> <img src="/assets/images/Cleanly_Logo.jpeg" height="40" class="mb-1 rounded shadow-sm"> <h6 class="mb-0 fw-800 letter-spacing-1">CLEANLY SERVICES</h6> <p class="text-xxs mb-0 opacity-75 text-uppercase">Digital Laundry Pass</p> </div> <div class="laundry-card-body"> <div class="profile-container mb-3 mx-auto"> <img src="/assets/profile/<?= htmlspecialchars($user['profile_pic'] ?? 'default.png') ?>" class="profile-img shadow-sm" onerror="this.src='/assets/images/default_avatar.png'"> </div> <div class="text-center mb-4"> <h4 class="fw-900 mb-1 text-uppercase letter-spacing-1"><?= htmlspecialchars($full_name) ?></h4> <span class="badge bg-info rounded-pill px-3 py-1 mb-3 text-uppercase small shadow-sm"> <?= $sub ? $sub['plan_name'] : 'NO ACTIVE PLAN' ?> </span> <div class="details-grid text-start mx-auto" style="max-width: 220px;"> <div class="d-flex justify-content-between mb-1 border-bottom border-light pb-1"> <span class="text-xxs fw-bold text-muted">PASS ID</span> <span class="text-xs fw-900">CL-<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span> </div> <div class="d-flex justify-content-between"> <span class="text-xxs fw-bold text-muted">REMAINING</span> <span class="text-xs fw-900"><?= $sub ? $sub['remaining_clothes'] : '0' ?> Clothes</span> </div> </div> </div> <div class="barcode-section rounded-3 p-2 text-center border"> <div class="wave-pattern mb-1"></div> <code class="text-xxs fw-bold">CLEANLY-AUTH-<?= time() ?>-<?= $user_id ?></code> </div> </div> <div class="laundry-card-footer text-center py-2 bg-info"> <small class="text-xxs fw-bold">SCAN AT COLLECTION • FRESH & CLEAN</small> </div> </div> </div> </div> <div class="text-center mt-5 reveal"> <button onclick="window.print()" class="btn btn-info rounded-pill px-5 py-2 fw-bold shadow-lg"> <i class="bi bi-printer me-2"></i> Print Laundry Pass </button> </div>
</div>
<style>
.laundry-card-wrapper { width: 320px; border-radius: 20px; overflow: hidden; border: 1px solid #e0f2fe; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.laundry-card-wrapper:hover { transform: scale(1.02) rotate(1deg); }
.laundry-card-header { background: linear-gradient(135deg, #0ea5e9, #0369a1); padding: 30px 15px 70px; position: relative;
}
.laundry-card-header::after { content: ''; position: absolute; bottom: -10px; left: 0; right: 0; height: 20px; clip-path: ellipse(50% 100% at 50% 100%);
}
.laundry-card-body { padding: 40px 20px 20px; position: relative; }
.profile-container { width: 100px; height: 100px; border-radius: 20px; border: 4px solid #fff; box-shadow: 0 10px 25px rgba(14, 165, 233, 0.2); overflow: hidden; background: #f0f9ff; margin-top: -85px;
}
.profile-img { width: 100%; height: 100%; object-fit: cover; }
.wave-pattern { height: 35px; background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxMjAwIDEyMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSI+PHBhdGggZD0iTTAsMzBDMTUwLDUwLDM1MCwwLDYwMCwwUzEwNTAsNTAsMTIwMCwzMFYxMjBIMFYzMFoiIGZpbGw9IiMwZWE1ZzkiIG9wYWNpdHk9IjAuMSI+PC9wYXRoPjwvc3ZnPg=='); background-size: cover; width: 100%;
}
.fw-800 { font-weight: 800; }
.fw-900 { font-weight: 900; }
.letter-spacing-1 { letter-spacing: 1px; }
.text-xxs { font-size: 0.65rem; }
@media print { body * { visibility: hidden; } .laundry-card-wrapper, .laundry-card-wrapper * { visibility: visible; } .laundry-card-wrapper { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 1px solid #0ea5e9; } .btn { display: none; }
}
</style>
<?php include("../../footer.php"); ?>