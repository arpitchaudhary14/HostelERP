<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db.php";
require_once "microsoft_oauth_config.php";
if (!isset($_GET['code'])) {
    die("No authorization code received from Microsoft.");
}
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['ms_oauth_state'] ?? '')) {
    die("Invalid state parameter. Possible CSRF attack.");
}
$tokenResponse = file_get_contents(MS_TOKEN_URL, false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => MS_CLIENT_ID,
            'client_secret' => MS_CLIENT_SECRET,
            'redirect_uri'  => MS_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
            'scope'         => 'openid email profile User.Read',
        ]),
    ],
]));
if ($tokenResponse === false) {
    die("Failed to get access token from Microsoft.");
}
$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    die("Microsoft OAuth error: " . ($tokenData['error_description'] ?? 'Unknown error'));
}
$userInfoResponse = file_get_contents(MS_USER_URL, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer {$tokenData['access_token']}\r\n"],
]));
if ($userInfoResponse === false) {
    die("Failed to get user info from Microsoft.");
}
$msUser = json_decode($userInfoResponse, true);
$microsoftId = $msUser['id'];
$email       = $msUser['mail'] ?? $msUser['userPrincipalName'] ?? '';
$fullName    = $msUser['displayName']  ?? '';
$firstName   = $msUser['givenName']    ?? '';
$lastName    = $msUser['surname']      ?? '';
$stmt = $conn->prepare("SELECT * FROM users WHERE microsoft_id = ?");
$stmt->bind_param("s", $microsoftId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user && !empty($email)) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($user) {
        $stmt = $conn->prepare("UPDATE users SET microsoft_id = ? WHERE id = ?");
        $stmt->bind_param("si", $microsoftId, $user['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $username = strtolower(preg_replace('/\s+/', '', $firstName)) . '_ms_' . substr($microsoftId, 0, 6);
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, first_name, last_name, email, microsoft_id, username, role)
             VALUES (?, ?, ?, ?, ?, ?, 'student')"
        );
        $stmt->bind_param("ssssss", $fullName, $firstName, $lastName, $email, $microsoftId, $username);
        $stmt->execute();
        $newId = $conn->insert_id;
        $stmt->close();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $newId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
if (!$user) {
    die("Could not find or create user account.");
}
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
require_once "security_config.php";
record_login($conn, $user['id'], 'microsoft');
header("Location: dashboard.php");
exit();
?>