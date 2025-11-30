<?php
/**
 * Database Connection and Schema Management
 * Production-ready database handler with migrations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/logger.php';

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            Logger::info('Database connection established');
        } catch (PDOException $e) {
            // Don't throw - allow site to work with sample data
            // Just log the error and set pdo to null
            $errorMsg = $e->getMessage();
            Logger::warning('Database connection failed, using sample data', [
                'error' => $errorMsg,
                'host' => DB_HOST,
                'database' => DB_NAME
            ]);
            $this->pdo = null;
        } catch (Exception $e) {
            Logger::warning('Database initialization failed', ['error' => $e->getMessage()]);
            $this->pdo = null;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute a prepared statement
     */
    public function query($sql, $params = []) {
        if (!$this->pdo) {
            throw new Exception('Database not available. Please run setup.php to initialize the database.');
        }
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Database query failed', [
                'sql' => $sql,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Insert and return last insert ID
     */
    public function insert($table, $data) {
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":$field";
        }, $fields);
        
        $sql = "INSERT INTO `{$table}` (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update records
     */
    public function update($table, $data, $where, $whereParams = []) {
        $set = [];
        foreach (array_keys($data) as $field) {
            $set[] = "`{$field}` = :{$field}";
        }
        
        $sql = "UPDATE `{$table}` SET " . implode(', ', $set) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);
        return $this->query($sql, $params);
    }
    
    /**
     * Delete records
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->query($sql, $params);
    }
    
    /**
     * Run migrations
     */
    public function migrate() {
        if (!$this->pdo) {
            throw new Exception('Database not available. Please check your database configuration and ensure MySQL is running.');
        }
        
        Logger::info('Starting database migration');
        
        $migrations = [
            '001_create_users_table.sql',
            '002_create_vendors_table.sql',
            '003_create_reviews_table.sql',
            '004_create_city_buddies_table.sql',
            '005_create_bookings_table.sql',
            '006_create_sessions_table.sql',
            '007_create_audit_log_table.sql',
            '008_create_products_table.sql',
            '009_create_coupons_table.sql'
        ];
        
        $executed = 0;
        $failed = 0;
        
        foreach ($migrations as $migration) {
            $file = __DIR__ . '/../database/migrations/' . $migration;
            if (file_exists($file)) {
                $sql = file_get_contents($file);
                try {
                    $this->pdo->exec($sql);
                    Logger::info("Migration executed: {$migration}");
                    $executed++;
                } catch (PDOException $e) {
                    // Check if table already exists (error 1050)
                    if (strpos($e->getMessage(), 'already exists') === false && strpos($e->getCode(), '42S01') === false) {
                        Logger::warning("Migration failed: {$migration}", ['error' => $e->getMessage()]);
                        $failed++;
                    } else {
                        Logger::info("Migration skipped (table exists): {$migration}");
                    }
                }
            } else {
                Logger::warning("Migration file not found: {$migration}");
            }
        }
        
        Logger::info("Database migration completed. Executed: {$executed}, Failed: {$failed}");
        
        if ($failed > 0 && $executed === 0) {
            throw new Exception("All migrations failed. Please check your database configuration and permissions.");
        }
    }
}

