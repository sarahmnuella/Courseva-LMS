<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Enroll Course - Courseva";
$userId = $_SESSION['user_id'];
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirectWithMessage('/peserta/courses.php', 'Course tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Ambil detail course
$query = "SELECT c.*, u.nama_lengkap as pengajar_nama 
          FROM courses c
          LEFT JOIN users u ON c.pengajar_id = u.id
          WHERE c.id = ? AND c.status = 'published'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    redirectWithMessage('/peserta/courses.php', 'Course tidak ditemukan.', 'error');
}

// Cek apakah sudah enroll
$isEnrolled = isEnrolled($userId, $courseId);
if ($isEnrolled) {
    redirectWithMessage('/peserta/learn.php?course_id=' . $courseId, 'Anda sudah terdaftar di course ini.', 'info');
}

// Cek prasyarat
$prasyarat = null;
$prasyaratCompleted = false;
if ($course['prasyarat_course_id']) {
    $query = "SELECT * FROM courses WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course['prasyarat_course_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $prasyarat = $result->fetch_assoc();
    $stmt->close();
    
    if ($prasyarat) {
        $prasyaratCompleted = isAllModulesCompleted($userId, $prasyarat['id']);
        if (!$prasyaratCompleted) {
            redirectWithMessage('/peserta/courses.php?view=' . $courseId, 'Anda harus menyelesaikan course prasyarat terlebih dahulu.', 'error');
        }
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        // Upload bukti pembayaran
        if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_OK) {
            $uploadResult = uploadFile(
                $_FILES['bukti_pembayaran'],
                '../uploads/bukti_pembayaran',
                ['jpg', 'jpeg', 'png', 'pdf'],
                5242880 // 5MB
            );
            
            if ($uploadResult['success']) {
                // Insert enrollment dengan status pending
                $query = "INSERT INTO enrollments (user_id, course_id, status, created_at) VALUES (?, ?, 'pending', NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ii", $userId, $courseId);
                $stmt->execute();
                $enrollmentId = $conn->insert_id;
                $stmt->close();
                
                // Insert payment
                $query = "INSERT INTO payments (enrollment_id, amount, bukti_pembayaran, status, created_at) 
                          VALUES (?, ?, ?, 'pending', NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ids", $enrollmentId, $course['harga'], $uploadResult['filename']);
                $stmt->execute();
                $stmt->close();
                
                redirectWithMessage('/peserta/payments.php', 'Pendaftaran berhasil! Menunggu verifikasi pembayaran.', 'success');
            } else {
                $errors = $uploadResult['errors'];
            }
        } else {
            $errors[] = "Bukti pembayaran harus diupload.";
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Daftar Course</h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h4><?php echo htmlspecialchars($course['judul']); ?></h4>
                    <p class="text-muted">
                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($course['pengajar_nama']); ?>
                        <span class="ms-3"><i class="bi bi-clock"></i> <?php echo $course['durasi']; ?> jam</span>
                    </p>
                    <p><?php echo nl2br(htmlspecialchars($course['deskripsi'])); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informasi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <h4 class="text-primary mb-3"><?php echo formatRupiah($course['harga']); ?></h4>
                    
                    <h6>Transfer ke:</h6>
                    <p class="mb-1"><strong>Bank:</strong> BCA</p>
                    <p class="mb-1"><strong>No. Rekening:</strong> 1234567890</p>
                    <p class="mb-3"><strong>Atas Nama:</strong> Courseva</p>
                    
                    <hr>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="bukti_pembayaran" class="form-label">Upload Bukti Pembayaran <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" 
                                   accept="image/*,application/pdf" required>
                            <small class="text-muted">Format: JPG, PNG, PDF (Max 5MB)</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Daftar Course</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

