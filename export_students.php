<?php
include("session_check.php");
include("db.php");
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'warden') {
    die("Unauthorized access.");
}
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="students.csv"');
$output = fopen("php://output", "w");
fputcsv($output, ['Name', 'Email', 'Phone']);
$result = mysqli_query($conn, "SELECT CONCAT(first_name,' ',COALESCE(last_name,'')) as full_name, email, phone FROM users WHERE role='student'");
while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [$row['full_name'], $row['email'], $row['phone']]);
}
fclose($output);
exit;