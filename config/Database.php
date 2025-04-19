<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            // Use the connection URL directly for more reliable connection
            $this->connection = new PDO(DB_URL);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Create the leads table if it doesn't exist (PostgreSQL syntax)
            $this->connection->exec("
                CREATE TABLE IF NOT EXISTS leads (
                    id SERIAL PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    phone VARCHAR(50),
                    message TEXT,
                    source VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );
                
                DO $$
                BEGIN
                    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'leads' AND indexname = 'idx_created_at') THEN
                        CREATE INDEX idx_created_at ON leads(created_at);
                    END IF;
                    
                    IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename = 'leads' AND indexname = 'idx_email') THEN
                        CREATE INDEX idx_email ON leads(email);
                    END IF;
                END
                $$;
            ");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    private function __wakeup() {}
} 