<?php
/**
 * SICURAH Database Configuration
 * Kelompok 9 - PSTI B
 * File konfigurasi koneksi database
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');        // Host database (biasanya localhost)
define('DB_USER', 'root');              // Username MySQL (default root di XAMPP)
define('DB_PASS', '');                  // Password MySQL (default kosong di XAMPP)
define('DB_NAME', 'MonitoringIOT');     // Nama database

// Konfigurasi Aplikasi
define('APP_NAME', 'SICURAH');
define('APP_VERSION', '1.0.0');
define('API_KEY', 'SICURAH_2024_SECRET_KEY'); // Untuk autentikasi dari perangkat IoT

// Timezone
date_default_timezone_set('Asia/Makassar');

// Error Reporting (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers (izinkan akses dari mana saja)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Class Database - Singleton pattern untuk koneksi database
 */
class Database {
    private static $instance = null;
    private $connection = null;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
        } catch (Exception $e) {
            $this->logError("Database connection error: " . $e->getMessage());
            die(json_encode([
                'success' => false,
                'error' => 'Database connection failed',
                'message' => $e->getMessage()
            ]));
        }
    }
    
    /**
     * Get database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get connection
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Escape string untuk prevent SQL injection
     */
    public function escape($value) {
        return $this->connection->real_escape_string($value);
    }
    
    /**
     * Execute query
     */
    public function query($sql) {
        $result = $this->connection->query($sql);
        
        if (!$result) {
            $this->logError("Query error: " . $this->connection->error . " | SQL: " . $sql);
            return false;
        }
        
        return $result;
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql) {
        $result = $this->query($sql);
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql) {
        $result = $this->query($sql);
        if ($result && $result->num_rows > 0) {
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            return $rows;
        }
        return [];
    }
    
    /**
     * Insert data and return ID
     */
    public function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $values = implode("', '", array_map([$this, 'escape'], array_values($data)));
        
        $sql = "INSERT INTO $table ($columns) VALUES ('$values')";
        
        if ($this->query($sql)) {
            return $this->connection->insert_id;
        }
        
        return false;
    }
    
    /**
     * Update data
     */
    public function update($table, $data, $where) {
        $set = [];
        foreach ($data as $column => $value) {
            $set[] = "$column = '" . $this->escape($value) . "'";
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
        
        return $this->query($sql);
    }
    
    /**
     * Delete data
     */
    public function delete($table, $where) {
        $sql = "DELETE FROM $table WHERE $where";
        return $this->query($sql);
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }
    
    /**
     * Log error ke file
     */
    private function logError($message) {
        $logFile = __DIR__ . '/../logs/error.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Close connection
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper Functions
 */

/**
 * Send JSON response
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Send success response
 */
function sendSuccess($message, $data = null) {
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendResponse($response, 200);
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 400, $details = null) {
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    sendResponse($response, $statusCode);
}

/**
 * Validate API key
 */
function validateApiKey() {
    $headers = getallheaders();
    $apiKey = isset($headers['X-API-Key']) ? $headers['X-API-Key'] : 
              (isset($_GET['api_key']) ? $_GET['api_key'] : null);
    
    if ($apiKey !== API_KEY) {
        sendError('Invalid or missing API key', 401);
    }
    
    return true;
}

/**
 * Get POST data
 */
function getPostData() {
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Fallback ke $_POST jika bukan JSON
        $data = $_POST;
    }
    
    return $data;
}

/**
 * Log activity
 */
function logActivity($action, $description = '', $userId = null) {
    $db = Database::getInstance();
    
    $data = [
        'user_id' => $userId,
        'action' => $action,
        'description' => $description,
        'ip_address' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];
    
    $db->insert('system_logs', $data);
}

/**
 * Get node status based on last_seen
 */
function getNodeStatus($lastSeen) {
    if (empty($lastSeen)) {
        return 'offline';
    }
    
    $lastSeenTime = strtotime($lastSeen);
    $currentTime = time();
    $diffMinutes = ($currentTime - $lastSeenTime) / 60;
    
    if ($diffMinutes > 10) {
        return 'offline';
    }
    
    return 'online';
}

/**
 * Calculate reading status based on thresholds
 */
function calculateStatus($value, $warningThreshold, $dangerThreshold) {
    if ($value >= $dangerThreshold) {
        return 'danger';
    } elseif ($value >= $warningThreshold) {
        return 'warning';
    }
    return 'safe';
}

/**
 * Format time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' detik yang lalu';
    } elseif ($diff < 3600) {
        return round($diff / 60) . ' menit yang lalu';
    } elseif ($diff < 86400) {
        return round($diff / 3600) . ' jam yang lalu';
    } else {
        return round($diff / 86400) . ' hari yang lalu';
    }
}

?>
