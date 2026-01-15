<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Edit Course - Courseva";
$courseId = $_GET['id'] ?? null;

if (!$courseId) {
    $basePath = getBasePath();
    redirectWithMessage("{$basePath}/admin/courses.php", 'Course tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Ambil detail course
$query = "SELECT * FROM courses WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    $basePath = getBasePath();
    redirectWithMessage("{$basePath}/admin/courses.php", 'Course tidak ditemukan.', 'error');
}

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
        $pengajar_id = $_POST['pengajar_id'] ?? null;
        $prasyarat_course_id = !empty($_POST['prasyarat_course_id']) ? $_POST['prasyarat_course_id'] : null;
        $status = $_POST['status'] ?? 'draft';
        
        if (empty($judul)) {
            $errors[] = "Judul harus diisi.";
        }
        
        // Upload thumbnail baru jika ada
        $thumbnail = $course['thumbnail'];
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] == UPLOAD_ERR_OK) {
            $uploadResult = uploadFile(
                $_FILES['thumbnail'],
                '../uploads/course_thumbnails',
                ['jpg', 'jpeg', 'png'],
                2097152
            );
            
            if ($uploadResult['success']) {
                if ($thumbnail) {
                    deleteFile('../uploads/course_thumbnails/' . $thumbnail);
                }
                $thumbnail = $uploadResult['filename'];
            } else {
                $errors = array_merge($errors, $uploadResult['errors']);
            }
        }
        
        if (empty($errors)) {
            $query = "UPDATE courses SET pengajar_id = ?, judul = ?, deskripsi = ?, thumbnail = ?, durasi = ?, harga = ?, 
                      kategori = ?, prasyarat_course_id = ?, status = ?, updated_at = NOW() 
                      WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issidisisi", $pengajar_id, $judul, $deskripsi, $thumbnail, $durasi, $harga, $kategori, $prasyarat_course_id, $status, $courseId);
            
            if ($stmt->execute()) {
                $stmt->close();
                $basePath = getBasePath();
                redirectWithMessage("{$basePath}/admin/edit_course.php?id=" . $courseId, 'Course berhasil diupdate!', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan course.";
            }
            $stmt->close();
        }
    }
}

// Ambil semua pengajar
$pengajars = $conn->query("SELECT id, nama_lengkap FROM users WHERE role = 'pengajar' ORDER BY nama_lengkap");

// Ambil semua course untuk prasyarat
$allCourses = $conn->query("SELECT id, judul FROM courses WHERE id != $courseId ORDER BY judul");
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Edit Course</h2>
    
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
                    <label for="pengajar_id" class="form-label">Pengajar <span class="text-danger">*</span></label>
                    <select class="form-select" id="pengajar_id" name="pengajar_id" required>
                        <?php while ($p = $pengajars->fetch_assoc()): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo $course['pengajar_id'] == $p['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Course <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" 
                           value="<?php echo htmlspecialchars($course['judul']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo htmlspecialchars($course['deskripsi']); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="durasi" name="durasi" 
                                   value="<?php echo $course['durasi']; ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="harga" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="harga" name="harga" 
                                   value="<?php echo $course['harga']; ?>" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="kategori" class="form-label">Kategori</label>
                    <input type="text" class="form-control" id="kategori" name="kategori" 
                           value="<?php echo htmlspecialchars($course['kategori'] ?? ''); ?>">
                </div>
                
                <div class="mb-3">
                    <label for="prasyarat_course_id" class="form-label">Prasyarat Course</label>
                    <select class="form-select" id="prasyarat_course_id" name="prasyarat_course_id">
                        <option value="">Tidak ada prasyarat</option>
                        <?php while ($c = $allCourses->fetch_assoc()): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $course['prasyarat_course_id'] == $c['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c['judul']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="draft" <?php echo $course['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published" <?php echo $course['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="thumbnail" class="form-label">Thumbnail</label>
                    <?php if ($course['thumbnail']): ?>
                        <div class="mb-2">
                            <img src="/uploads/course_thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                 style="max-width: 200px; max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG (Max 2MB)</small>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Update Course</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

