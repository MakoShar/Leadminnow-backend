<?php
define('BACKEND_ACCESS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/Database.php';

try {
    // Test database connection
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    // Try to create the table
    $connection->exec("
        CREATE TABLE IF NOT EXISTS leads (
            id SERIAL PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            message TEXT,
            source VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Insert a test record
    $stmt = $connection->prepare("
        INSERT INTO leads (name, email, phone, message, source) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'Test User',
        'test@example.com',
        '1234567890',
        'This is a test message',
        'test_db.php'
    ]);
    
    // Fetch the record back
    $result = $connection->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 1");
    $row = $result->fetch();
    
    echo "Database connection successful!\n";
    echo "Test record inserted and retrieved:\n";
    print_r($row);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} 