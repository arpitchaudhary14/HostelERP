<?php
require_once __DIR__ . "/security_config.php";
$role = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HostelERP</title>
    <link rel="icon" type="image/x-icon" href="/WebTechProject/assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css">
    <script>
        (function(){
            var t = localStorage.getItem('hostelerp-theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body class="d-flex flex-column min-vh-100 inner-bg">
<nav class="navbar navbar-expand-lg navbar-dark navbar-glass">
<div class="container-fluid">
<a class="navbar-brand d-flex align-items-center" href="/WebTechProject/index.php">
<img src="/WebTechProject/assets/images/logo.png" height="30" class="me-2" alt="HostelERP">
HostelERP
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto align-items-center">
<li class="nav-item">
<a class="nav-link" href="/WebTechProject/index.php">Home</a>
</li>
<?php if($role=="student"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   data-bs-auto-close="outside"
   aria-expanded="false">Student Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="/WebTechProject/profile.php">Profile</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/leave_request.php">Leave Request</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/my_leaves.php">My Leaves</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/notices.php">Notices</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/submit_complaint.php">Submit Complaint</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/my_complaints.php">My Complaints</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/attendance.php">Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/fees.php">Fees</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/my_room.php">My Room</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/feedback.php">Submit Feedback</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="/WebTechProject/student/visitors.php">Visitors</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/parcels.php">Parcels</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/mess_menu.php">Mess Menu</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/notifications.php">Notifications</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/documents.php">Documents</a></li>
</ul>
</li>
<?php } ?>
<?php if($role=="warden"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   data-bs-auto-close="outside"
   aria-expanded="false">Warden Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="/WebTechProject/profile.php">Profile</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/manage_students.php">Manage Students</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/assign_rooms.php">Assign Rooms</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/manage_rooms.php">Manage Rooms</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/mark_attendance.php">Mark Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/manage_complaints.php">Manage Complaints</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/manage_leaves.php">Approve Leaves</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/collect_fees.php">Collect Fees</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/notices.php">Post Notice</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/feedback.php">Submit Feedback</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/visitors.php">Manage Visitors</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/parcels.php">Manage Parcels</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/mess_menu.php">Mess Menu</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/notifications.php">Notifications</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/documents.php">Verify Documents</a></li>
</ul>
</li>
<?php } ?>
<?php if($role=="admin"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   data-bs-auto-close="outside"
   aria-expanded="false">Admin Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<li><a class="dropdown-item" href="/WebTechProject/profile.php">Profile</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/manage_users.php">Manage Users</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/manage_rooms.php">Manage Rooms</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/room_history.php">Room History</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/manage_fees.php">Manage Fees</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/system_settings.php">System Settings</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/settings.php">Hostel Settings</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/reports.php">Reports</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/view_feedback.php">View Feedback</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/activity_logs.php">Activity Logs</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/notices.php">Post Notice</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/visitors_records.php">Visitor Records</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/parcels_records.php">Parcel Records</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/mess_menu.php">Mess Menu</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/notifications.php">Notifications</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/documents.php">Verify Documents</a></li>
</ul>
</li>
<?php } ?>
<li class="nav-item me-2">
<button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
    <span class="theme-icon">🌙</span>
    <span class="theme-label">Dark</span>
</button>
</li>
<li class="nav-item">
<a class="nav-link" href="/WebTechProject/contact.php">Contact Us</a>
</li>
<li class="nav-item">
<a class="nav-link" href="/WebTechProject/logout.php" style="color: var(--accent-danger) !important;">Logout</a>
</li>
</ul>
</div>
</div>
</nav>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    var dropdownTriggerList = document.querySelectorAll('.dropdown-toggle');
    dropdownTriggerList.forEach(function (dropdownToggleEl) {
        new bootstrap.Dropdown(dropdownToggleEl);
    });
});
</script>