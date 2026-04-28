<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
$timeout_duration = 900;
if (isset($_SESSION['LAST_ACTIVITY']) &&
    (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: /WebTechProject/login.php?session=expired");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();