<?php
/**
 * Script untuk membuat user pengajar
 * Akses: http://localhost/KerjaPraktek/create_pengajar.php
 * HAPUS FILE INI SETELAH PENGAJAR BERHASIL DIBUAT!
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// Generate password hash untuk "pengajar123"
$password = 'pengajar123';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$conn = getDBConnection();

// Data pengajar default
$nama_lengkap = 'Pengajar Demo';
$email = 'pengajar@courseva.com';
$username = 'pengajar';
$role = 'pengajar';
$status = 'active';
$instansi = 'Courseva Academy';
$nomor_hp = '081234567890';

// Cek apakah pengajar sudah ada
$query = "SELECT id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update password pengajar yang sudah ada
    $query = "UPDATE users SET password = ?, status = 'active', role = 'pengajar' WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $hashedPassword, $username, $email);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Password pengajar berhasil diupdate!</h2>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Email:</strong> $email</p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error: " . $conn->error . "</h2>";
    }
    $stmt->close();
} else {
    // Insert pengajar baru
    $query = "INSERT INTO users (nama_lengkap, instansi, email, username, password, nomor_hp, role, status, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $nama_lengkap, $instansi, $email, $username, $hashedPassword, $nomor_hp, $role, $status);
    
    if ($stmt->execute()) {
        echo "<h2 style='color: green;'>✓ Pengajar berhasil dibuat!</h2>";
        echo "<p><strong>Nama:</strong> $nama_lengkap</p>";
        echo "<p><strong>Username:</strong> $username</p>";
        echo "<p><strong>Password:</strong> $password</p>";
        echo "<p><strong>Email:</strong> $email</p>";
        echo "<p><a href='login.php'>Klik di sini untuk login</a></p>";
    } else {
        echo "<h2 style='color: red;'>✗ Error: " . $conn->error . "</h2>";
    }
    $stmt->close();
}

// Test password verification
echo "<hr>";
echo "<h3>Test Password Verification:</h3>";
if (password_verify($password, $hashedPassword)) {
    echo "<p style='color: green;'>✓ Password verification: BERHASIL</p>";
} else {
    echo "<p style='color: red;'>✗ Password verification: GAGAL</p>";
}

echo "<hr>";
echo "<h3>Informasi Login:</h3>";
echo "<div style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";
echo "<p><strong>Username:</strong> $username</p>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>Role:</strong> Pengajar</p>";
echo "</div>";

echo "<hr>";
echo "<p style='color: orange;'><strong>PENTING:</strong> Hapus file create_pengajar.php setelah pengajar berhasil dibuat untuk keamanan!</p>";
?>

