<?php
define('BACKEND_ACCESS', true);

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');

// Route the request
switch ($path) {
    case 'api/submit':
        require __DIR__ . '/api/submit.php';
        break;
        
    case 'api/get-submissions':
        require __DIR__ . '/api/get-submissions.php';
        break;
        
    case 'test-db':
        require __DIR__ . '/test_db.php';
        break;
        
    case 'phpinfo':
        phpinfo();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
} 