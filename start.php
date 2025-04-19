<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize database
require_once __DIR__ . '/init_db.php';

// Set headers for CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Route requests
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash and query string
$path = ltrim($path, '/');
$path = explode('?', $path)[0];

// Route handling
switch ($path) {
    case '':
    case 'index.php':
        echo json_encode(['status' => 'success', 'message' => 'API is running']);
        break;
        
    case 'api/submit':
        require_once __DIR__ . '/form_handler.php';
        break;
        
    case 'admin':
        require_once __DIR__ . '/admin.php';
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}
?> 