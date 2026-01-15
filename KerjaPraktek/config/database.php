<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'courseva');
define('DB_PORT', 3306);

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

// Koneksi ke database
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
                $errorMsg .= "<br>3. Pastikan database 'courseva' sudah dibuat";
                $errorMsg .= "<br>4. Import file database.sql ke phpMyAdmin";
                die($errorMsg);
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            $errorMsg = "Error koneksi database: " . $e->getMessage();
            $errorMsg .= "<br><br><strong>Kemungkinan penyebab:</strong>";
            $errorMsg .= "<br>1. MySQL service di XAMPP belum di-start";
            $errorMsg .= "<br>2. Port MySQL (3308) sedang digunakan aplikasi lain";
            $errorMsg .= "<br>3. Database 'courseva' belum dibuat";
            $errorMsg .= "<br><br><strong>Solusi:</strong>";
            $errorMsg .= "<br>1. Buka XAMPP Control Panel";
            $errorMsg .= "<br>2. Klik 'Start' pada MySQL";
            $errorMsg .= "<br>3. Buka phpMyAdmin (http://localhost/phpmyadmin)";
            $errorMsg .= "<br>4. Buat database baru dengan nama 'courseva'";
            $errorMsg .= "<br>5. Import file database.sql";
            die($errorMsg);
        }
    }
    
    return $conn;
}

function executeQuery($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    return $result;
}

// Helper function untuk insert dan mendapatkan last insert id
function executeInsert($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $insertId = $conn->insert_id;
    $stmt->close();
    
    return $insertId;
}

// Helper function untuk update/delete
function executeUpdate($query, $types = "", $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $result = $stmt->execute();
    $affectedRows = $stmt->affected_rows;
    $stmt->close();
    
    return $affectedRows;
}
?>

