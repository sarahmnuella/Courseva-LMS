<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Tambah/Edit Modul - Courseva";
$userId = $_SESSION['user_id'];
$courseId = $_GET['course_id'] ?? null;
$moduleId = $_GET['module_id'] ?? null;

if (!$courseId) {
    redirectWithMessage('/pengajar/courses.php', 'Course tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Cek apakah course milik pengajar
$query = "SELECT id FROM courses WHERE id = ? AND pengajar_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $courseId, $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    redirectWithMessage('/pengajar/courses.php', 'Course tidak ditemukan atau bukan milik Anda.', 'error');
}
$stmt->close();

$module = null;
if ($moduleId) {
    $query = "SELECT * FROM modules WHERE id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $moduleId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $module = $result->fetch_assoc();
    $stmt->close();
}

// Delete module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_module'])) {
    $moduleId = $_POST['module_id'];
    $query = "DELETE FROM modules WHERE id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $moduleId, $courseId);
    $stmt->execute();
    $stmt->close();
    
    redirectWithMessage('/pengajar/edit_course.php?id=' . $courseId, 'Modul berhasil dihapus.', 'success');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_module'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $tipe_konten = $_POST['tipe_konten'] ?? 'text';
        $durasi = $_POST['durasi'] ?? 0;
        $urutan = $_POST['urutan'] ?? 1;
        
        if (empty($judul)) {
            $errors[] = "Judul harus diisi.";
        }
        
        $file_path = $module['file_path'] ?? null;
        $konten = '';
        
        if ($tipe_konten == 'video' || $tipe_konten == 'pdf') {
            if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
                $allowedTypes = $tipe_konten == 'video' ? ['mp4', 'avi'] : ['pdf'];
                $uploadResult = uploadFile(
                    $_FILES['file'],
                    '../uploads/module_files',
                    $allowedTypes,
                    104857600 // 100MB
                );
                
                if ($uploadResult['success']) {
                    if ($file_path) {
                        deleteFile('../uploads/module_files/' . $file_path);
                    }
                    $file_path = $uploadResult['filename'];
                } else {
                    $errors = array_merge($errors, $uploadResult['errors']);
                }
            } elseif (!$module) {
                $errors[] = "File harus diupload untuk tipe konten ini.";
            }
        } else {
            $konten = sanitize($_POST['konten'] ?? '');
            if (empty($konten)) {
                $errors[] = "Konten harus diisi.";
            }
        }
        
        if (empty($errors)) {
            if ($module) {
                // Update
                $query = "UPDATE modules SET judul = ?, deskripsi = ?, tipe_konten = ?, konten = ?, 
                          file_path = ?, durasi = ?, urutan = ?, updated_at = NOW() 
                          WHERE id = ? AND course_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssiiii", $judul, $deskripsi, $tipe_konten, $konten, $file_path, $durasi, $urutan, $moduleId, $courseId);
            } else {
                // Insert
                $query = "INSERT INTO modules (course_id, judul, deskripsi, tipe_konten, konten, file_path, durasi, urutan, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("isssssii", $courseId, $judul, $deskripsi, $tipe_konten, $konten, $file_path, $durasi, $urutan);
            }
            
            if ($stmt->execute()) {
                $stmt->close();
                redirectWithMessage('/pengajar/edit_course.php?id=' . $courseId, 'Modul berhasil disimpan!', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan modul.";
            }
            $stmt->close();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4"><?php echo $module ? 'Edit' : 'Tambah'; ?> Modul</h2>
    
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
                    <label for="judul" class="form-label">Judul Modul <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" 
                           value="<?php echo htmlspecialchars($module['judul'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($module['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="tipe_konten" class="form-label">Tipe Konten <span class="text-danger">*</span></label>
                            <select class="form-select" id="tipe_konten" name="tipe_konten" required>
                                <option value="text" <?php echo ($module['tipe_konten'] ?? '') == 'text' ? 'selected' : ''; ?>>Text</option>
                                <option value="video" <?php echo ($module['tipe_konten'] ?? '') == 'video' ? 'selected' : ''; ?>>Video</option>
                                <option value="pdf" <?php echo ($module['tipe_konten'] ?? '') == 'pdf' ? 'selected' : ''; ?>>PDF</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="durasi" name="durasi" 
                                   value="<?php echo $module['durasi'] ?? ''; ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="urutan" class="form-label">Urutan <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="urutan" name="urutan" 
                                   value="<?php echo $module['urutan'] ?? '1'; ?>" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3" id="fileUploadSection" style="display: none;">
                    <label for="file" class="form-label">Upload File <span class="text-danger">*</span></label>
                    <?php if ($module && $module['file_path']): ?>
                        <div class="mb-2">
                            <small class="text-muted">File saat ini: <?php echo htmlspecialchars($module['file_path']); ?></small>
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="file" name="file">
                    <small class="text-muted" id="fileHelp"></small>
                </div>
                
                <div class="mb-3" id="textContentSection" style="display: none;">
                    <label for="konten" class="form-label">Konten <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="konten" name="konten" rows="10"><?php echo htmlspecialchars($module['konten'] ?? ''); ?></textarea>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Simpan Modul</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('tipe_konten').addEventListener('change', function() {
    const tipe = this.value;
    const fileSection = document.getElementById('fileUploadSection');
    const textSection = document.getElementById('textContentSection');
    const fileInput = document.getElementById('file');
    const fileHelp = document.getElementById('fileHelp');
    
    if (tipe === 'video') {
        fileSection.style.display = 'block';
        textSection.style.display = 'none';
        fileInput.accept = 'video/*';
        fileHelp.textContent = 'Format: MP4, AVI (Max 100MB)';
        fileInput.required = true;
    } else if (tipe === 'pdf') {
        fileSection.style.display = 'block';
        textSection.style.display = 'none';
        fileInput.accept = 'application/pdf';
        fileHelp.textContent = 'Format: PDF (Max 100MB)';
        fileInput.required = true;
    } else {
        fileSection.style.display = 'none';
        textSection.style.display = 'block';
        fileInput.required = false;
    }
});

// Trigger on load
document.getElementById('tipe_konten').dispatchEvent(new Event('change'));
</script>

<?php include '../includes/footer.php'; ?>

