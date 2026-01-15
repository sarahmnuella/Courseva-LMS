<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Buat Course Baru - Courseva";
$userId = $_SESSION['user_id'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $durasi = $_POST['durasi'] ?? 0;
        $harga = $_POST['harga'] ?? 0;
        $kategori = sanitize($_POST['kategori'] ?? '');
        $prasyarat_course_id = !empty($_POST['prasyarat_course_id']) ? $_POST['prasyarat_course_id'] : null;
        
        if (empty($judul)) {
            $errors[] = "Judul harus diisi.";
        }
        
        if (empty($deskripsi)) {
            $errors[] = "Deskripsi harus diisi.";
        }
        
        if (empty($durasi) || $durasi <= 0) {
            $errors[] = "Durasi harus lebih dari 0.";
        }
        
        if ($harga < 0) {
            $errors[] = "Harga tidak valid.";
        }
        
        // Upload thumbnail
        $thumbnail = null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
            $uploadResult = uploadFile(
                $_FILES['thumbnail'],
                '../uploads/course_thumbnails',
                ['jpg', 'jpeg', 'png'],
                2097152 // 2MB
            );
            
            if ($uploadResult['success']) {
                $thumbnail = $uploadResult['filename'];
            } else {
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }
        
        if (empty($errors)) {
            $conn = getDBConnection();
            $status = 'draft';
            
            $query = "INSERT INTO courses (pengajar_id, judul, deskripsi, thumbnail, durasi, harga, kategori, prasyarat_course_id, status, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isssidiss", $userId, $judul, $deskripsi, $thumbnail, $durasi, $harga, $kategori, $prasyarat_course_id, $status);
            
            if ($stmt->execute()) {
                $courseId = $conn->insert_id;
                $stmt->close();
                redirectWithMessage('/pengajar/edit_course.php?id=' . $courseId, 'Course berhasil dibuat!', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan course.";
            }
            $stmt->close();
        }
    }
}

// Ambil semua course untuk prasyarat
$conn = getDBConnection();
$query = "SELECT id, judul FROM courses WHERE id != ? OR ? IS NULL ORDER BY judul";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $userId);
$stmt->execute();
$allCourses = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Buat Course Baru</h2>
    
    <div class="card">
        <div class="card-body">
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
                    <label for="judul" class="form-label">Judul Course <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" 
                           value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="durasi" name="durasi" 
                                   value="<?php echo htmlspecialchars($_POST['durasi'] ?? ''); ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga" name="harga" 
                                   value="<?php echo htmlspecialchars($_POST['harga'] ?? ''); ?>" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <input type="text" class="form-control" id="kategori" name="kategori" 
                           value="<?php echo htmlspecialchars($_POST['kategori'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="prasyarat_course_id" class="form-label">Prasyarat Course (Opsional)</label>
                    <select class="form-select" id="prasyarat_course_id" name="prasyarat_course_id">
                        <option value="">Tidak ada prasyarat</option>
                        <?php while ($course = $allCourses->fetch_assoc()): ?>
                            <option value="<?php echo $course['id']; ?>" 
                                    <?php echo (isset($_POST['prasyarat_course_id']) && $_POST['prasyarat_course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($course['judul']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Thumbnail</label>
                    <input type="file" class="form-control" id="thumbnail" name="thumbnail" 
                           accept="image/*">
                    <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Buat Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

