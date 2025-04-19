<?php
define('BACKEND_ACCESS', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

// Set headers
header('Content-Type: application/json');

// Basic authentication
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] !== 'admin' || $_SERVER['PHP_AUTH_PW'] !== 'leadminnow2025') {
    header('WWW-Authenticate: Basic realm="Admin Access"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();

    // Get page number and limit
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;

    // Get total count
    $count_stmt = $db->query("SELECT COUNT(*) as total FROM leads");
    $total = $count_stmt->fetch()['total'];

    // Get submissions with pagination
    $stmt = $db->prepare("SELECT * FROM leads ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $submissions = $stmt->fetchAll();

    // Send response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'submissions' => $submissions,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ]
        ]
    ]);

} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch submissions']);
} 