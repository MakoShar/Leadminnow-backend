<?php
define('BACKEND_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

// Set headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Validate origin
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
if (!in_array($origin, ALLOWED_ORIGINS)) {
    http_response_code(403);
    echo json_encode(['error' => 'Origin not allowed']);
    exit;
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!$data) {
        throw new Exception('Invalid JSON data');
    }

    // Validate required fields
    $required_fields = ['name', 'email'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Sanitize input
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $phone = isset($data['phone']) ? filter_var($data['phone'], FILTER_SANITIZE_STRING) : '';
    $message = isset($data['message']) ? filter_var($data['message'], FILTER_SANITIZE_STRING) : '';
    $source = isset($data['source']) ? filter_var($data['source'], FILTER_SANITIZE_STRING) : 'Unknown';

    // Get database connection
    $db = Database::getInstance()->getConnection();

    // Insert into database
    $sql = "INSERT INTO leads (name, email, phone, message, source) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$name, $email, $phone, $message, $source]);

    // Save to file as backup
    $submission_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
        'source' => $source
    ];

    $filename = UPLOAD_DIR . date('Y-m-d_H-i-s') . '_' . md5(uniqid()) . '.json';
    file_put_contents($filename, json_encode($submission_data, JSON_PRETTY_PRINT));

    // Send email notification
    $to = ADMIN_EMAIL;
    $subject = "New Form Submission from " . $source;
    $email_message = "New submission received:\n\n";
    foreach ($submission_data as $key => $value) {
        $email_message .= ucfirst($key) . ": " . $value . "\n";
    }
    $headers = "From: " . FROM_EMAIL;

    mail($to, $subject, $email_message, $headers);

    // Send success response
    http_response_code(200);
    echo json_encode(['message' => 'Form submitted successfully']);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 