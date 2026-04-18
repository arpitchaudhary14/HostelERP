<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$menu_result = mysqli_query($conn, "SELECT * FROM mess_menu");
$menu = [];
while($row = mysqli_fetch_assoc($menu_result)){
    $menu[$row['day_of_week']][$row['meal_type']] = $row['items'];
}
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$meals = ['Breakfast','Lunch','Snacks','Dinner'];
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
        <h4 style="font-weight:700; color:#1a1a2e;">Weekly Mess Menu</h4>
        <div class="table-responsive mt-3">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Day</th>
                        <?php foreach($meals as $m): ?>
                            <th><?= $m ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($days as $day): ?>
                    <tr>
                        <td style="font-weight:600;"><?= $day ?></td>
                        <?php foreach($meals as $m): ?>
                            <td><?= isset($menu[$day][$m]) ? nl2br(htmlspecialchars($menu[$day][$m])) : '<span class="text-muted">Not Set</span>' ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>