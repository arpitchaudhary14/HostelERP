<?php
$code = isset($_GET['code']) ? (int)$_GET['code'] : (isset($_SERVER['REDIRECT_STATUS']) ? (int)$_SERVER['REDIRECT_STATUS'] : 404);
$errors = [
    400 => ['title' => 'Bad Request', 'desc' => "We couldn't process your request.", 'icon' => 'exclamation-triangle'],
    403 => ['title' => 'Access Denied', 'desc' => "You don't have permission to view this page.", 'icon' => 'slash-circle'],
    404 => ['title' => 'Page Not Found', 'desc' => "We couldn't find the page you're looking for.", 'icon' => 'compass'],
    500 => ['title' => 'Server Error', 'desc' => "Something went wrong on our end. We're looking into it.", 'icon' => 'hdd-network'],
    503 => ['title' => 'Service Unavailable', 'desc' => "We're currently down for maintenance. Be right back!", 'icon' => 'tools']
];
if (!array_key_exists($code, $errors)) {
    $code = 404;
}
$error = $errors[$code];
$base_url = "";
if (strpos($_SERVER['REQUEST_URI'], '/WebTechProject') !== false || file_exists(__DIR__ . '/assets')) {
    $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    $base_url = rtrim($script_dir, '/');
}
if(empty($base_url)) {
    $base_url = (strpos($_SERVER['REQUEST_URI'], '/WebTechProject/') === 0) ? '/WebTechProject' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $code; ?> - <?php echo $error['title']; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo $base_url; ?>/assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/style.css">
    <script>(function(){var t=localStorage.getItem('hostelerp-theme')||'dark';document.documentElement.setAttribute('data-theme',t);})();</script>
    <style>
        .error-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
        }
        .error-card {
            max-width: 500px;
            width: 100%;
            text-align: center;
            position: relative;
            z-index: 10;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(135deg, var(--accent-info), var(--accent-primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }
        .theme-toggle-fixed {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
        }
        .custom-alert {
            background: rgba(108, 99, 255, 0.1);
            border: 1px solid rgba(108, 99, 255, 0.2);
            border-left: 4px solid var(--accent-primary);
            color: var(--inner-text);
            padding: 1rem;
            border-radius: var(--radius-sm);
            font-style: italic;
        }
        .btn-outline-custom {
            border: 2px solid rgba(108, 99, 255, 0.3);
            color: var(--inner-text);
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 600;
        }
        .btn-outline-custom:hover {
            border-color: var(--accent-primary);
            background: rgba(108, 99, 255, 0.1);
            color: var(--accent-primary-light);
        }
    </style>
</head>
<body class="inner-bg">
    <div class="theme-toggle-fixed">
        <button class="theme-toggle" id="errorThemeToggle" aria-label="Toggle theme"> 
            <span class="theme-icon">☀️</span> 
            <span class="theme-label">Light</span>
        </button>
    </div> 
    <div class="error-wrapper">
        <div class="glass-card-light error-card page-fade-in">
            <h1 class="error-code mb-3">
                <i class="bi bi-<?php echo $error['icon']; ?>"></i>
                <?php echo $code; ?>
            </h1>
            <h2 class="mb-3" style="font-weight: 700; color: var(--inner-heading);"><?php echo $error['title']; ?></h2>  
            <p class="text-muted mb-4"><?php echo $error['desc']; ?></p>
            <div class="custom-alert mb-4">
                "We're fixing this issue, kindly wait... or head back to the homepage."
            </div>
            <div class="d-flex flex-column gap-3 mt-4">
                <a href="https://hostelerp.eastasia.cloudapp.azure.com/" class="btn-gradient w-100">
                    <i class="bi bi-house-door"></i> Back to Homepage
                </a>
                <a href="https://hostelerp.eastasia.cloudapp.azure.com/login.php" class="btn-outline-custom w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Go to Login
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btn = document.getElementById('errorThemeToggle');
        function updateBtnState(theme) {
            const icon = btn.querySelector('.theme-icon');
            const label = btn.querySelector('.theme-label');
            if (theme === 'dark') {
                icon.textContent = '☀️';
                label.textContent = 'Light';
            } else {
                icon.textContent = '🌙';
                label.textContent = 'Dark';
            }
        }
        const initialTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        updateBtnState(initialTheme);
        btn.addEventListener('click', () => {
            const doc = document.documentElement;
            const current = doc.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            doc.setAttribute('data-theme', next);
            localStorage.setItem('hostelerp-theme', next);
            updateBtnState(next);
        });
    </script>
</body>
</html>