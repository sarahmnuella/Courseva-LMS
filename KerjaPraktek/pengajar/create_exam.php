<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Buat Exam - Courseva";
$userId = $_SESSION['user_id'];
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirectWithMessage('/pengajar/courses.php', 'Course tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Cek apakah course milik pengajar
$query = "SELECT id, judul FROM courses WHERE id = ? AND pengajar_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $courseId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    redirectWithMessage('/pengajar/courses.php', 'Course tidak ditemukan atau bukan milik Anda.', 'error');
}

// Cek apakah sudah ada exam
$query = "SELECT id FROM exams WHERE course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    redirectWithMessage('/pengajar/edit_course.php?id=' . $courseId, 'Course ini sudah memiliki exam.', 'info');
}
$stmt->close();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $judul = sanitize($_POST['judul'] ?? '');
        $deskripsi = sanitize($_POST['deskripsi'] ?? '');
        $durasi = $_POST['durasi'] ?? 0;
        $passing_score = $_POST['passing_score'] ?? 0;
        $max_attempts = $_POST['max_attempts'] ?? 1;
        
        if (empty($judul)) {
            $errors[] = "Judul harus diisi.";
        }
        
        if (empty($durasi) || $durasi <= 0) {
            $errors[] = "Durasi harus lebih dari 0.";
        }
        
        if ($passing_score < 0 || $passing_score > 100) {
            $errors[] = "Passing score harus antara 0-100.";
        }
        
        if ($max_attempts < 1) {
            $errors[] = "Max attempts minimal 1.";
        }
        
        if (empty($errors)) {
            $query = "INSERT INTO exams (course_id, judul, deskripsi, durasi, passing_score, max_attempts, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("isiiii", $courseId, $judul, $deskripsi, $durasi, $passing_score, $max_attempts);
            
            if ($stmt->execute()) {
                $examId = $conn->insert_id;
                $stmt->close();
                redirectWithMessage('/pengajar/manage_questions.php?exam_id=' . $examId, 'Exam berhasil dibuat! Tambahkan pertanyaan.', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan exam.";
            }
            $stmt->close();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Buat Exam untuk: <?php echo htmlspecialchars($course['judul']); ?></h2>
    
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
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="judul" class="form-label">Judul Exam <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="judul" name="judul" 
                           value="<?php echo htmlspecialchars($_POST['judul'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?php echo htmlspecialchars($_POST['deskripsi'] ?? ''); ?></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (menit) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="durasi" name="durasi" 
                                   value="<?php echo htmlspecialchars($_POST['durasi'] ?? ''); ?>" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="passing_score" class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="passing_score" name="passing_score" 
                                   value="<?php echo htmlspecialchars($_POST['passing_score'] ?? '70'); ?>" min="0" max="100" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="max_attempts" class="form-label">Max Attempts <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_attempts" name="max_attempts" 
                                   value="<?php echo htmlspecialchars($_POST['max_attempts'] ?? '3'); ?>" min="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Buat Exam</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

