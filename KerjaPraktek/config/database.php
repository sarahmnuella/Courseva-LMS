<?php
/**
 * ByteForge LMS - Database Configuration
 * Learning Management System Configuration File
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ByteForgeDB');
define('DB_PORT', 3306);

// Base Path Configuration
if (!defined('BASE_PATH')) {
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $scriptPath = str_replace('\\', '/', $scriptPath);
    
    // Jika file ada di root htdocs, gunakan '/KerjaPraktek'
    // Jika ada di subfolder, ambil folder project
    if (strpos($scriptPath, '/KerjaPraktek/') !== false) {
        define('BASE_PATH', '/KerjaPraktek');
    } else {
        // Deteksi dari SCRIPT_NAME
        $parts = explode('/', trim($scriptPath, '/'));
        if (count($parts) > 0 && $parts[0] != '') {
            define('BASE_PATH', '/' . $parts[0]);
        } else {
            define('BASE_PATH', '/KerjaPraktek'); // Default
        }
    }
}

/**
 * Get Database Connection
 * 
 * @return mysqli Database connection object
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($conn->connect_error) {
                $errorMsg = "Koneksi database gagal: " . $conn->connect_error;
                $errorMsg .= "<br><br><strong>Solusi:</strong>";
                $errorMsg .= "<br>1. Pastikan XAMPP Control Panel sudah dibuka";
                $errorMsg .= "<br>2. Klik 'Start' pada MySQL di XAMPP Control Panel";
                $errorMsg .= "<br>3. Pastikan database 'ByteForgeDB' sudah dibuat";
                $errorMsg .= "<br>4. Import file ByteForge_schema.sql ke phpMyAdmin";
                $errorMsg .= "<br>5. Periksa konfigurasi DB_HOST, DB_USER, DB_PASS di config.php";
                die($errorMsg);
            }
            
            // Set charset ke utf8mb4 untuk mendukung emoji dan karakter unicode
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            $errorMsg = "Error koneksi database: " . $e->getMessage();
            $errorMsg .= "<br><br><strong>Kemungkinan penyebab:</strong>";
            $errorMsg .= "<br>1. MySQL service di XAMPP belum di-start";
            $errorMsg .= "<br>2. Port MySQL (" . DB_PORT . ") sedang digunakan aplikasi lain";
            $errorMsg .= "<br>3. Database 'ByteForgeDB' belum dibuat";
            $errorMsg .= "<br>4. Username atau password database salah";
            $errorMsg .= "<br><br><strong>Solusi:</strong>";
            $errorMsg .= "<br>1. Buka XAMPP Control Panel";
            $errorMsg .= "<br>2. Klik 'Start' pada MySQL";
            $errorMsg .= "<br>3. Buka phpMyAdmin (http://localhost/phpmyadmin)";
            $errorMsg .= "<br>4. Buat database baru dengan nama 'ByteForgeDB'";
            $errorMsg .= "<br>5. Import file ByteForge_schema.sql";
            $errorMsg .= "<br>6. Refresh halaman ini";
            die($errorMsg);
        }
    }
    
    return $conn;
}

/**
 * Execute SELECT Query
 * 
 * @param string $query SQL query string
 * @param string $types Parameter types (e.g., "si" for string and integer)
 * @param array $params Parameters to bind
 * @return mysqli_result|false Query result
 */
function executeQuery($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error executing query: " . $stmt->error);
        die("Error executing query: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

/**
 * Execute INSERT Query and return last insert ID
 * 
 * @param string $query SQL INSERT query
 * @param string $types Parameter types
 * @param array $params Parameters to bind
 * @return int Last inserted ID
 */
function executeInsert($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error executing insert: " . $stmt->error);
        die("Error executing insert: " . $stmt->error);
    }
    
    $insertId = $conn->insert_id;
    $stmt->close();
    
    return $insertId;
}

/**
 * Execute UPDATE/DELETE Query
 * 
 * @param string $query SQL UPDATE or DELETE query
 * @param string $types Parameter types
 * @param array $params Parameters to bind
 * @return int Number of affected rows
 */
function executeUpdate($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("Error preparing statement: " . $conn->error);
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Error executing update: " . $stmt->error);
        die("Error executing update: " . $stmt->error);
    }
    
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}

/**
 * Check if database connection is active
 * 
 * @return bool True if connected, false otherwise
 */
function isDatabaseConnected() {
    try {
        $conn = getDBConnection();
        return $conn->ping();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Close database connection
 */
function closeDBConnection() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->close();
    }
}

/**
 * Sanitize input data
 * 
 * @param string $data Input data to sanitize
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password against hash
 * 
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Test database connection on file include (optional, comment out in production)
// if (isDatabaseConnected()) {
//     echo "<!-- Database ByteForgeDB connected successfully -->";
// }
?>