<?php
session_start();
require_once '../../db.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_knowledge'])) {
        $category = $_POST['category'];
        $content = $_POST['content'];
        $stmt = $conn->prepare("INSERT INTO chatbot_knowledge (category, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $category, $content);
        if ($stmt->execute()) {
            $message = "Official guideline published successfully!";
        } else {
            $error = "Error publishing guideline.";
        }
    } elseif (isset($_POST['edit_knowledge'])) {
        $id = $_POST['id'];
        $category = $_POST['category'];
        $content = $_POST['content'];
        $stmt = $conn->prepare("UPDATE chatbot_knowledge SET category = ?, content = ? WHERE id = ?");
        $stmt->bind_param("ssi", $category, $content, $id);
        if ($stmt->execute()) {
            $message = "Official record updated successfully!";
        } else {
            $error = "Error updating record.";
        }
    } elseif (isset($_POST['delete_knowledge'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM chatbot_knowledge WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Guideline removed from official registry.";
        } else {
            $error = "Error removing record.";
        }
    }
}
$knowledge = $conn->query("SELECT * FROM chatbot_knowledge ORDER BY category ASC");
include '../../header.php';
?>
<style>
    .registry-header {
        padding: 1.5rem 0;
        margin-bottom: 2rem;
    }
    .status-badge {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    .policy-content {
        font-size: 0.9rem;
        color: var(--inner-heading);
        line-height: 1.5;
    }
    .module-sync-banner {
        background: rgba(59, 130, 246, 0.05);
        border-left: 4px solid #3b82f6;
        padding: 1rem;
        margin-top: 2rem;
        border-radius: 0 8px 8px 0;
    }
</style>
<div class="container mt-5 pt-4">
    <div class="registry-header reveal">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="fw-bold mb-1"><i class="bi bi-shield-check me-2 text-primary"></i> System Knowledge Registry</h2>
                <p class="text-muted mb-0">Official guidelines and operational protocols for the HostelERP ecosystem.</p>
            </div>
            <div class="text-end">
                <a href="../dashboard.php" class="btn btn-outline-secondary btn-sm mb-2 me-2"><i class="bi bi-arrow-left me-1"></i> Exit Registry</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle me-2"></i> Publish Guideline
                </button>
            </div>
        </div>
    </div>
    <?php if($message): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <div class="glass-card-light p-0 overflow-hidden mb-4">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted">
                    <th class="ps-4" style="width: 200px;">Protocol Category</th>
                    <th>Official Guideline / Policy Detail</th>
                    <th style="width: 170px;">Last Update</th>
                    <th class="text-end pe-4" style="width: 220px;">Management</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $knowledge->fetch_assoc()): ?>
                <tr>
                    <td class="ps-4"><span class="status-badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                    <td class="policy-content"><?php echo nl2br(htmlspecialchars(substr($row['content'], 0, 150))); ?>...</td>
                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($row['updated_at'])); ?></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-primary px-3 me-2" onclick='editEntry(<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>)'>
                            Edit
                        </button>
                        <button class="btn btn-sm btn-outline-danger px-3" onclick="deleteEntry(<?php echo $row['id']; ?>)">
                            Remove
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="module-sync-banner mb-5">
        <h6 class="fw-bold mb-1"><i class="bi bi-cpu me-2"></i> Automated System Integration</h6>
        <p class="mb-0 text-muted small">All guidelines published here are instantly synchronized with the AI Assistant and System Documentation. Global data audit is active across Gym, Library, and Administrative modules.</p>
    </div>
</div>
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content glass-card-light border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Publish Guideline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">PROTOCOL CATEGORY</label>
                    <input type="text" name="category" class="form-control bg-light border-0" placeholder="e.g., Security Protocols" required style="border-radius: 8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">GUIDELINE CONTENT</label>
                    <textarea name="content" class="form-control bg-light border-0" rows="6" placeholder="Provide detailed official instruction..." required style="border-radius: 8px;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="submit" name="add_knowledge" class="btn btn-primary" style="border-radius: 8px;">Publish to Registry</button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" class="modal-content glass-card-light border-0">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Update Official Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">PROTOCOL CATEGORY</label>
                    <input type="text" name="category" id="edit_category" class="form-control bg-light border-0" required style="border-radius: 8px;">
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">GUIDELINE CONTENT</label>
                    <textarea name="content" id="edit_content" class="form-control bg-light border-0" rows="6" required style="border-radius: 8px;"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                <button type="submit" name="edit_knowledge" class="btn btn-primary" style="border-radius: 8px;">Apply Changes</button>
            </div>
        </form>
    </div>
</div>
<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="id" id="delete_id">
    <input type="hidden" name="delete_knowledge" value="1">
</form>
<script>
function editEntry(entry) {
    document.getElementById('edit_id').value = entry.id;
    document.getElementById('edit_category').value = entry.category;
    document.getElementById('edit_content').value = entry.content;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
function deleteEntry(id) {
    if(confirm('Warning: Removing this guideline will permanently delete it from the official system registry. Proceed?')) {
        document.getElementById('delete_id').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>
<?php include '../../footer.php'; ?>