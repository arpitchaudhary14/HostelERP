<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT u.*, s.status as sub_status 
            FROM users u 
            LEFT JOIN gym_subscriptions s ON (u.id = s.user_id AND s.status = 'active')
            WHERE u.id = $user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $user_sql));
$full_name = trim(($user['first_name'] ?? '') . " " . ($user['last_name'] ?? ''));
if (empty($full_name)) $full_name = $user['full_name'] ?? 'User';
include("../../header.php");
?>
<div class="container mt-5 page-fade-in">
    <div class="text-center mb-5 reveal">
        <h2 class="fw-bold text-gradient">MatrixFit Digital Card</h2>
        <p class="text-muted">Your entry pass to the ultimate fitness destination.</p>
    </div>
    <div class="d-flex justify-content-center reveal">
        <div class="gym-card-wrapper shadow-2xl">
            <div class="gym-card-main">
                <div class="gym-card-header p-4 text-center">
                    <img src="/WebTechProject/assets/images/MatrixFit_Logo.jpeg" height="40" class="mb-2 rounded shadow-sm">
                    <h6 class="mb-0 fw-900 letter-spacing-1 text-white">MATRIXFIT GYM</h6>
                    <p class="text-xxs text-white-50 mb-0">HARDER • BETTER • FASTER • STRONGER</p>
                </div>
                <div class="gym-card-body p-4 text-center bg-dark">
                    <div class="profile-hex-container mb-4 mx-auto shadow-lg">
                        <img src="/WebTechProject/assets/profile/<?= htmlspecialchars($user['profile_pic'] ?? 'default.png') ?>" 
                             class="profile-img"
                             onerror="this.src='/WebTechProject/assets/images/default_avatar.png'">
                    </div>
                    <h4 class="fw-900 text-white text-uppercase letter-spacing-1 mb-1"><?= htmlspecialchars($full_name) ?></h4>
                    <div class="mb-4">
                        <span class="badge <?= $user['sub_status'] == 'active' ? 'bg-success' : 'bg-danger' ?> rounded-pill px-4 py-1 shadow-sm">
                            <?= $user['sub_status'] == 'active' ? 'ELITE MEMBER' : 'INACTIVE' ?>
                        </span>
                    </div>
                    <div class="details-box text-start p-3 rounded-4 mb-4" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between mb-2 border-bottom border-secondary pb-1">
                            <span class="text-xxs text-white-50 fw-bold">MEMBER ROLE</span>
                            <span class="text-xs text-white fw-bold"><?= strtoupper($user['role']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-xxs text-white-50 fw-bold">GYM PASS ID</span>
                            <span class="text-xs text-white fw-bold">MF-<?= str_pad($user['id'], 5, '0', STR_PAD_LEFT) ?></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-2 py-2 bg-black rounded-pill border border-secondary">
                        <div class="chip-sim"></div>
                        <span class="text-xxs text-white-50 letter-spacing-1 fw-bold">NFC ENABLED SMART PASS</span>
                    </div>
                </div>
                <div class="gym-card-footer text-center py-2 bg-black text-white-50 border-top border-secondary">
                    <small class="text-xxs">VALID FOR ALL MATRIXFIT LOCATIONS</small>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-5 reveal">
        <button onclick="window.print()" class="btn btn-gradient rounded-pill px-5 py-2 fw-bold shadow-lg">
            <i class="bi bi-printer me-2"></i> Print Gym Pass
        </button>
    </div>
</div>
<style>
.gym-card-wrapper {
    width: 330px;
    background: #000;
    border-radius: 25px;
    overflow: hidden;
    border: 2px solid #333;
}
.gym-card-header {
    background: linear-gradient(135deg, #000000, #1a1a1a);
    border-bottom: 2px solid var(--accent-primary);
}
.profile-hex-container {
    width: 120px;
    height: 120px;
    background: var(--accent-primary);
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
    padding: 4px;
}
.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
}
.chip-sim {
    width: 30px;
    height: 20px;
    background: linear-gradient(135deg, #ffd700, #b8860b);
    border-radius: 4px;
}
.fw-900 { font-weight: 900; }
.letter-spacing-1 { letter-spacing: 1px; }
.text-xxs { font-size: 0.65rem; }
@media print {
    body * { visibility: hidden; }
    .gym-card-wrapper, .gym-card-wrapper * { visibility: visible; }
    .gym-card-wrapper { position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); }
    .btn { display: none; }
}
</style>
<?php include("../../footer.php"); ?>