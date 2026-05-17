<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
require_once "../../gym/includes/gym_functions.php";
$user_id = $_SESSION['user_id'];
$active_sub = get_active_membership($conn, $user_id);
$message = "";
if (isset($_POST['toggle_attendance'])) {
    if (!$active_sub) {
        $message = "You need an active membership to check in!";
    } else {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $check = mysqli_query($conn, "SELECT id FROM gym_attendance WHERE user_id = $user_id AND date = '$date' AND check_out_time IS NULL");
        if ($row = mysqli_fetch_assoc($check)) {
            $id = $row['id'];
            mysqli_query($conn, "UPDATE gym_attendance SET check_out_time = '$time' WHERE id = $id");
            $message = "Checked out successfully!";
        } else {
            mysqli_query($conn, "INSERT INTO gym_attendance (user_id, date, check_in_time) VALUES ($user_id, '$date', '$time')");
            $message = "Welcome to MatrixFit! You are now checked in.";
        }
    }
}
$logs = mysqli_query($conn, "SELECT * FROM gym_attendance WHERE user_id = $user_id ORDER BY date DESC, check_in_time DESC");
$current_session = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM gym_attendance WHERE user_id = $user_id AND date = CURDATE() AND check_out_time IS NULL"));
$total_sessions = mysqli_num_rows($logs);
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <?php if($message){ ?>
        <div class="alert alert-info reveal mb-4 text-center"><?= $message ?></div>
    <?php } ?>
    <div class="row g-4 mb-4">
        <div class="col-md-6 reveal">
            <div class="glass-card-light h-100 d-flex flex-column justify-content-center">
                <div class="text-center py-3">
                    <h5 class="fw-bold mb-3">Gym Check-in (Warden)</h5>
                    <form method="POST">
                        <button type="submit" name="toggle_attendance" class="btn <?= $current_session ? 'btn-danger' : 'btn-gradient' ?> btn-lg px-5 rounded-pill shadow">
                            <i class="bi <?= $current_session ? 'bi-box-arrow-right' : 'bi-box-arrow-in-right' ?> me-2"></i>
                            <?= $current_session ? 'Check Out' : 'Check Me In' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6 reveal">
            <div class="stat-card stat-primary">
                <h5>My Gym Sessions</h5>
                <h2 class="text-gradient"><?= $total_sessions ?></h2>
                <small >Total visits by you</small>
            </div>
        </div>
    </div>
    <div class="glass-card-light reveal">
        <h4 class="mb-4 fw-bold">My Attendance History</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>In Time</th>
                        <th>Out Time</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($logs)){ ?>
                    <tr>
                        <td><?= date('d M, Y', strtotime($row['date'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['check_in_time'])) ?></td>
                        <td><?= $row['check_out_time'] ? date('h:i A', strtotime($row['check_out_time'])) : '<span class="text-success">In Gym</span>' ?></td>
                        <td>
                            <?php 
                            if($row['check_out_time']){
                                $diff = strtotime($row['check_out_time']) - strtotime($row['check_in_time']);
                                echo floor($diff / 60) . " mins";
                            } else { echo "-"; }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<?php include("../../footer.php"); ?>