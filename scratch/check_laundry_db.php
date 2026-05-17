<?php
require_once 'db.php';
$tables = ['laundry_plans', 'laundry_subscriptions', 'laundry_requests'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "Table $table exists.\n";
    } else {
        echo "Table $table DOES NOT exist.\n";
    }
}
?>