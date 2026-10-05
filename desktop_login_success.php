<?php
session_start();
$token = $_GET['token'] ?? '';
$pageTitle = 'Login Successful - HostelERP';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .success-card { max-width: 500px; padding: 40px; border-radius: 20px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; background: #d1e7dd; color: #0f5132; font-size: 40px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    </style>
</head>
<body>

<div class="success-card">
    <div class="icon-circle">
        <i class="bi bi-check-lg"></i>
    </div>
    <h3 class="fw-bold mb-3">Authentication Successful!</h3>
    <p class="text-muted mb-4">You have successfully logged in. You can now close this page and continue on HostelERP Desktop. The app will automatically sync your session.</p>
    
    <a href="hostelerp://auth-callback?session_token=<?= htmlspecialchars($token) ?>" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" id="autoRedirectBtn">
        <i class="bi bi-box-arrow-in-right me-2"></i>Go To HostelERP Desktop
    </a>
</div>

<script>
    // Automatically attempt to deep link to the app
    window.onload = function() {
        const token = "<?= htmlspecialchars($token) ?>";
        if (token) {
            setTimeout(() => {
                window.location.href = "hostelerp://auth-callback?session_token=" + token;
            }, 1000);
        }
    };
</script>

</body>
</html>
