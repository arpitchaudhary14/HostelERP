<?php
include("session_check.php");
include("db.php");
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}
$id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$stmt = mysqli_prepare($conn, "SELECT posted_by, title FROM notices WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$notice = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if ($notice) {
    if ($role === 'admin' || ($role === 'warden' && $notice['posted_by'] == $user_id)) {
        $del_stmt = mysqli_prepare($conn, "DELETE FROM notices WHERE id = ?");
        mysqli_stmt_bind_param($del_stmt, "i", $id);
        if (mysqli_stmt_execute($del_stmt)) {
            $action = "Deleted notice: " . $notice['title'];
            $log_stmt = mysqli_prepare($conn, "INSERT INTO activity_logs (user_id, role, action) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($log_stmt, "iss", $user_id, $role, $action);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        mysqli_stmt_close($del_stmt);
    }
}
if ($role === 'admin') {
    header("Location: admin/notices.php?msg=Notice deleted");
} elseif ($role === 'warden') {
    header("Location: warden/notices.php?msg=Notice deleted");
} else {
    header("Location: student/notices.php");
}
exit();