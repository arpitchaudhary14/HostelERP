<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /WebTechProject/login.php");
    exit();
}
require_once "../db.php";
$user_id = intval($_SESSION['user_id']);
$msg = ""; $error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['document'])) {
    $doc_type = mysqli_real_escape_string($conn, $_POST['document_type']);
    $file = $_FILES['document'];
    $target_dir = "../uploads/documents/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
    $filename = "uid_".$user_id."_time_".time().".".$ext;
    $target_file = $target_dir . $filename;
    $allowed = ['pdf','jpg','jpeg','png'];   
    if(!in_array(strtolower($ext), $allowed)){
        $error = "Only PDF, JPG, JPEG, and PNG files are allowed.";
    } elseif(move_uploaded_file($file["tmp_name"], $target_file)) {
        $path_db = "/WebTechProject/uploads/documents/" . $filename;
        $stmt = mysqli_prepare($conn, "INSERT INTO documents (user_id, document_type, file_path) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $doc_type, $path_db);
        if(mysqli_stmt_execute($stmt)){
            $msg = "Document uploaded successfully!";
        } else {
            $error = "Failed to save to database.";
        }
    } else {
        $error = "Failed to upload document.";
    }
}
$documents = mysqli_query($conn, "SELECT * FROM documents WHERE user_id = '$user_id' ORDER BY uploaded_at DESC");
?>
<?php include("../header.php"); ?>
<div class="container mt-4 page-fade-in">
    <div class="row">
        <div class="col-md-4">
            <div class="glass-card-light p-4 reveal">
                <h4 style="font-weight:700; color:#1a1a2e;">Upload Document</h4>
                <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label>Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="ID Proof">ID Proof</option>
                            <option value="Address Proof">Address Proof</option>
                            <option value="Admission Letter">Admission Letter</option>
                            <option value="Medical Certificate">Medical Certificate</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Select File</label>
                        <input type="file" name="document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Allowed: PDF, JPG, PNG.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Upload</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="glass-card-light p-4 reveal" style="min-height: 400px;">
                <h4 style="font-weight:700; color:#1a1a2e;">My Documents</h4>
                <div class="table-responsive mt-3">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Uploaded At</th>
                                <th>Status</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($documents)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['document_type']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['uploaded_at']))) ?></td>
                                <td><span class="badge bg-<?= $row['status'] == 'Verified' ? 'success' : ($row['status'] == 'Rejected' ? 'danger' : 'warning') ?>"><?= $row['status'] ?></span></td>
                                <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($documents) == 0): ?>
                            <tr><td colspan="4" class="text-center">No documents uploaded.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../footer.php"); ?>