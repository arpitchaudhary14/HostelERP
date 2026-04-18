<?php
session_start();
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}
if ($_SESSION['role'] == 'student') {
    header("Location: student/dashboard.php");
} elseif ($_SESSION['role'] == 'warden') {
    header("Location: warden/dashboard.php");
} elseif ($_SESSION['role'] == 'admin') {
    header("Location: admin/dashboard.php");
}
exit();