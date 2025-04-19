<?php
// Skip database initialization during composer install
if (isset($argv[0]) && strpos($argv[0], 'composer') !== false) {
    exit(0);
}

require_once 'config.php';

try {
    $pdo = get_db_connection();
    
    // Create leads table
    $sql = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        message TEXT,
        source VARCHAR(50) DEFAULT 'website',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "Database initialized successfully!\n";
    
} catch (PDOException $e) {
    die("Error initializing database: " . $e->getMessage() . "\n");
}
?> 