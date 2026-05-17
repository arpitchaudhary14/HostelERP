<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;
$amount = $_GET['amount'] ?? 0;
$item_name = $_GET['item'] ?? 'Service Payment';
if (!$amount || !$type) {
    echo "Invalid payment request.";
    exit();
}
$logo = "/WebTechProject/assets/images/logo.png";
if ($type == 'library_fine') $logo = "/WebTechProject/assets/images/Indexia_Logo.jpeg";
if ($type == 'gym_sub') $logo = "/WebTechProject/assets/images/MatrixFit_Logo.jpeg";
if ($type == 'laundry') $logo = "/WebTechProject/assets/images/Cleanly_Logo.jpeg";
include("../header.php");
?>
<div class="container mt-5 page-fade-in">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="glass-card shadow-lg p-0 overflow-hidden reveal">
                <div class="p-4 text-center bg-dark text-white border-bottom border-primary border-3">
                    <img src="<?= $logo ?>" height="30" class="mb-2 rounded">
                    <h5 class="mb-0 fw-bold">HostelERP Secure Checkout</h5>
                    <div class="mt-2">
                        <span class="badge bg-success-soft text-success small border border-success">
                            <i class="bi bi-shield-check me-1"></i> End-to-End Encrypted
                        </span>
                    </div>
                </div>
                <div class="p-4 bg-white text-dark">
                    <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-light rounded-4">
                        <div>
                            <p class="text-muted text-xxs text-uppercase fw-bold mb-0">Order Description</p>
                            <h6 class="fw-bold mb-0"><?= htmlspecialchars($item_name) ?></h6>
                        </div>
                        <div class="text-end">
                            <p class="text-muted text-xxs text-uppercase fw-bold mb-0">Payable Amount</p>
                            <h3 class="fw-bold text-primary mb-0">₹<?= number_format($amount, 2) ?></h3>
                        </div>
                    </div>
                    <h6 class="fw-bold mb-3 small text-uppercase letter-spacing-1">Payment Methods</h6>
                    <form action="process_payment.php" method="POST">
                        <input type="hidden" name="type" value="<?= $type ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <input type="hidden" name="amount" value="<?= $amount ?>">
                        <div class="payment-option p-3 rounded-4 mb-2 border d-flex align-items-center active">
                            <input type="radio" name="method" value="UPI" id="upi" checked class="me-3">
                            <label for="upi" class="d-flex align-items-center w-100 cursor-pointer">
                                <div class="icon-box-sm bg-primary-soft text-primary me-3">
                                    <i class="bi bi-qr-code"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">UPI (GPay, PhonePe, Paytm)</div>
                                    <div class="text-muted text-xxs">Pay with any UPI App</div>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e1/UPI-Logo.png/1200px-UPI-Logo.png" height="15">
                            </label>
                        </div>
                        <div class="payment-option p-3 rounded-4 mb-2 border d-flex align-items-center">
                            <input type="radio" name="method" value="Card" id="card" class="me-3">
                            <label for="card" class="d-flex align-items-center w-100 cursor-pointer">
                                <div class="icon-box-sm bg-success-soft text-success me-3">
                                    <i class="bi bi-credit-card"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">Cards & No-Cost EMI</div>
                                    <div class="text-muted text-xxs">Visa, MasterCard, Amex</div>
                                </div>
                                <div class="h4 mb-0 text-muted">
                                    <i class="bi bi-credit-card-2-front"></i>
                                </div>
                            </label>
                        </div>
                        <div class="payment-option p-3 rounded-4 mb-2 border d-flex align-items-center">
                            <input type="radio" name="method" value="NetBanking" id="nb" class="me-3">
                            <label for="nb" class="d-flex align-items-center w-100 cursor-pointer">
                                <div class="icon-box-sm bg-info-soft text-info me-3">
                                    <i class="bi bi-bank"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">Net Banking</div>
                                    <div class="text-muted text-xxs">All Major Indian Banks</div>
                                </div>
                            </label>
                        </div>
                        <div class="payment-option p-3 rounded-4 mb-4 border d-flex align-items-center">
                            <input type="radio" name="method" value="Cash" id="cash" class="me-3">
                            <label for="cash" class="d-flex align-items-center w-100 cursor-pointer">
                                <div class="icon-box-sm bg-warning-soft text-warning me-3">
                                    <i class="bi bi-cash-stack"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">Pay at Office (Cash)</div>
                                    <div class="text-muted text-xxs">Generate Receipt & Pay Offline</div>
                                </div>
                            </label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark rounded-pill py-3 fw-bold text-uppercase shadow">
                                Complete Payment
                            </button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <img src="https://www.verifone.com/sites/default/files/styles/logo_mobile/public/2021-08/pci-dss-logo.png" height="30" class="grayscale opacity-50 me-3">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" height="15" class="grayscale opacity-50">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
.bg-success-soft { background: rgba(25, 135, 84, 0.1); }
.bg-info-soft { background: rgba(13, 202, 240, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
.icon-box-sm { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.2rem; }
.payment-option { transition: all 0.2s; border: 2px solid #f1f1f1 !important; cursor: pointer; }
.payment-option:hover { border-color: #ddd !important; }
.payment-option.active { border-color: #000 !important; background: #f8f9fa; }
.text-xxs { font-size: 0.65rem; }
.grayscale { filter: grayscale(1); }
.letter-spacing-1 { letter-spacing: 1px; }
</style>
<script>
document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('active'));
        this.classList.add('active');
        this.querySelector('input').checked = true;
    });
});
</script>
<?php include("../footer.php"); ?>