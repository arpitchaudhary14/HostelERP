<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../../db.php";
$message = "";
$message_type = "success";
if (isset($_POST['add_trainer'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $spec  = mysqli_real_escape_string($conn, $_POST['specialization']);
    $sched = mysqli_real_escape_string($conn, $_POST['schedule']);
    $bio   = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    if (mysqli_query($conn, "INSERT INTO gym_trainers (full_name, email, phone, specialization, schedule, bio) VALUES ('$name', '$email', '$phone', '$spec', '$sched', '$bio')")) {
        $message = "✅ Trainer added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
if (isset($_POST['edit_trainer'])) {
    $id    = intval($_POST['trainer_id']);
    $name  = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $spec  = mysqli_real_escape_string($conn, $_POST['specialization']);
    $sched = mysqli_real_escape_string($conn, $_POST['schedule']);
    $bio   = mysqli_real_escape_string($conn, $_POST['bio'] ?? '');
    if (mysqli_query($conn, "UPDATE gym_trainers SET full_name='$name', email='$email', phone='$phone', specialization='$spec', schedule='$sched', bio='$bio' WHERE id=$id")) {
        $message = "✅ Trainer updated successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
        $message_type = "danger";
    }
}
if (isset($_POST['delete_trainer'])) {
    $id = intval($_POST['trainer_id']);
    if (mysqli_query($conn, "DELETE FROM gym_trainers WHERE id=$id")) {
        $message = "🗑️ Trainer removed.";
        $message_type = "warning";
    }
}
$trainers = mysqli_query($conn, "SELECT * FROM gym_trainers ORDER BY created_at DESC");
?>
<?php include("../../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light mb-4 reveal">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h3 style="font-weight:700; color:var(--inner-heading); margin:0;">MatrixFit Trainers</h3>
                <p class="text-muted mb-0">Manage certified gym trainers and their schedules.</p>
            </div>
            <button class="btn btn-gradient rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addTrainerModal">
                <i class="bi bi-plus-lg me-2"></i>Add Trainer
            </button>
        </div>
    </div>
    <?php if($message): ?>
    <div class="alert alert-<?= $message_type ?> alert-dismissible fade show rounded-4 shadow-sm" role="alert">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="row g-4">
        <?php while($row = mysqli_fetch_assoc($trainers)): ?>
        <div class="col-md-6 reveal">
            <div class="glass-card-light">
                <div class="d-flex align-items-center mb-3">
                    <div class="trainer-avatar me-3">
                        <?= strtoupper(substr($row['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold"><?= htmlspecialchars($row['full_name']) ?></h5>
                        <small class="text-primary fw-semibold"><?= htmlspecialchars($row['specialization']) ?></small>
                    </div>
                </div>
                <?php if (!empty($row['bio'])): ?>
                <p class="small text-muted fst-italic mb-3" style="border-left: 3px solid var(--accent-primary); padding-left: 10px;">
                    "<?= htmlspecialchars($row['bio']) ?>"
                </p>
                <?php endif; ?>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">📧 Email</small>
                        <span><?= htmlspecialchars($row['email']) ?></span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">📞 Phone</small>
                        <span><?= htmlspecialchars($row['phone']) ?></span>
                    </div>
                </div>
                <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.05); font-size:0.85rem;">
                    <strong class="d-block mb-1">🗓️ Schedule:</strong>
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($row['schedule'])) ?></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="action-btn action-btn-edit btn-edit-trainer"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['full_name'], ENT_QUOTES) ?>"
                        data-email="<?= htmlspecialchars($row['email'], ENT_QUOTES) ?>"
                        data-phone="<?= htmlspecialchars($row['phone'], ENT_QUOTES) ?>"
                        data-spec="<?= htmlspecialchars($row['specialization'], ENT_QUOTES) ?>"
                        data-schedule="<?= htmlspecialchars($row['schedule'], ENT_QUOTES) ?>"
                        data-bio="<?= htmlspecialchars($row['bio'] ?? '', ENT_QUOTES) ?>">
                        <i class="bi bi-pencil-square"></i> Edit
                    </button>
                    <button class="action-btn action-btn-delete btn-delete-trainer"
                        data-id="<?= $row['id'] ?>"
                        data-name="<?= htmlspecialchars($row['full_name'], ENT_QUOTES) ?>">
                        <i class="bi bi-trash3"></i> Delete
                    </button>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<style>
.trainer-avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: linear-gradient(135deg, #6c63ff, #00e676);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; font-weight: 700; color: #fff;
    flex-shrink: 0;
}
.action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; font-size: 0.8rem;
    font-weight: 600; border: none; cursor: pointer;
    transition: all 0.2s ease; white-space: nowrap;
}
.action-btn-edit {
    background: linear-gradient(135deg, #4f8ef7, #6c63ff);
    color: #fff; box-shadow: 0 2px 8px rgba(108,99,255,0.35);
}
.action-btn-edit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(108,99,255,0.5); color:#fff; }
.action-btn-delete {
    background: linear-gradient(135deg, #ff5252, #ff1744);
    color: #fff; box-shadow: 0 2px 8px rgba(255,82,82,0.35);
}
.action-btn-delete:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(255,23,68,0.5); color:#fff; }
.action-btn:active { transform: scale(0.96); }
</style>
<div class="modal fade" id="addTrainerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-4">
            <h4 class="fw-bold mb-4">➕ Add New Trainer</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="full_name" class="form-control rounded-3" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Phone</label>
                        <input type="text" name="phone" class="form-control rounded-3">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Specialization</label>
                    <input type="text" name="specialization" class="form-control rounded-3" placeholder="e.g. Strength, Yoga, Cardio">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Schedule</label>
                    <textarea name="schedule" class="form-control rounded-3" rows="2"></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Bio <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="bio" class="form-control rounded-3" rows="2" placeholder="Short intro about the trainer..."></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" name="add_trainer" class="btn btn-gradient rounded-pill py-2">Save Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="editTrainerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-4">
            <h4 class="fw-bold mb-4">✏️ Edit Trainer</h4>
            <form method="POST">
                <input type="hidden" name="trainer_id" id="edit_trainer_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Full Name</label>
                    <input type="text" name="full_name" id="edit_trainer_name" class="form-control rounded-3" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" id="edit_trainer_email" class="form-control rounded-3">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Phone</label>
                        <input type="text" name="phone" id="edit_trainer_phone" class="form-control rounded-3">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Specialization</label>
                    <input type="text" name="specialization" id="edit_trainer_spec" class="form-control rounded-3">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Schedule</label>
                    <textarea name="schedule" id="edit_trainer_schedule" class="form-control rounded-3" rows="2"></textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Bio <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="bio" id="edit_trainer_bio" class="form-control rounded-3" rows="2" placeholder="Short intro about the trainer..."></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" name="edit_trainer" class="btn btn-gradient rounded-pill py-2">Update Trainer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteTrainerModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="glass-card modal-content border-0 p-4 text-center">
            <div style="font-size:3rem;">🗑️</div>
            <h5 class="fw-bold mt-2">Remove Trainer?</h5>
            <p class="text-muted small" id="delete_trainer_name"></p>
            <p class="text-danger small">This cannot be undone.</p>
            <form method="POST">
                <input type="hidden" name="trainer_id" id="delete_trainer_id">
                <div class="d-flex gap-2 justify-content-center mt-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="delete_trainer" class="btn btn-danger rounded-pill px-4">Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit-trainer').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_trainer_id').value       = this.dataset.id;
            document.getElementById('edit_trainer_name').value     = this.dataset.name;
            document.getElementById('edit_trainer_email').value    = this.dataset.email;
            document.getElementById('edit_trainer_phone').value    = this.dataset.phone;
            document.getElementById('edit_trainer_spec').value     = this.dataset.spec;
            document.getElementById('edit_trainer_schedule').value = this.dataset.schedule;
            document.getElementById('edit_trainer_bio').value      = this.dataset.bio;
            new bootstrap.Modal(document.getElementById('editTrainerModal')).show();
        });
    });
    document.querySelectorAll('.btn-delete-trainer').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('delete_trainer_id').value = this.dataset.id;
            document.getElementById('delete_trainer_name').textContent = '"' + this.dataset.name + '"';
            new bootstrap.Modal(document.getElementById('deleteTrainerModal')).show();
        });
    });
});
</script>
<?php include("../../footer.php"); ?>