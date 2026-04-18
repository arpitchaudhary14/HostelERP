<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'student'){
    header("Location: ../dashboard.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT date, status FROM attendance WHERE user_id=? ORDER BY date DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$attendance = [];
$present = $absent = $leave = 0;
while($row = mysqli_fetch_assoc($result)){
    $attendance[] = $row;
    if($row['status'] === 'present') $present++;
    elseif($row['status'] === 'absent') $absent++;
    else $leave++;
}
$total = $present + $absent + $leave;
$percent = $total > 0 ? round(($present / $total) * 100) : 0;
include("../header.php");
?>
<div class="container mt-4">
    <h3 class="mb-2">My Attendance</h3>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card stat-success text-center">
                <h5>Present</h5>
                <h2 style="color:var(--accent-secondary);"><?= $present ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-danger text-center">
                <h5>Absent</h5>
                <h2 style="color:var(--accent-danger);"><?= $absent ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-primary text-center">
                <h5>On Leave</h5>
                <h2 style="color:var(--accent-primary);"><?= $leave ?></h2>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center" style="background:var(--glass-bg-light);">
                <h5>Attendance %</h5>
                <h2><?= $percent ?>%</h2>
            </div>
        </div>
    </div>
    <div class="glass-card-light">
        <table class="table table-bordered mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($attendance): ?>
                    <?php foreach($attendance as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date']) ?></td>
                            <td>
                                <?php
                                    if($row['status']==='present'){
                                        echo "<span class='badge bg-success'>Present</span>";
                                    } elseif($row['status']==='absent'){
                                        echo "<span class='badge bg-danger'>Absent</span>";
                                    } else {
                                        echo "<span class='badge bg-warning text-dark'>On Leave</span>";
                                    }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center text-muted">No attendance records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include("../footer.php"); ?>