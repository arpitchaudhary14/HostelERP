<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$message = "";
if (isset($_POST['add_equipment'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $last_m = $_POST['last_maintenance'];
    $next_m = $_POST['next_maintenance'];
    $query = "INSERT INTO gym_equipment (name, status, last_maintenance, next_maintenance) VALUES ('$name', '$status', '$last_m', '$next_m')";
    if (mysqli_query($conn, $query)) {
        $message = "Equipment added successfully!";
    }
}
if (isset($_POST['update_status'])) {
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE gym_equipment SET status = '$status' WHERE id = $id");
}
if (isset($_POST['delete_equipment'])) {
    $id = intval($_POST['equip_id']);
    mysqli_query($conn, "DELETE FROM gym_equipment WHERE id=$id");
    $message = "🗑️ Equipment removed.";
}
$equipment = mysqli_query($conn, "SELECT * FROM gym_equipment ORDER BY created_at DESC");
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 style="font-weight:700; margin:0;">MatrixFit Inventory</h3>
                <p style="margin:0;">Monitor gym equipment and maintenance schedules.</p>
            </div>
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">Add Equipment</button>
        </div>
    </div>
    <?php if($message){ ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php } ?>
    <div class="row g-4">
        <?php while($row = mysqli_fetch_assoc($equipment)){ ?>
        <div class="col-md-4 reveal">
            <div class="glass-card-light">
                <h5 class="fw-bold mb-3"><?= htmlspecialchars($row['name']) ?></h5>
                <div class="mb-3">
                    <?php 
                    $badge_class = 'success';
                    if($row['status'] == 'under_maintenance') $badge_class = 'warning';
                    if($row['status'] == 'out_of_order') $badge_class = 'danger';
                    ?>
                    <span class="badge bg-<?= $badge_class ?>"><?= str_replace('_', ' ', ucfirst($row['status'])) ?></span>
                </div>
                <div class="small text-muted mb-1">Last Maintenance: <?= $row['last_maintenance'] ? date('d M, Y', strtotime($row['last_maintenance'])) : 'N/A' ?></div>
                <div class="small text-muted mb-3">Next Maintenance: <?= $row['next_maintenance'] ? date('d M, Y', strtotime($row['next_maintenance'])) : 'N/A' ?></div>
                <form method="POST" class="mt-2">
                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                    <select name="status" class="form-select form-select-sm mb-2" onchange="this.form.submit()">
                        <option value="available" <?= ($row['status']=='available'?'selected':'') ?>>Available</option>
                        <option value="under_maintenance" <?= ($row['status']=='under_maintenance'?'selected':'') ?>>Maintenance</option>
                        <option value="out_of_order" <?= ($row['status']=='out_of_order'?'selected':'') ?>>Out of Order</option>
                    </select>
                    <input type="hidden" name="update_status" value="1">
                </form>
                <button class="action-btn action-btn-delete btn-delete-equip mt-2"
                    data-id="<?= $row['id'] ?>"
                    data-name="<?= htmlspecialchars($row['name'], ENT_QUOTES) ?>">
                    <i class="bi bi-trash3"></i> Remove
                </button>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<div class="modal fade" id="addEquipmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content glass-card-light">
            <div class="modal-header">
                <h5 class="modal-title">Add Equipment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Equipment Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Status</label>
                        <select name="status" class="form-select">
                            <option value="available">Available</option>
                            <option value="under_maintenance">Maintenance</option>
                            <option value="out_of_order">Out of Order</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Last Maintenance</label>
                            <input type="date" name="last_maintenance" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Next Maintenance</label>
                            <input type="date" name="next_maintenance" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_equipment" class="btn btn-gradient">Save Equipment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include("../../footer.php"); ?>
<div class="modal fade" id="deleteEquipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="glass-card modal-content border-0 p-4 text-center">
            <div style="font-size:3rem;">🗑️</div>
            <h5 class="fw-bold mt-2">Remove Equipment?</h5>
            <p class="text-muted small" id="delete_equip_name"></p>
            <p class="text-danger small">This cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="equip_id" id="delete_equip_id">
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_equipment" class="btn btn-danger rounded-pill px-4">Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
.action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: 0.8rem;
    font-weight: 600; border: none; cursor: pointer;
    transition: all 0.2s ease; white-space: nowrap;
}
.action-btn-delete {
    background: linear-gradient(135deg, #ff5252, #ff1744);
    color: #fff; box-shadow: 0 2px 8px rgba(255,82,82,0.35);
}
.action-btn-delete:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,23,68,0.5); color:#fff; }
.action-btn:active { transform: scale(0.96); }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete-equip').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('delete_equip_id').value = this.dataset.id;
            document.getElementById('delete_equip_name').textContent = '"' + this.dataset.name + '"';
            new bootstrap.Modal(document.getElementById('deleteEquipModal')).show();
        });
    });
});
</script>