<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: /login.php"); exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $user_sql));
$full_name = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
if (empty($full_name)) $full_name = $user['full_name'] ?? 'User';
include("../../header.php");
?>
<div class="container mt-5 page-fade-in"> <div class="text-center mb-5 reveal"> <h2 class="fw-bold text-gradient">Digital Library Card</h2> <p class="text-muted">Your official access to Indexia Library Services.</p> </div> <div class="d-flex justify-content-center reveal"> <div class="id-card-wrapper shadow-2xl"> <div class="id-card-main"> <div class="id-card-header text-center"> <img src="/assets/images/Indexia_Logo.jpeg" height="35" class="mb-1 rounded shadow-sm"> <h6 class="mb-0 fw-800 letter-spacing-1">INDEXIA LIBRARY</h6> <p class="text-xxs mb-0 opacity-75">HOSTEL ERP INTEGRATED SYSTEM</p> </div> <div class="id-card-body"> <div class="profile-container mb-3 mx-auto"> <img src="/assets/profile/<?= htmlspecialchars($user['profile_pic'] ?? 'default.png') ?>" class="profile-img shadow-sm" onerror="this.src='/assets/images/default_avatar.png'"> </div> <div class="text-center mb-4"> <h4 class="fw-900 mb-1 text-uppercase letter-spacing-1"><?= htmlspecialchars($full_name) ?></h4> <span class="badge bg-primary rounded-pill px-3 py-1 mb-3 text-uppercase small shadow-sm"> <?= strtoupper($user['role']) ?> MEMBER </span> <div class="details-grid text-start mx-auto" style="max-width: 220px;"> <div class="d-flex justify-content-between mb-1 border-bottom border-light pb-1"> <span class="text-xxs fw-bold text-muted">MEMBER ID</span> <span class="text-xs fw-900">IX-<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span> </div> <div class="d-flex justify-content-between"> <span class="text-xxs fw-bold text-muted">VALID UNTIL</span> <span class="text-xs fw-900"><?= date('M Y', strtotime('+1 year')) ?></span> </div> </div> </div> <div class="barcode-section rounded-3 p-2 text-center border"> <div class="barcode-sim mb-1"></div> <code class="text-xxs fw-bold"><?= time() ?>-MEMBER-<?= $user_id ?></code> </div> </div> <div class="id-card-footer text-center py-2 -50"> <small class="text-xxs fw-bold">SCAN AT ENTRY GATE • DIGITAL ACCESS PASS</small> </div> </div> </div> </div> <div class="text-center mt-5 reveal"> <button onclick="window.print()" class="btn btn-dark rounded-pill px-5 py-2 fw-bold shadow-lg"> <i class="bi bi-printer me-2"></i> Print High-Res ID Card </button> </div>
</div>
<style>
.id-card-wrapper { width: 330px; border-radius: 25px; overflow: hidden; border: 1px solid #eee; transition: transform 0.3s;
}
.id-card-wrapper:hover { transform: translateY(-5px); }
.id-card-header { background: linear-gradient(135deg, #1a1a2e, #16213e); padding: 25px 15px 55px;
}
.id-card-body { padding: 30px 20px 20px; }
.profile-container { width: 110px; height: 110px; border-radius: 50%; border: 5px solid #fff; box-shadow: 0 8px 20px rgba(0,0,0,0.1); overflow: hidden; margin-top: -60px;
}
.profile-img { width: 100%; height: 100%; object-fit: cover; }
.barcode-sim { height: 40px; background: repeating-linear-gradient(90deg, #111, #111 1px, #fff 1px, #fff 4px); width: 100%;
}
.fw-800 { font-weight: 800; }
.fw-900 { font-weight: 900; }
.letter-spacing-1 { letter-spacing: 1px; }
.text-xxs { font-size: 0.65rem; }
@media print { body * { visibility: hidden; } .id-card-wrapper, .id-card-wrapper * { visibility: visible; } .id-card-wrapper { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); border: 1px solid #000; } .btn { display: none; }
}
</style>
<?php include("../../footer.php"); ?>