<footer class="mt-auto" style="width: 100%; padding: 20px 0; text-align: center; font-size: 0.85rem; color: rgba(255,255,255,0.6); z-index: 10;">
    <div class="container">
        &copy; <?php echo date("Y"); ?> HostelERP All Rights Reserved. | 
        <a href="#" data-bs-toggle="modal" data-bs-target="#authPrivacyModal" style="color: rgba(255,255,255,0.8); text-decoration: none; margin: 0 5px;">Privacy Policy</a> | 
        <a href="#" data-bs-toggle="modal" data-bs-target="#authTermsModal" style="color: rgba(255,255,255,0.8); text-decoration: none; margin: 0 5px;">Terms</a>
    </div>
</footer>
<div class="modal fade modal-glass" id="authPrivacyModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Privacy Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Effective Date:</strong> February 27, 2026</p>
                <p>At HostelERP, we are committed to protecting the privacy of our users. This Privacy Policy explains how we collect, use, and safeguard information within our hostel management application.</p>
                <h6>Information Collection</h6>
                <p>Personal Identification: Name, Roll Number, Course/Department, Contact Details.</p>
                <p>Hostel Records: Room assignments, attendance logs, fee payment history.</p>
                <p>Usage Data: Log files, device information, IP addresses for security monitoring.</p>
                <h6>How We Use Your Information</h6>
                <p>Managing room allotments, processing leave requests, sending notifications, and ensuring hostel security.</p>
                <h6>Data Protection</h6>
                <p>Industry-standard security practices are implemented and access is restricted to authorized personnel only.</p>
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-glass" id="authTermsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms &amp; Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Last Updated:</strong> February 27, 2026</p>
                <p>By accessing HostelERP you agree to follow the platform rules and maintain proper usage of the system.</p>
                <h6>User Responsibilities</h6>
                <p>Users must maintain account confidentiality and provide accurate data.</p>
                <h6>System Integrity</h6>
                <p>Users must not bypass security measures or disrupt the system.</p>
                <h6>System Usage</h6>
                <p>Availability may vary due to maintenance or technical issues.</p>
                <h6>Termination</h6>
                <p>Accounts may be suspended for policy violations.</p>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include __DIR__ . '/chatbot_ui.php'; ?>