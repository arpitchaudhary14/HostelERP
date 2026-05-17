<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$type = $_POST['type'] ?? '';
$id = $_POST['id'] ?? 0;
$amount = $_POST['amount'] ?? 0;
$method = $_POST['method'] ?? 'UPI';
$user_id = $_SESSION['user_id'];
if ($type == 'library_fine') {
    $sql_hist = "INSERT INTO library_fine_payments (borrow_id, user_id, amount, payment_method) 
                 VALUES ($id, $user_id, $amount, '$method')";
    if (mysqli_query($conn, $sql_hist)) {
        mysqli_query($conn, "UPDATE library_borrows SET fine_amount = 0 WHERE id = $id");
        $_SESSION['payment_success'] = "Library fine paid successfully!";
        header("Location: library/fines_history.php");
    }
} 
else if ($type == 'gym_sub') {
    $plan_id = $id;
    $plan_res = mysqli_query($conn, "SELECT * FROM gym_plans WHERE id = $plan_id");
    $plan = mysqli_fetch_assoc($plan_res);
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+" . $plan['duration_months'] . " months"));
    mysqli_query($conn, "UPDATE gym_subscriptions SET status = 'expired' WHERE user_id = $user_id");
    $sql_sub = "INSERT INTO gym_subscriptions (user_id, plan_id, start_date, end_date, status) 
                VALUES ($user_id, $plan_id, '$start_date', '$end_date', 'active')";
    if (mysqli_query($conn, $sql_sub)) {
        $_SESSION['payment_success'] = "Gym membership activated successfully!";
        header("Location: gym/my_membership.php");
    }
} else if ($type == 'laundry') {
    $plan_id = $id;
    $plan_res = mysqli_query($conn, "SELECT * FROM laundry_plans WHERE id = $plan_id");
    $plan = mysqli_fetch_assoc($plan_res);
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+" . $plan['duration_days'] . " days"));
    $clothes_limit = $plan['clothes_limit'];
    mysqli_query($conn, "UPDATE laundry_subscriptions SET status = 'Expired' WHERE user_id = $user_id");
    $sql_sub = "INSERT INTO laundry_subscriptions (user_id, plan_id, start_date, end_date, remaining_clothes, status) 
                VALUES ($user_id, $plan_id, '$start_date', '$end_date', $clothes_limit, 'Active')";
    $tid = "CL-" . time() . rand(1000, 9999);
    mysqli_query($conn, "INSERT INTO laundry_payments (user_id, amount, payment_method, transaction_id, status) 
                         VALUES ($user_id, $amount, '$method', '$tid', 'Completed')");
    if (mysqli_query($conn, $sql_sub)) {
        $_SESSION['payment_success'] = "Cleanly subscription activated! You can now start requesting washes.";
        header("Location: laundry/dashboard.php");
    }
}
?>