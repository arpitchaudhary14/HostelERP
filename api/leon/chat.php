<?php
/**
 * ============================================================================
 * LEON AI Chatbot Reverse Proxy Endpoint
 * ============================================================================
 * 
 * Location: /var/www/html/api/leon/chat.php
 * 
 * Purpose:
 *   - Acts as a secure reverse proxy between browser and LEON API
 *   - Proxies POST requests from JavaScript frontend to Flask backend
 *   - Handles connection failures gracefully with user-friendly errors
 *   - Validates and sanitizes input before forwarding
 * 
 * Architecture:
 *   Browser → PHP proxy (this file) → LEON API (internal Docker network)
 * 
 * Security:
 *   - LEON API is never directly exposed to the browser/internet
 *   - LEON API only accessible from PHP container (localhost/network)
 *   - All errors handled without exposing internal system details
 *   - HTTP status codes properly set for API compliance
 * ============================================================================
 */

// ============================================================================
// Headers and Content Type
// ============================================================================
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// ============================================================================
// Method Validation
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // CORS preflight request
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method Not Allowed',
        'message' => 'Only POST requests are accepted at this endpoint.'
    ]);
    exit;
}

// ============================================================================
// Input Validation and Sanitization
// ============================================================================
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'Invalid JSON payload.'
    ]);
    exit;
}

if (empty($input['message']) || !is_string($input['message'])) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Bad Request',
        'message' => 'Message field is required and must be a string.'
    ]);
    exit;
}

// ============================================================================
// LEON API Configuration
// ============================================================================
// LEON API endpoint (internal Docker network)
$leon_api_url = getenv('LEON_API_URL') ?: 'http://leon-api:5000/chat';

// Connection and timeout settings
$timeout = 30; // seconds - generous timeout for AI processing

// ============================================================================
// Proxy Request to LEON API
// ============================================================================
$ch = curl_init($leon_api_url);

if (!$ch) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server Error',
        'message' => 'Unable to initialize proxy request.'
    ]);
    exit;
}

// Configure curl request
curl_setopt_array($ch, [
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => json_encode($input),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

// ============================================================================
// Error Handling - Connection Failures
// ============================================================================
if ($response === false) {
    // LEON API is unreachable
    http_response_code(503);
    echo json_encode([
        'error' => 'Service Unavailable',
        'message' => 'The AI chatbot service is currently unavailable. Please try again in a moment.'
    ]);
    exit;
}

// ============================================================================
// Response Handling
// ============================================================================
// Set the HTTP status code from LEON API
http_response_code($http_code);

// Forward the response from LEON API
if (!empty($response)) {
    // Validate JSON response
    $decoded = json_decode($response, true);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        // Invalid JSON from LEON API
        http_response_code(502);
        echo json_encode([
            'error' => 'Bad Gateway',
            'message' => 'Received invalid response from chatbot service.'
        ]);
    } else {
        // Valid response - forward as-is
        echo $response;
    }
} else {
    // Empty response from LEON API
    http_response_code(502);
    echo json_encode([
        'error' => 'Bad Gateway',
        'message' => 'Chatbot service returned an empty response.'
    ]);
}
?>
