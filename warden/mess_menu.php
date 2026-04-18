<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['items'])) {
    $day = mysqli_real_escape_string($conn, $_POST['day_of_week']);
    $meal = mysqli_real_escape_string($conn, $_POST['meal_type']);
    $items = mysqli_real_escape_string($conn, $_POST['items']);
    $stmt = mysqli_prepare($conn, "INSERT INTO mess_menu (day_of_week, meal_type, items, updated_by) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE items=VALUES(items), updated_by=VALUES(updated_by)");
    mysqli_stmt_bind_param($stmt, "sssi", $day, $meal, $items, $user_id);   
    if(mysqli_stmt_execute($stmt)){
         $msg = "Menu updated successfully!";
    } else {
         $error = "Failed to update menu.";
    }
}
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
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card-light p-4 reveal">
                <h4 style="font-weight:700; color:#1a1a2e;">Update Menu</h4>
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Day of Week</label>
                        <select name="day_of_week" class="form-select" required>
                            <?php foreach($days as $d): ?>
                                <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Meal Type</label>
                        <select name="meal_type" class="form-select" required>
                            <?php foreach($meals as $m): ?>
                                <option value="<?= $m ?>"><?= $m ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Items</label>
                        <textarea name="items" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Save Menu</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
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
    </div>
</div>
<?php include("../footer.php"); ?>