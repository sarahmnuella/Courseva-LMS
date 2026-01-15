<?php
/**
 * Script untuk test registrasi dan debug
 * Akses: http://localhost/KerjaPraktek/test_register.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'includes/functions.php';

echo "<h2>Test Koneksi Database</h2>";
try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✓ Koneksi database berhasil</p>";
    
    // Test query
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Tabel 'users' ditemukan</p>";
        
        // Cek struktur tabel
        $result = $conn->query("DESCRIBE users");
        echo "<h3>Struktur Tabel Users:</h3>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test insert
        echo "<h3>Test Insert User:</h3>";
        $testEmail = 'test' . time() . '@test.com';
        $testUsername = 'test' . time();
        $testPassword = password_hash('test123456', PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nama_lengkap, email, username, password, role, status, created_at) 
                  VALUES (?, ?, ?, ?, 'peserta', 'active', NOW())";
        $stmt = $conn->prepare($query);
        $nama = 'Test User';
        $stmt->bind_param("ssss", $nama, $testEmail, $testUsername, $testPassword);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✓ Test insert berhasil!</p>";
            echo "<p>Email: $testEmail</p>";
            echo "<p>Username: $testUsername</p>";
            
            // Hapus test user
            $deleteQuery = "DELETE FROM users WHERE email = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("s", $testEmail);
            $deleteStmt->execute();
            $deleteStmt->close();
            echo "<p style='color: blue;'>Test user telah dihapus</p>";
        } else {
            echo "<p style='color: red;'>✗ Error insert: " . $stmt->error . "</p>";
            echo "<p style='color: red;'>✗ Connection error: " . $conn->error . "</p>";
        }
        $stmt->close();
        
    } else {
        echo "<p style='color: red;'>✗ Tabel 'users' tidak ditemukan!</p>";
        echo "<p>Silakan import file database.sql terlebih dahulu.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Test Functions</h2>";
echo "<p>generateCSRFToken(): " . (function_exists('generateCSRFToken') ? '✓ Ada' : '✗ Tidak ada') . "</p>";
echo "<p>sanitize(): " . (function_exists('sanitize') ? '✓ Ada' : '✗ Tidak ada') . "</p>";
echo "<p>getBasePath(): " . (function_exists('getBasePath') ? '✓ Ada' : '✗ Tidak ada') . "</p>";

if (function_exists('getBasePath')) {
    echo "<p>BASE_PATH: " . getBasePath() . "</p>";
}

echo "<hr>";
echo "<p><a href='register.php'>Kembali ke Halaman Registrasi</a></p>";
?>

