<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "db.php";
require_once "google_oauth_config.php";
if (!isset($_GET['code'])) {
    die("No authorization code received from Google.");
}
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    die("Invalid state parameter. Possible CSRF attack.");
}
$tokenResponse = file_get_contents(GOOGLE_TOKEN_URL, false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));
if ($tokenResponse === false) {
    die("Failed to get access token from Google.");
}
$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    die("Google OAuth error: " . ($tokenData['error_description'] ?? 'Unknown error'));
}
$userInfoResponse = file_get_contents(GOOGLE_USER_URL, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer {$tokenData['access_token']}\r\n"],
]));
if ($userInfoResponse === false) {
    die("Failed to get user info from Google.");
}
$googleUser = json_decode($userInfoResponse, true);
$googleId  = $googleUser['sub'];
$email     = $googleUser['email'];
$fullName  = $googleUser['name']         ?? '';
$firstName = $googleUser['given_name']   ?? '';
$lastName  = $googleUser['family_name']  ?? '';
$picture   = $googleUser['picture']      ?? 'default.png';
$stmt = $conn->prepare("SELECT * FROM users WHERE google_id = ?");
$stmt->bind_param("s", $googleId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$user) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($user) {
        $stmt = $conn->prepare("UPDATE users SET google_id = ? WHERE id = ?");
        $stmt->bind_param("si", $googleId, $user['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $username = strtolower(preg_replace('/\s+/', '', $firstName)) . '_' . substr($googleId, 0, 6);
        $stmt = $conn->prepare(
            "INSERT INTO users (full_name, first_name, last_name, email, google_id, username, role, profile_pic)
             VALUES (?, ?, ?, ?, ?, ?, 'student', ?)"
        );
        $stmt->bind_param("sssssss", $fullName, $firstName, $lastName, $email, $googleId, $username, $picture);
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
$_SESSION['user_id'] = $user['id'];
$_SESSION['role']    = $user['role'];
require_once "security_config.php";
record_login($conn, $user['id'], 'google');
header("Location: dashboard.php");
exit();
?>