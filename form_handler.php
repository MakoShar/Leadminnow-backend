<?php
require_once 'config.php';

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get JSON data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate and sanitize input
    $name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
    $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = filter_var($data['phone'] ?? '', FILTER_SANITIZE_STRING);
    $message = filter_var($data['message'] ?? '', FILTER_SANITIZE_STRING);
    $source = filter_var($data['source'] ?? 'website', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name) || empty($email) || empty($phone)) {
        throw new Exception('Please fill in all required fields');
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Create submission data
    $submission = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'source' => $source
    ];

    // Save to database
    $id = save_submission($submission);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Form submitted successfully',
        'id' => $id
    ]);

} catch (Exception $e) {
    error_log("Form submission error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 