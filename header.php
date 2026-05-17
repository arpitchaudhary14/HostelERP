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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/WebTechProject/assets/css/style.css?v=<?= time(); ?>">
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
<li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/student/dashboard.php">Dashboard</a></li>
<li><a class="dropdown-item" href="/WebTechProject/profile.php">Profile</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/leave_request.php">Leave Request</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/my_leaves.php">My Leaves</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/notices.php">Notices</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/submit_complaint.php">Submit Complaint</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/my_complaints.php">My Complaints</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/attendance.php">Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/student/attendance_correction.php">Correction Request</a></li>
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
<li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/warden/dashboard.php">Dashboard</a></li>
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
<li><a class="dropdown-item" href="/WebTechProject/warden/my_attendance.php">My Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/leave_request.php">Request Leave</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/my_leaves.php">My Leaves</a></li>
<li><a class="dropdown-item" href="/WebTechProject/warden/attendance_correction.php">Correction Request</a></li>
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
<li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/dashboard.php">Dashboard</a></li>
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
<li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/chatbot/knowledge.php"><i class="bi bi-shield-lock me-2"></i> System Knowledge Registry</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/notices.php">Post Notice</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/mark_warden_attendance.php">Mark Warden Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/warden_attendance.php">Warden Attendance</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/manage_warden_leaves.php">Warden Leaves</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/manage_corrections.php">Attendance Corrections</a></li>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/visitors_records.php">Visitor Records</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/parcels_records.php">Parcel Records</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/mess_menu.php">Mess Menu</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/notifications.php">Notifications</a></li>
<li><a class="dropdown-item" href="/WebTechProject/admin/documents.php">Verify Documents</a></li>
</ul>
</li>
<?php } ?>
<?php if($role){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   aria-expanded="false"
   style="color: var(--accent-secondary) !important; font-weight: 700;">
   <img src="/WebTechProject/assets/images/MatrixFit_Logo.jpeg" height="20" class="me-1" style="border-radius: 4px;"> MatrixFit
</a>
<ul class="dropdown-menu dropdown-menu-end">
    <?php if($role=="admin"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/gym/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/my_membership.php">My Membership</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/members.php">Members</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/plans.php">Plans</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/subscriptions.php">Subscriptions</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/trainers.php">Trainers</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/attendance.php">Attendance</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/payments.php">Payments</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/gym/equipment.php">Equipment</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/member_card.php">My Gym Card</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/payment_history.php">Gym Payments</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/gym/guidelines.php">Gym Guidelines</a></li>
<?php } else if($role=="warden"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/warden/gym/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/gym/my_membership.php">My Membership</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/gym/plans.php">Gym Plans</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/gym/trainers.php">Meet Trainers</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/gym/equipment.php">Equipment Status</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/gym/attendance.php">My Attendance</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/member_card.php">My Gym Card</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/payment_history.php">Gym Payments</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/gym/guidelines.php">Gym Guidelines</a></li>
<?php } else if($role=="student"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/student/gym/my_membership.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/plans.php">View Plans</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/trainers.php">Meet Trainers</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/equipment.php">Equipment Status</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/attendance.php">My Attendance</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/member_card.php">My Gym Card</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/gym/payment_history.php">Gym Payments</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/gym/guidelines.php">Gym Guidelines</a></li>
<?php } ?>
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   aria-expanded="false"
   style="color: var(--accent-primary) !important; font-weight: 700;">
   <img src="/WebTechProject/assets/images/Indexia_Logo.jpeg" height="20" class="me-1" style="border-radius: 4px;"> Indexia
</a>
<ul class="dropdown-menu dropdown-menu-end">
    <?php if($role=="admin"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/library/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/library/manage_books.php">Manage Books</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/library/categories.php">Category Manager</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/library/borrows.php">Issue/Returns</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/library/fines.php">Fine Management</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/library/suggestions.php">Student Requests</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/member_card.php">My Library Card</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/my_bookings.php">My Reservations</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/fines_history.php">My Fine History</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/library/reports.php">Insights & Reports</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/library/guidelines.php">Library Guidelines</a></li>
    <?php } else if($role=="warden"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/warden/library/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/library/overdue.php">Overdue Monitor</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/library/suggestions.php">Interest Hub</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/library/audit.php">Health Audit</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/member_card.php">My Library Card</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/my_bookings.php">My Reservations</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/fines_history.php">My Fine History</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/library/guidelines.php">Library Guidelines</a></li>
    <?php } else if($role=="student"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/student/library/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/catalog.php">Book Catalog</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/my_bookshelf.php">My Bookshelf</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/member_card.php">Member Card Request</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/my_bookings.php">My Booking Request</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/fines_history.php">Paid Fine List</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/library/suggest.php">Suggest a Book</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/library/guidelines.php">Library Guidelines</a></li>
    <?php } ?>
</ul>
</li>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle" 
   href="javascript:void(0)" 
   role="button" 
   data-bs-toggle="dropdown"
   aria-expanded="false"
   style="color: #0ea5e9 !important; font-weight: 700;">
   <img src="/WebTechProject/assets/images/Cleanly_Logo.jpeg" height="20" class="me-1" style="border-radius: 4px;"> Cleanly
</a>
<ul class="dropdown-menu dropdown-menu-end">
    <?php if($role=="admin"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/admin/laundry/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/laundry/plans.php">Service Tiers & Pricing</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/laundry/requests.php">Order Lifecycle Manager</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/laundry/payments.php">Transaction Ledger</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/requests.php">Collection Hub</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/ready.php">Dispatch Center</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/laundry/machines.php">Machine Monitor</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/admin/laundry/audit.php">Quality Audit</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/member_card.php">Digital Laundry Pass</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/request.php">Submit Wash Request</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/history.php">Wash History & Status</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/laundry/guidelines.php">Laundry Guidelines</a></li>
    <?php } else if($role=="warden"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/warden/laundry/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/requests.php">Collection Hub</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/ready.php">Dispatch Center</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/machines.php">Machine Monitor</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/warden/laundry/audit.php">Quality Audit</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/member_card.php">Digital Laundry Pass</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/request.php">Submit Wash Request</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/history.php">Wash History & Status</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/laundry/guidelines.php">Laundry Guidelines</a></li>
    <?php } else if($role=="student"){ ?>
        <li><a class="dropdown-item fw-bold text-primary" href="/WebTechProject/student/laundry/dashboard.php">Dashboard</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/member_card.php">Digital Laundry Pass</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/request.php">Submit Wash Request</a></li>
        <li><a class="dropdown-item" href="/WebTechProject/student/laundry/history.php">Wash History & Status</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/WebTechProject/laundry/guidelines.php">Laundry Guidelines</a></li>
    <?php } ?>
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