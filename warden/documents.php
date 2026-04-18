<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'warden') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $doc_id = intval($_POST['doc_id']);
    $action = $_POST['action'];
    if($action == 'verify') {
        mysqli_query($conn, "UPDATE documents SET status='Verified', verified_by=$user_id WHERE id=$doc_id");
    } elseif($action == 'reject') {
        mysqli_query($conn, "UPDATE documents SET status='Rejected', verified_by=$user_id WHERE id=$doc_id");
    }
    header("Location: documents.php");
    exit();
}
$documents = mysqli_query($conn, "SELECT d.*, u.first_name, u.last_name, u.username, v.first_name as v_fn, v.last_name as v_ln FROM documents d JOIN users u ON d.user_id = u.id LEFT JOIN users v ON d.verified_by = v.id ORDER BY d.uploaded_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
        <h4 style="font-weight:700; color:#1a1a2e;">Student Documents Verification</h4>
        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>File</th>
                        <th>Status</th>
                        <th>Verified By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($documents)): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['uploaded_at']))) ?></td>
                        <td><?= htmlspecialchars($row['first_name'].' '.$row['last_name']. ' ('.$row['username'].')') ?></td>
                        <td><?= htmlspecialchars($row['document_type']) ?></td>
                        <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View File</a></td>
                        <td><span class="badge bg-<?= $row['status'] == 'Verified' ? 'success' : ($row['status'] == 'Rejected' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                        <td><?= $row['verified_by'] ? htmlspecialchars($row['v_fn'].' '.$row['v_ln']) : '--' ?></td>
                        <td>
                            <?php if($row['status'] == 'Pending'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="doc_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="verify" class="btn btn-sm btn-success">Verify</button>
                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                            <?php else: ?>
                            <small class="text-muted">Reviewed</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($documents) == 0): ?>
                    <tr><td colspan="7" class="text-center">No documents found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>