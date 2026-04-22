<?php
include("../session_check.php");
include("../db.php");
if($_SESSION['role'] != 'admin'){
    header("Location: ../dashboard.php");
    exit;
}
$view_warden = isset($_GET['warden_id']) ? intval($_GET['warden_id']) : 0;
include("../header.php");
?>
<div class="container mt-4">
<?php if($view_warden > 0):
    $stmt = mysqli_prepare($conn, "SELECT CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name FROM users WHERE id=? AND role='warden'");
    mysqli_stmt_bind_param($stmt, "i", $view_warden);
    mysqli_stmt_execute($stmt);
    $wname = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if(!$wname){ echo "<div class='alert alert-danger'>Warden not found.</div>"; include("../footer.php"); exit; }
    $stmt2 = mysqli_prepare($conn, "SELECT date, status FROM attendance WHERE user_id=? ORDER BY date DESC");
    mysqli_stmt_bind_param($stmt2, "i", $view_warden);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    $records = [];
    $present = $absent = $leave = 0;
    while($row = mysqli_fetch_assoc($result)){
        $records[] = $row;
        if($row['status'] === 'present') $present++;
        elseif($row['status'] === 'absent') $absent++;
        else $leave++;
    }
    $total = $present + $absent + $leave;
    $percent = $total > 0 ? round(($present / $total) * 100) : 0;
?>
<a href="warden_attendance.php" class="btn btn-outline-secondary btn-sm mb-3">← Back to All Wardens</a>
<h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">📊 <?= htmlspecialchars($wname['full_name']) ?>'s Attendance</h3>
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
            <tr><th>Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php if($records): ?>
            <?php foreach($records as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td>
                    <?php
                    if($row['status']==='present') echo "<span class='badge bg-success'>Present</span>";
                    elseif($row['status']==='absent') echo "<span class='badge bg-danger'>Absent</span>";
                    else echo "<span class='badge bg-warning text-dark'>On Leave</span>";
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
<?php else: ?>
    <h3 class="mb-3" style="font-weight:700; color:#1a1a2e;">📊 Warden Attendance Records</h3>
    <div class="glass-card-light">
    <table class="table table-bordered mb-0">
        <thead class="table-dark">
            <tr>
                <th>Warden</th>
                <th>Present</th>
                <th>Absent</th>
                <th>Leave</th>
                <th>Attendance %</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $wardens = mysqli_query($conn, "SELECT id, CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name FROM users WHERE role='warden' ORDER BY first_name");
        while($w = mysqli_fetch_assoc($wardens)):
            $sid = $w['id'];
            $att = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT SUM(status='present') as p, SUM(status='absent') as a, SUM(status='leave') as l, COUNT(*) as t FROM attendance WHERE user_id=$sid"
            ));
            $p = intval($att['p']); $a = intval($att['a']); $l = intval($att['l']); $t = intval($att['t']);
            $pct = $t > 0 ? round(($p/$t)*100) : 0;
        ?>
        <tr>
            <td><?= htmlspecialchars($w['full_name']) ?></td>
            <td><span class="badge bg-success"><?= $p ?></span></td>
            <td><span class="badge bg-danger"><?= $a ?></span></td>
            <td><span class="badge bg-warning text-dark"><?= $l ?></span></td>
            <td><strong><?= $pct ?>%</strong></td>
            <td><a href="?warden_id=<?= $sid ?>" class="btn btn-sm btn-outline-primary">View Details</a></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
<?php endif; ?>
</div>
<?php include("../footer.php"); ?>