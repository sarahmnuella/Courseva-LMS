<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

// Hapus semua session
$_SESSION = array();

// Hapus session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect ke halaman login
$basePath = getBasePath();
header("Location: {$basePath}/login.php");
exit();
?>

