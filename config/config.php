<?php
// Prevent direct access to this file
if (!defined('BACKEND_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access forbidden');
}

// Error reporting - Enable for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Database configuration for Railway PostgreSQL
// Using public URL instead of internal
define('DB_URL', 'postgresql://postgres:CUBncQhfjiKEElpwCtZPFbWWFmPZWaRb@viaduct.proxy.rlwy.net:52316/railway');

// Parse database URL
$db_url = parse_url(DB_URL);
define('DB_HOST', $db_url['host']);
define('DB_PORT', $db_url['port']);
define('DB_NAME', ltrim($db_url['path'], '/'));
define('DB_USER', $db_url['user']);
define('DB_PASS', $db_url['pass']);

// Email configuration
define('ADMIN_EMAIL', 'aaditya@leadminnow.com');
define('FROM_EMAIL', 'noreply@leadminnow.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', '');
define('SMTP_PASS', '');

// File storage paths
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('LOG_DIR', __DIR__ . '/../logs/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024);  // 5MB

// Security settings
define('ALLOWED_ORIGINS', [
    'http://localhost',
    'http://127.0.0.1',
    'https://leadminnow.com',
    '*'  // Temporarily allow all origins for testing
]);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
session_name('LEADMINNOW_SESSION');

// Create necessary directories
$directories = [UPLOAD_DIR, LOG_DIR];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
} 