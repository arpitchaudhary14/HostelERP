<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}
$action = $_SESSION['action_type'] ?? 'default';
if(isset($_SESSION['action_type'])) {
    unset($_SESSION['action_type']);
}

$thankYouConfig = [
    'leave_request' => [
        'title' => 'Leave Application Submitted',
        'message' => 'Your leave application has been successfully submitted and is pending approval by the warden.',
        'icon' => 'calendar-check',
        'return_link' => '/student/my_leaves.php',
        'return_text' => 'View My Leaves'
    ],
    'complaint' => [
        'title' => 'Complaint Registered',
        'message' => 'Your complaint has been recorded. Our team will look into it shortly.',
        'icon' => 'exclamation-circle',
        'return_link' => '/student/my_complaints.php',
        'return_text' => 'Track Complaints'
    ],
    'room_swap' => [
        'title' => 'Room Swap Request Saved',
        'message' => 'Your room swap request has been submitted to the administration.',
        'icon' => 'arrow-left-right',
        'return_link' => '/student/my_room.php',
        'return_text' => 'Back to My Room'
    ],
    'gym_pass' => [
        'title' => 'Gym Pass Activated',
        'message' => 'Your MatrixFit Gym pass has been successfully purchased and activated.',
        'icon' => 'person-badge',
        'return_link' => '/student/gym/my_membership.php',
        'return_text' => 'View Membership'
    ],
    'library_reservation' => [
        'title' => 'Book Reserved',
        'message' => 'Your library book reservation request has been submitted.',
        'icon' => 'book',
        'return_link' => '/student/library/catalog.php',
        'return_text' => 'Back to Catalog'
    ],
    'laundry_request' => [
        'title' => 'Laundry Scheduled',
        'message' => 'Your laundry wash request has been scheduled successfully.',
        'icon' => 'droplet',
        'return_link' => '/student/laundry/dashboard.php',
        'return_text' => 'Go to Laundry'
    ],
    'profile_update' => [
        'title' => 'Profile Updated',
        'message' => 'Your profile details have been successfully updated.',
        'icon' => 'person-check',
        'return_link' => '/profile.php',
        'return_text' => 'Back to Profile'
    ],
    'feedback' => [
        'title' => 'Feedback Submitted',
        'message' => 'Thank you for your feedback! Your insights help us improve.',
        'icon' => 'chat-left-text',
        'return_link' => '/student/feedback.php',
        'return_text' => 'Back to Feedback'
    ],
    'attendance_correction' => [
        'title' => 'Correction Request Sent',
        'message' => 'Your attendance correction request has been submitted for review.',
        'icon' => 'calendar-plus',
        'return_link' => '/student/attendance_correction.php',
        'return_text' => 'View Requests'
    ],
    'fee_payment' => [
        'title' => 'Payment Successful',
        'message' => 'Your fee payment has been processed successfully.',
        'icon' => 'receipt',
        'return_link' => '/student/fees.php',
        'return_text' => 'View Receipt'
    ],
    'default' => [
        'title' => 'Action Successful!',
        'message' => 'Your action was completed successfully.',
        'icon' => 'check-circle-fill',
        'return_link' => '/index.php',
        'return_text' => 'Back to Home'
    ]
];

$config = $thankYouConfig[$action] ?? $thankYouConfig['default'];


$pageTitle = $config['title'] . ' - HostelERP';
$pageDesc = 'Thank you for your submission on HostelERP.';

include 'header.php'; 
?>

<div class="container page-fade-in d-flex align-items-center justify-content-center" style="min-height: 70vh;">
    <div class="glass-card-light p-5 reveal text-center" style="max-width: 650px; width: 100%;">
        <div class="mb-4" style="font-size: 5rem; line-height: 1; background: linear-gradient(135deg, var(--accent-secondary), #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            <i class="bi bi-<?php echo $config['icon']; ?>"></i>
        </div>
        
        <h2 style="font-weight: 800; color: var(--inner-heading);" class="mb-3">
            <?php echo $config['title']; ?>
        </h2>
        
        <p class="text-muted mb-5" style="font-size: 1.1rem; line-height: 1.6;">
            <?php echo $config['message']; ?>
        </p>
        
        <div class="d-flex justify-content-center flex-wrap gap-3">
            <a href="<?php echo $config['return_link']; ?>" class="btn-gradient px-4 py-2" style="border-radius: 50px; text-decoration: none; display: inline-flex;">
                <i class="bi bi-arrow-return-right"></i> <?php echo $config['return_text']; ?>
            </a>
            <a href="/index.php" class="btn px-4 py-2" style="border-radius: 50px; border: 2px solid rgba(108, 99, 255, 0.3); color: var(--inner-text); font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
                <i class="bi bi-house"></i> Home
            </a>
        </div>
    </div>
</div>

<style>
    .glass-card-light .btn[href="/index.php"]:hover {
        border-color: var(--accent-primary);
        background: rgba(108, 99, 255, 0.1);
        color: var(--accent-primary-light);
    }
</style>

<?php include 'footer.php'; ?>
