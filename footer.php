<footer class="footer-glass mt-auto pt-5 pb-3">
<div class="container">
<div class="row">
<div class="col-md-4 mb-4">
<h5>HostelERP</h5>
<p>
HostelERP is a modern hostel management system designed to simplify
room allocation, complaints, attendance, and hostel administration
through a centralized digital platform.
</p>
</div>
<div class="col-md-2 mb-4">
<h6>Quick Links</h6>
<ul class="list-unstyled">
<li><a href="/WebTechProject/index.php">Home</a></li>
<li><a href="/WebTechProject/login.php">Login</a></li>
<li><a href="/WebTechProject/register.php">Register</a></li>
<li><a href="/WebTechProject/contact.php">Contact</a></li>
</ul>
</div>
<div class="col-md-3 mb-4">
<h6>Features</h6>
<ul class="list-unstyled" style="color: var(--text-muted-light);">
<?php 
$f_link = isset($_SESSION['user_id']) ? '' : '/WebTechProject/login.php'; 
$f_role = $_SESSION['role'] ?? 'guest';
?>
<?php if ($f_role === 'warden'): ?>
<li><a href="<?= $f_link ?: '/WebTechProject/warden/my_attendance.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">My Attendance</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/warden/mark_attendance.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Mark Attendance</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/warden/attendance_correction.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Corrections</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/warden/my_leaves.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">My Leaves</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/warden/leave_requests.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Student Leaves</a></li>
<?php elseif ($f_role === 'admin'): ?>
<li><a href="<?= $f_link ?: '/WebTechProject/admin/warden_attendance.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Warden Attendance</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/admin/manage_warden_leaves.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Manage Leaves</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/admin/manage_corrections.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Manage Corrections</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/admin/manage_users.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">User Management</a></li>
<?php else: ?>
<li><a href="<?= $f_link ?: '/WebTechProject/student/my_room.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Room Management</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/student/submit_complaint.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Complaints System</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/student/leave_request.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Leave Requests</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/student/attendance.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Attendance Tracking</a></li>
<li><a href="<?= $f_link ?: '/WebTechProject/student/attendance_correction.php' ?>" style="color:var(--text-muted-light); text-decoration:none;">Corrections</a></li>
<?php endif; ?>
</ul>
</div>
<div class="col-md-3 mb-4">
<h6>Contact</h6>
<p class="mb-1">📧 support@hostelerp.com</p>
<p class="mb-1">📞 +91 9876543210</p>
<p>📍 India</p>
</div>
</div>
<hr style="border-color: rgba(255,255,255,0.08);">
<div class="text-center">
© <?php echo date("Y"); ?> HostelERP All Rights Reserved. |
<a href="#" style="color: var(--accent-info) !important;"
data-bs-toggle="modal" data-bs-target="#privacyModal">
Privacy Policy
</a> |
<a href="#" style="color: var(--accent-info) !important;"
data-bs-toggle="modal" data-bs-target="#termsModal">
Terms & Conditions
</a>
</div>
</div>
</footer>
<div class="modal fade modal-glass" id="privacyModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Privacy Policy</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<p><strong>Effective Date:</strong> February 27, 2026</p>
<div id="privacyShort">
<p>
At HostelERP, we are committed to protecting the privacy of our users.
This Privacy Policy explains how we collect, use, and safeguard information
within our hostel management application.
</p>
<button class="btn btn-sm btn-gradient" onclick="togglePrivacy()" style="width:auto; padding:8px 20px;">Read More</button>
</div>
<div id="privacyFull" style="display:none;">
<h6>Information Collection</h6>
<p>
Personal Identification: Name, Roll Number, Course/Department,
Contact Details.
</p>
<p>
Hostel Records: Room assignments, attendance logs, fee payment history.
</p>
<p>
Usage Data: Log files, device information, IP addresses for security monitoring.
</p>
<h6>How We Use Your Information</h6>
<p>
Managing room allotments, processing leave requests, sending notifications,
and ensuring hostel security.
</p>
<h6>Data Protection</h6>
<p>
Industry-standard security practices are implemented and access is
restricted to authorized personnel only.
</p>
<button class="btn btn-sm btn-outline-light" onclick="togglePrivacy()">Read Less</button>
</div>
</div>
</div>
</div>
</div>
<div class="modal fade modal-glass" id="termsModal" tabindex="-1">
<div class="modal-dialog modal-lg modal-dialog-scrollable">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">Terms & Conditions</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
<p><strong>Last Updated:</strong> February 27, 2026</p>
<div id="termsShort">
<p>
By accessing HostelERP you agree to follow the platform rules and
maintain proper usage of the system.
</p>
<button class="btn btn-sm btn-gradient" onclick="toggleTerms()" style="width:auto; padding:8px 20px;">Read More</button>
</div>
<div id="termsFull" style="display:none;">
<h6>User Responsibilities</h6>
<p>
Users must maintain account confidentiality and provide accurate data.
</p>
<h6>System Integrity</h6>
<p>
Users must not bypass security measures or disrupt the system.
</p>
<h6>System Usage</h6>
<p>
Availability may vary due to maintenance or technical issues.
</p>
<h6>Termination</h6>
<p>
Accounts may be suspended for policy violations.
</p>
<button class="btn btn-sm btn-outline-light" onclick="toggleTerms()">Read Less</button>
</div>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo '/WebTechProject/'; ?>assets/js/app.js"></script>
<script>
function togglePrivacy(){
var shortDiv = document.getElementById("privacyShort");
var fullDiv = document.getElementById("privacyFull");
if(fullDiv.style.display === "none"){
fullDiv.style.display = "block";
shortDiv.style.display = "none";
}else{
fullDiv.style.display = "none";
shortDiv.style.display = "block";
}
}
function toggleTerms(){
var shortDiv = document.getElementById("termsShort");
var fullDiv = document.getElementById("termsFull");
if(fullDiv.style.display === "none"){
fullDiv.style.display = "block";
shortDiv.style.display = "none";
}else{
fullDiv.style.display = "none";
shortDiv.style.display = "block";
}
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/chatbot_ui.php'; ?>
</body>
</html>