<?php
// Helper Functions untuk Courseva

// Sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Format tanggal Indonesia
function formatTanggal($date, $withTime = false) {
    if (empty($date)) return '-';
    
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $tanggal = date('d', $timestamp);
    $bulan_nama = $bulan[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);
    
    $result = $tanggal . ' ' . $bulan_nama . ' ' . $tahun;
    
    if ($withTime) {
        $result .= ' ' . date('H:i', $timestamp);
    }
    
    return $result;
}

// Format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Upload file dengan validasi
function uploadFile($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'], $maxSize = 5242880) {
    // 5MB default
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Error upload file.";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Cek ukuran file
    if ($file['size'] > $maxSize) {
        $errors[] = "Ukuran file melebihi batas maksimal (" . ($maxSize / 1024 / 1024) . "MB).";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Cek tipe file
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExt, $allowedTypes)) {
        $errors[] = "Tipe file tidak diizinkan. Hanya: " . implode(', ', $allowedTypes);
        return ['success' => false, 'errors' => $errors];
    }
    
    // Generate nama file unik
    $fileName = uniqid() . '_' . time() . '.' . $fileExt;
    $targetPath = $targetDir . '/' . $fileName;
    
    // Buat direktori jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    // Upload file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $targetPath];
    } else {
        $errors[] = "Gagal mengupload file.";
        return ['success' => false, 'errors' => $errors];
    }
}

// Hapus file
function deleteFile($filePath) {
    if (file_exists($filePath)) {
        return unlink($filePath);
    }
    return false;
}

// Generate certificate number
function generateCertificateNumber($courseId, $userId) {
    $year = date('Y');
    $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return "COURSEVA-{$year}-{$random}";
}

// Hitung progress course
function calculateCourseProgress($userId, $courseId) {
    $conn = getDBConnection();
    
    // Total modul
    $query = "SELECT COUNT(*) as total FROM modules WHERE course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    
    if ($total == 0) return 0;
    
    // Modul yang sudah selesai
    $query = "SELECT COUNT(*) as completed FROM module_progress 
              WHERE user_id = ? AND course_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed = $result->fetch_assoc()['completed'];
    $stmt->close();
    
    return round(($completed / $total) * 100);
}

// Cek apakah user sudah enroll course
function isEnrolled($userId, $courseId) {
    $conn = getDBConnection();
    $query = "SELECT id FROM enrollments 
              WHERE user_id = ? AND course_id = ? AND status = 'verified'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $enrolled = $result->num_rows > 0;
    $stmt->close();
    
    return $enrolled;
}

// Cek apakah semua modul sudah selesai
function isAllModulesCompleted($userId, $courseId) {
    $conn = getDBConnection();
    
    // Total modul
    $query = "SELECT COUNT(*) as total FROM modules WHERE course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $total = $result->fetch_assoc()['total'];
    $stmt->close();
    
    if ($total == 0) return false;
    
    // Modul yang sudah selesai
    $query = "SELECT COUNT(*) as completed FROM module_progress 
              WHERE user_id = ? AND course_id = ? AND status = 'completed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $completed = $result->fetch_assoc()['completed'];
    $stmt->close();
    
    return $completed == $total;
}

// Get base path
function getBasePath() {
    if (defined('BASE_PATH')) {
        return BASE_PATH;
    }
    // Deteksi otomatis
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = trim($scriptDir, '/');
    
    if (empty($scriptDir)) {
        return '/KerjaPraktek';
    } else {
        $parts = explode('/', $scriptDir);
        return '/' . $parts[0];
    }
}

// Helper function untuk generate URL dengan BASE_PATH
function url($path) {
    $basePath = getBasePath();
    // Jika path sudah dimulai dengan /, hapus dulu
    $path = ltrim($path, '/');
    return $basePath . '/' . $path;
}

// Helper function untuk generate upload URL
function uploadUrl($path) {
    $basePath = getBasePath();
    // Jika path sudah dimulai dengan /, hapus dulu
    $path = ltrim($path, '/');
    return $basePath . '/' . $path;
}

// Redirect dengan pesan
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    
    // Jika URL tidak dimulai dengan http, tambahkan BASE_PATH
    if (strpos($url, 'http') !== 0 && $url[0] == '/') {
        $url = getBasePath() . $url;
    }
    
    header("Location: $url");
    exit();
}

// Tampilkan flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        
        $alertClass = $type == 'success' ? 'alert-success' : ($type == 'error' ? 'alert-danger' : 'alert-info');
        
        echo "<div class='alert {$alertClass} alert-dismissible fade show' role='alert'>";
        echo htmlspecialchars($message);
        echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        echo "</div>";
    }
}
?>

