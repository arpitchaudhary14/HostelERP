<?php
$token = $_GET['session_token'] ?? '';
if ($token) {
    session_id($token);
    session_start();
    
    if (isset($_SESSION['role'])) {
        $role = $_SESSION['role'];
        if ($role == 'admin') header("Location: /admin/dashboard.php");
        elseif ($role == 'warden') header("Location: /warden/dashboard.php");
        else header("Location: /student/dashboard.php");
        exit();
    }
}
header("Location: /login.php");
exit();
?>
