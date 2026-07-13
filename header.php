<?php
require_once __DIR__ . "/security_config.php";
$role = $_SESSION['role'] ?? '';
// Active page detection
$_cu = strtok($_SERVER['REQUEST_URI'], '?');
$_gy = strpos($_cu, '/gym/') !== false;
$_lb = strpos($_cu, '/library/') !== false;
$_ly = strpos($_cu, '/laundry/') !== false;
$_ad = strpos($_cu, '/admin/') !== false && !$_gy && !$_lb && !$_ly;
$_wa = strpos($_cu, '/warden/') !== false && !$_gy && !$_lb && !$_ly;
$_st = strpos($_cu, '/student/') !== false && !$_gy && !$_lb && !$_ly;
$_ho = in_array($_cu, ['/','/index.php']);
function _na(bool $c): string { return $c ? ' active' : ''; }
function _ia(string $p): string { global $_cu; return ($_cu===$p) ? ' active' : ''; }
function _di(string $icon, string $href, string $label, bool $bold=false): string { global $_cu; $act = ($_cu===$href) ? ' active' : ''; $fw = $bold ? ' fw-bold' : ''; $col = $bold ? ' text-primary' : ''; return "<li><a class=\"dropdown-item{$fw}{$col}{$act}\" href=\"{$href}\"><i class=\"bi bi-{$icon} item-icon\"></i>{$label}</a></li>\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head> <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>HostelERP</title> <link rel="icon" type="image/x-icon" href="/assets/favicon.ico"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> <link rel="stylesheet" href="/assets/css/style.css?v=<?= time() ?>"> <script>(function(){var t=localStorage.getItem('hostelerp-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body class="d-flex flex-column min-vh-100 inner-bg">
<nav class="navbar navbar-expand-lg navbar-dark navbar-glass">
<div class="container-fluid">
<a class="navbar-brand d-flex align-items-center" href="/index.php">
<img src="/assets/images/logo.png" height="30" class="me-2" alt="HostelERP">
<span>HostelERP</span>
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto align-items-center">
<li class="nav-item">
<a class="nav-link<?= _na($_ho) ?>" href="/index.php"><i class="bi bi-house nav-panel-icon"></i>Home</a>
</li> <?php if($role=="student"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_st) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
<i class="bi bi-mortarboard nav-panel-icon"></i>Student Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<?= _di('grid','/student/dashboard.php','Dashboard',true) ?>
<?= _di('person-circle','/profile.php','Profile') ?>
<?= _di('calendar-plus','/student/leave_request.php','Leave Request') ?>
<?= _di('calendar-check','/student/my_leaves.php','My Leaves') ?>
<?= _di('megaphone','/student/notices.php','Notices') ?>
<?= _di('exclamation-triangle','/student/submit_complaint.php','Submit Complaint') ?>
<?= _di('chat-left-text','/student/my_complaints.php','My Complaints') ?>
<?= _di('clipboard-check','/student/attendance.php','Attendance') ?>
<?= _di('pencil-square','/student/attendance_correction.php','Correction Request') ?>
<?= _di('credit-card','/student/fees.php','Fees') ?>
<?= _di('door-open','/student/my_room.php','My Room') ?>
<?= _di('star','/student/feedback.php','Submit Feedback') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('people','/student/visitors.php','Visitors') ?>
<?= _di('box-seam','/student/parcels.php','Parcels') ?>
<?= _di('cup-hot','/student/mess_menu.php','Mess Menu') ?>
<?= _di('bell','/student/notifications.php','Notifications') ?>
<?= _di('file-earmark-text','/student/documents.php','Documents') ?>
</ul>
</li>
<?php } ?> <?php if($role=="warden"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_wa) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
<i class="bi bi-shield-person nav-panel-icon"></i>Warden Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<?= _di('grid','/warden/dashboard.php','Dashboard',true) ?>
<?= _di('person-circle','/profile.php','Profile') ?>
<?= _di('people','/warden/manage_students.php','Manage Students') ?>
<?= _di('door-open','/warden/assign_rooms.php','Assign Rooms') ?>
<?= _di('building','/warden/manage_rooms.php','Manage Rooms') ?>
<?= _di('clipboard-check','/warden/mark_attendance.php','Mark Attendance') ?>
<?= _di('exclamation-triangle','/warden/manage_complaints.php','Manage Complaints') ?>
<?= _di('calendar-check','/warden/manage_leaves.php','Approve Leaves') ?>
<?= _di('credit-card','/warden/collect_fees.php','Collect Fees') ?>
<?= _di('megaphone','/warden/notices.php','Post Notice') ?>
<?= _di('star','/warden/feedback.php','Submit Feedback') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('clipboard','/warden/my_attendance.php','My Attendance') ?>
<?= _di('calendar-plus','/warden/leave_request.php','Request Leave') ?>
<?= _di('calendar','/warden/my_leaves.php','My Leaves') ?>
<?= _di('pencil-square','/warden/attendance_correction.php','Correction Request') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('people','/warden/visitors.php','Manage Visitors') ?>
<?= _di('box-seam','/warden/parcels.php','Manage Parcels') ?>
<?= _di('cup-hot','/warden/mess_menu.php','Mess Menu') ?>
<?= _di('bell','/warden/notifications.php','Notifications') ?>
<?= _di('file-earmark-check','/warden/documents.php','Verify Documents') ?>
</ul>
</li>
<?php } ?> <?php if($role=="admin"){ ?>
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_ad) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
<i class="bi bi-shield-lock nav-panel-icon"></i>Admin Panel</a>
<ul class="dropdown-menu dropdown-menu-end">
<?= _di('grid','/admin/dashboard.php','Dashboard',true) ?>
<?= _di('person-circle','/profile.php','Profile') ?>
<?= _di('people','/admin/manage_users.php','Manage Users') ?>
<?= _di('building','/admin/manage_rooms.php','Manage Rooms') ?>
<?= _di('clock-history','/admin/room_history.php','Room History') ?>
<?= _di('credit-card','/admin/manage_fees.php','Manage Fees') ?>
<?= _di('gear','/admin/system_settings.php','System Settings') ?>
<?= _di('sliders','/admin/settings.php','Hostel Settings') ?>
<?= _di('bar-chart','/admin/reports.php','Reports') ?>
<?= _di('star','/admin/view_feedback.php','View Feedback') ?>
<?= _di('journal-text','/admin/activity_logs.php','Activity Logs') ?>
<li><a class="dropdown-item fw-bold text-primary<?= _ia('/admin/chatbot/knowledge.php') ?>" href="/admin/chatbot/knowledge.php"><i class="bi bi-shield-lock item-icon"></i>System Knowledge Registry</a></li>
<?= _di('megaphone','/admin/notices.php','Post Notice') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('clipboard-check','/admin/mark_warden_attendance.php','Mark Warden Attendance') ?>
<?= _di('calendar-check','/admin/warden_attendance.php','Warden Attendance') ?>
<?= _di('calendar-event','/admin/manage_warden_leaves.php','Warden Leaves') ?>
<?= _di('pencil-square','/admin/manage_corrections.php','Attendance Corrections') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('people','/admin/visitors_records.php','Visitor Records') ?>
<?= _di('box-seam','/admin/parcels_records.php','Parcel Records') ?>
<?= _di('cup-hot','/admin/mess_menu.php','Mess Menu') ?>
<?= _di('bell','/admin/notifications.php','Notifications') ?>
<?= _di('file-earmark-check','/admin/documents.php','Verify Documents') ?>
</ul>
</li>
<?php } ?> <?php if($role){ ?>
<!-- MatrixFit Gym -->
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_gy) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="color:var(--accent-secondary)!important;font-weight:700;">
<span class="nav-module-label"><img src="/assets/images/MatrixFit_Logo.jpeg" alt="">MatrixFit</span></a>
<ul class="dropdown-menu dropdown-menu-end">
<?php if($role=="admin"){ ?>
<?= _di('grid','/admin/gym/dashboard.php','Dashboard',true) ?>
<?= _di('person-badge','/admin/gym/my_membership.php','My Membership') ?>
<?= _di('people','/admin/gym/members.php','Members') ?>
<?= _di('list-check','/admin/gym/plans.php','Plans') ?>
<?= _di('calendar-event','/admin/gym/subscriptions.php','Subscriptions') ?>
<?= _di('person-workspace','/admin/gym/trainers.php','Trainers') ?>
<?= _di('clipboard-check','/admin/gym/attendance.php','Attendance') ?>
<?= _di('credit-card','/admin/gym/payments.php','Payments') ?>
<?= _di('tools','/admin/gym/equipment.php','Equipment') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('card-heading','/student/gym/member_card.php','My Gym Card') ?>
<?= _di('receipt','/student/gym/payment_history.php','Gym Payments') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/gym/guidelines.php','Gym Guidelines') ?>
<?php } else if($role=="warden"){ ?>
<?= _di('grid','/warden/gym/dashboard.php','Dashboard',true) ?>
<?= _di('person-badge','/warden/gym/my_membership.php','My Membership') ?>
<?= _di('list-check','/warden/gym/plans.php','Gym Plans') ?>
<?= _di('person-workspace','/warden/gym/trainers.php','Meet Trainers') ?>
<?= _di('tools','/warden/gym/equipment.php','Equipment Status') ?>
<?= _di('clipboard-check','/warden/gym/attendance.php','My Attendance') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('card-heading','/student/gym/member_card.php','My Gym Card') ?>
<?= _di('receipt','/student/gym/payment_history.php','Gym Payments') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/gym/guidelines.php','Gym Guidelines') ?>
<?php } else if($role=="student"){ ?>
<?= _di('grid','/student/gym/my_membership.php','Dashboard',true) ?>
<?= _di('list-check','/student/gym/plans.php','View Plans') ?>
<?= _di('person-workspace','/student/gym/trainers.php','Meet Trainers') ?>
<?= _di('tools','/student/gym/equipment.php','Equipment Status') ?>
<?= _di('clipboard-check','/student/gym/attendance.php','My Attendance') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('card-heading','/student/gym/member_card.php','My Gym Card') ?>
<?= _di('receipt','/student/gym/payment_history.php','Gym Payments') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/gym/guidelines.php','Gym Guidelines') ?>
<?php } ?>
</ul>
</li> <!-- Indexia Library -->
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_lb) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="color:var(--accent-primary)!important;font-weight:700;">
<span class="nav-module-label"><img src="/assets/images/Indexia_Logo.jpeg" alt="">Indexia</span></a>
<ul class="dropdown-menu dropdown-menu-end">
<?php if($role=="admin"){ ?>
<?= _di('grid','/admin/library/dashboard.php','Dashboard',true) ?>
<?= _di('book','/admin/library/manage_books.php','Manage Books') ?>
<?= _di('folder','/admin/library/categories.php','Category Manager') ?>
<?= _di('arrow-left-right','/admin/library/borrows.php','Issue/Returns') ?>
<?= _di('exclamation-circle','/admin/library/fines.php','Fine Management') ?>
<?= _di('hand-thumbs-up','/admin/library/suggestions.php','Student Requests') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('card-heading','/student/library/member_card.php','My Library Card') ?>
<?= _di('bookmark','/student/library/my_bookings.php','My Reservations') ?>
<?= _di('receipt','/student/library/fines_history.php','My Fine History') ?>
<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item fw-bold text-primary<?= _ia('/admin/library/reports.php') ?>" href="/admin/library/reports.php"><i class="bi bi-bar-chart-line item-icon"></i>Insights &amp; Reports</a></li>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/library/guidelines.php','Library Guidelines') ?>
<?php } else if($role=="warden"){ ?>
<?= _di('grid','/warden/library/dashboard.php','Dashboard',true) ?>
<?= _di('alarm','/warden/library/overdue.php','Overdue Monitor') ?>
<?= _di('heart','/warden/library/suggestions.php','Interest Hub') ?>
<?= _di('clipboard2-check','/warden/library/audit.php','Health Audit') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('card-heading','/student/library/member_card.php','My Library Card') ?>
<?= _di('bookmark','/student/library/my_bookings.php','My Reservations') ?>
<?= _di('receipt','/student/library/fines_history.php','My Fine History') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/library/guidelines.php','Library Guidelines') ?>
<?php } else if($role=="student"){ ?>
<?= _di('grid','/student/library/dashboard.php','Dashboard',true) ?>
<?= _di('search','/student/library/catalog.php','Book Catalog') ?>
<?= _di('bookshelf','/student/library/my_bookshelf.php','My Bookshelf') ?>
<?= _di('card-heading','/student/library/member_card.php','Member Card Request') ?>
<?= _di('bookmark','/student/library/my_bookings.php','My Booking Request') ?>
<?= _di('receipt','/student/library/fines_history.php','Paid Fine List') ?>
<?= _di('lightbulb','/student/library/suggest.php','Suggest a Book') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/library/guidelines.php','Library Guidelines') ?>
<?php } ?>
</ul>
</li> <!-- Cleanly Laundry -->
<li class="nav-item dropdown">
<a class="nav-link dropdown-toggle<?= _na($_ly) ?>" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="color:#0ea5e9!important;font-weight:700;">
<span class="nav-module-label"><img src="/assets/images/Cleanly_Logo.jpeg" alt="">Cleanly</span></a>
<ul class="dropdown-menu dropdown-menu-end">
<?php if($role=="admin"){ ?>
<?= _di('grid','/admin/laundry/dashboard.php','Dashboard',true) ?>
<?= _di('tag','/admin/laundry/plans.php','Service Tiers &amp; Pricing') ?>
<?= _di('arrow-clockwise','/admin/laundry/requests.php','Order Lifecycle Manager') ?>
<?= _di('journal-check','/admin/laundry/payments.php','Transaction Ledger') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('basket','/warden/laundry/requests.php','Collection Hub') ?>
<?= _di('truck','/warden/laundry/ready.php','Dispatch Center') ?>
<?= _di('robot','/admin/laundry/machines.php','Machine Monitor') ?>
<?= _di('clipboard2-check','/admin/laundry/audit.php','Quality Audit') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('qr-code','/student/laundry/member_card.php','Digital Laundry Pass') ?>
<?= _di('droplet-half','/student/laundry/request.php','Submit Wash Request') ?>
<?= _di('clock-history','/student/laundry/history.php','Wash History &amp; Status') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/laundry/guidelines.php','Laundry Guidelines') ?>
<?php } else if($role=="warden"){ ?>
<?= _di('grid','/warden/laundry/dashboard.php','Dashboard',true) ?>
<?= _di('basket','/warden/laundry/requests.php','Collection Hub') ?>
<?= _di('truck','/warden/laundry/ready.php','Dispatch Center') ?>
<?= _di('robot','/warden/laundry/machines.php','Machine Monitor') ?>
<?= _di('clipboard2-check','/warden/laundry/audit.php','Quality Audit') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('qr-code','/student/laundry/member_card.php','Digital Laundry Pass') ?>
<?= _di('droplet-half','/student/laundry/request.php','Submit Wash Request') ?>
<?= _di('clock-history','/student/laundry/history.php','Wash History &amp; Status') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/laundry/guidelines.php','Laundry Guidelines') ?>
<?php } else if($role=="student"){ ?>
<?= _di('grid','/student/laundry/dashboard.php','Dashboard',true) ?>
<?= _di('qr-code','/student/laundry/member_card.php','Digital Laundry Pass') ?>
<?= _di('droplet-half','/student/laundry/request.php','Submit Wash Request') ?>
<?= _di('clock-history','/student/laundry/history.php','Wash History &amp; Status') ?>
<li><hr class="dropdown-divider"></li>
<?= _di('info-circle','/laundry/guidelines.php','Laundry Guidelines') ?>
<?php } ?>
</ul>
</li>
<?php } ?> <li class="nav-item me-2">
<button class="theme-toggle" id="themeToggle" aria-label="Toggle theme"> <span class="theme-icon">🌙</span> <span class="theme-label">Dark</span>
</button>
</li>
<li class="nav-item">
<a class="nav-link<?= _ia('/contact.php') ?>" href="/contact.php"><i class="bi bi-envelope nav-panel-icon"></i>Contact Us</a>
</li>
<?php if($role){ ?>
<li class="nav-item">
<a class="nav-link" href="/logout.php" style="color:var(--accent-danger)!important;"><i class="bi bi-box-arrow-right nav-panel-icon"></i>Logout</a>
</li>
<?php } ?>
</ul>
</div>
</div>
</nav>