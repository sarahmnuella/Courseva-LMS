<?php
// Session Check untuk autentikasi
session_start();

// Cek apakah user sudah login
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/functions.php';
        $basePath = getBasePath();
        header("Location: {$basePath}/login.php");
        exit();
    }
}

// Cek role tertentu
function requireRole($allowedRoles) {
    requireLogin();
    
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/functions.php';
        $basePath = getBasePath();
        header("Location: {$basePath}/index.php");
        exit();
    }
}

// Cek role peserta
function requirePeserta() {
    requireRole(['peserta']);
}

// Cek role pengajar
function requirePengajar() {
    requireRole(['pengajar', 'admin']);
}

// Cek role admin
function requireAdmin() {
    requireRole(['admin']);
}

// Get current user info
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $conn = getDBConnection();
    $userId = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    return $user;
}
?>

