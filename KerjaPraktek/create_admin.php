<?php
/**
 * Script untuk membuat user admin
 * Akses: http://localhost/KerjaPraktek/create_admin.php
 * HAPUS FILE INI SETELAH ADMIN BERHASIL DIBUAT!
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Generate password hash untuk "admin123"
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$conn = getDBConnection();

// Cek apakah admin sudah ada
$query = "SELECT id FROM users WHERE username = 'admin' OR email = 'admin@courseva.com'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Update password admin yang sudah ada
    $query = "UPDATE users SET password = ?, status = 'active', role = 'admin' WHERE username = 'admin' OR email = 'admin@courseva.com'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $hashedPassword);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Password admin berhasil diupdate!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Email:</strong> admin@courseva.com</p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error: " . $conn->error . "</h2>";
    }
    $stmt->close();
} else {
    // Insert admin baru
    $nama_lengkap = 'Administrator';
    $email = 'admin@courseva.com';
    $username = 'admin';
    $role = 'admin';
    $status = 'active';
    
    $query = "INSERT INTO users (nama_lengkap, email, username, password, role, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssss", $nama_lengkap, $email, $username, $hashedPassword, $role, $status);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Admin berhasil dibuat!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Email:</strong> admin@courseva.com</p>";
        echo "<p><a href='login.php'>Klik di sini untuk login</a></p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error: " . $conn->error . "</h2>";
    }
    $stmt->close();
}

// Test password verification
echo "<hr>";
echo "<h3>Test Password Verification:</h3>";
if (password_verify('admin123', $hashedPassword)) {
    echo "<p style='color: green;'>✓ Password verification: BERHASIL</p>";
} else {
    echo "<p style='color: red;'>✗ Password verification: GAGAL</p>";
}

echo "<hr>";
echo "<p style='color: orange;'><strong>PENTING:</strong> Hapus file create_admin.php setelah admin berhasil dibuat untuk keamanan!</p>";
?>

