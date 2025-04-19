<?php
// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

// Try to load .env file if it exists, otherwise use getenv()
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Database configuration using environment variables
$db_host = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST') ?? 'localhost';
$db_port = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306';
$db_name = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE') ?? 'leadminnow';
$db_user = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER') ?? 'root';
$db_password = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD') ?? '';

// Storage configuration
define('DATA_DIR', 'data');
define('SUBMISSIONS_FILE', DATA_DIR . '/submissions.json');
define('LOG_FILE', 'logs/form_submissions.log');

// Email configuration
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? getenv('ADMIN_EMAIL') ?? 'aaditya@leadminnow.com');
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? getenv('FROM_EMAIL') ?? 'noreply@leadminnow.com');

// Create necessary directories if they don't exist
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0777, true);
}
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

// Initialize submissions file if it doesn't exist
if (!file_exists(SUBMISSIONS_FILE)) {
    file_put_contents(SUBMISSIONS_FILE, json_encode([]));
}

// Database connection function
function get_db_connection() {
    global $db_host, $db_port, $db_name, $db_user, $db_password;
    
    try {
        $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        log_error("Database Connection Error: " . $e->getMessage());
        throw $e;
    }
}

// Function to get all submissions
function get_all_submissions() {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        log_error("Error fetching submissions: " . $e->getMessage());
        return [];
    }
}

// Function to save a submission
function save_submission($submission) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("INSERT INTO leads (name, email, phone, message, source, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $submission['name'],
            $submission['email'],
            $submission['phone'],
            $submission['message'],
            $submission['source']
        ]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        log_error("Error saving submission: " . $e->getMessage());
        throw $e;
    }
}

// Function to get a submission by ID
function get_submission_by_id($id) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        log_error("Error fetching submission: " . $e->getMessage());
        return null;
    }
}

// Function to log errors
function log_error($message) {
    $log_message = date('Y-m-d H:i:s') . " - " . $message . "\n";
    file_put_contents(LOG_FILE, $log_message, FILE_APPEND);
}
?> 