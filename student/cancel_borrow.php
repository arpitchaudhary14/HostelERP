<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $sql = "DELETE FROM library_borrows WHERE id = $id AND user_id = $user_id AND status = 'pending'";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['msg'] = "Request cancelled successfully.";
    } else {
        $_SESSION['error'] = "Could not cancel request.";
    }
}
header("Location: library/my_bookshelf.php");
exit();
?>