<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Belajar Course - Courseva";
$userId = $_SESSION['user_id'];
$courseId = $_GET['course_id'] ?? null;

if (!$courseId) {
    redirectWithMessage('/peserta/courses.php', 'Course tidak ditemukan.', 'error');
}

// Cek apakah sudah enroll
if (!isEnrolled($userId, $courseId)) {
    redirectWithMessage('/peserta/courses.php?view=' . $courseId, 'Anda belum terdaftar di course ini.', 'error');
}

$conn = getDBConnection();

// Ambil detail course
$query = "SELECT c.*, u.nama_lengkap as pengajar_nama 
          FROM courses c
          LEFT JOIN users u ON c.pengajar_id = u.id
          WHERE c.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    redirectWithMessage('/peserta/courses.php', 'Course tidak ditemukan.', 'error');
}

// Ambil semua modul
$query = "SELECT * FROM modules WHERE course_id = ? ORDER BY urutan ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $courseId);
$stmt->execute();
$modules = $stmt->get_result();

// Ambil progress modul
$query = "SELECT module_id, status FROM module_progress WHERE user_id = ? AND course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $courseId);
$stmt->execute();
$progressResult = $stmt->get_result();
$progress = [];
while ($p = $progressResult->fetch_assoc()) {
    $progress[$p['module_id']] = $p['status'];
}
$stmt->close();

// Hitung progress
$totalModules = $modules->num_rows;
$completedModules = 0;
foreach ($progress as $status) {
    if ($status == 'completed') {
        $completedModules++;
    }
}
$courseProgress = $totalModules > 0 ? round(($completedModules / $totalModules) * 100) : 0;

// Ambil modul yang sedang dilihat
$moduleId = $_GET['module_id'] ?? null;
$currentModule = null;

if ($moduleId) {
    $query = "SELECT * FROM modules WHERE id = ? AND course_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $moduleId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentModule = $result->fetch_assoc();
    $stmt->close();
} elseif ($modules->num_rows > 0) {
    // Ambil modul pertama
    $modules->data_seek(0);
    $currentModule = $modules->fetch_assoc();
    $moduleId = $currentModule['id'];
}

// Mark as complete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_complete']) && $moduleId) {
    // Cek apakah sudah ada progress
    $query = "SELECT id FROM module_progress WHERE user_id = ? AND course_id = ? AND module_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $courseId, $moduleId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update
        $query = "UPDATE module_progress SET status = 'completed', updated_at = NOW() 
                  WHERE user_id = ? AND course_id = ? AND module_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $userId, $courseId, $moduleId);
        $stmt->execute();
    } else {
        // Insert
        $query = "INSERT INTO module_progress (user_id, course_id, module_id, status, created_at) 
                  VALUES (?, ?, ?, 'completed', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $userId, $courseId, $moduleId);
        $stmt->execute();
    }
    $stmt->close();
    
    redirectWithMessage('/peserta/learn.php?course_id=' . $courseId . '&module_id=' . $moduleId, 'Modul ditandai sebagai selesai.', 'success');
}

// Cek apakah semua modul sudah selesai untuk menampilkan tombol exam
$allModulesCompleted = isAllModulesCompleted($userId, $courseId);

// Ambil exam jika ada
$exam = null;
if ($allModulesCompleted) {
    $query = "SELECT * FROM exams WHERE course_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exam = $result->fetch_assoc();
    $stmt->close();
}
?>
<?php include '../includes/header.php'; ?>

<div class="container-fluid my-4">
    <div class="row">
        <!-- Sidebar Modul -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($course['judul']); ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="progress m-3" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $courseProgress; ?>%"></div>
                    </div>
                    <p class="px-3 mb-2"><small>Progress: <?php echo $courseProgress; ?>%</small></p>
                    
                    <ul class="list-group list-group-flush">
                        <?php
                        $modules->data_seek(0);
                        $moduleIndex = 1;
                        while ($module = $modules->fetch_assoc()):
                            $isCompleted = isset($progress[$module['id']]) && $progress[$module['id']] == 'completed';
                            $isActive = $module['id'] == $moduleId;
                        ?>
                            <li class="list-group-item <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="d-flex align-items-center">
                                    <?php if ($isCompleted): ?>
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    <?php else: ?>
                                        <i class="bi bi-circle me-2"></i>
                                    <?php endif; ?>
                                    <a href="<?php echo url('peserta/learn.php?course_id=' . $courseId . '&module_id=' . $module['id']); ?>" 
                                       class="text-decoration-none <?php echo $isActive ? 'text-white' : ''; ?>">
                                        Modul <?php echo $moduleIndex; ?>: <?php echo htmlspecialchars($module['judul']); ?>
                                    </a>
                                </div>
                            </li>
                        <?php
                            $moduleIndex++;
                        endwhile;
                        ?>
                    </ul>
                    
                    <?php if ($allModulesCompleted && $exam): ?>
                        <div class="p-3">
                            <a href="<?php echo url('peserta/exam.php?exam_id=' . $exam['id']); ?>" class="btn btn-success w-100">
                                <i class="bi bi-pencil-square"></i> Take Exam
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Konten Modul -->
        <div class="col-md-9">
            <?php if ($currentModule): ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><?php echo htmlspecialchars($currentModule['judul']); ?></h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <i class="bi bi-clock"></i> Durasi: <?php echo $currentModule['durasi']; ?> menit
                        </p>
                        
                        <div class="mb-4">
                            <?php if ($currentModule['tipe_konten'] == 'video' && $currentModule['file_path']): ?>
                                <video controls class="w-100" style="max-height: 500px;">
                                    <source src="<?php echo uploadUrl('uploads/module_files/' . htmlspecialchars($currentModule['file_path'])); ?>" type="video/mp4">
                                    Browser Anda tidak mendukung video player.
                                </video>
                            <?php elseif ($currentModule['tipe_konten'] == 'pdf' && $currentModule['file_path']): ?>
                                <iframe src="<?php echo uploadUrl('uploads/module_files/' . htmlspecialchars($currentModule['file_path'])); ?>" 
                                        class="w-100" style="height: 600px;"></iframe>
                            <?php else: ?>
                                <div class="content-text">
                                    <?php echo nl2br(htmlspecialchars($currentModule['konten'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <div>
                                <?php
                                // Cari modul sebelumnya
                                $modules->data_seek(0);
                                $prevModule = null;
                                $foundCurrent = false;
                                while ($m = $modules->fetch_assoc()) {
                                    if ($foundCurrent && $m['id'] != $moduleId) {
                                        $prevModule = $m;
                                        break;
                                    }
                                    if ($m['id'] == $moduleId) {
                                        $foundCurrent = true;
                                    }
                                }
                                
                                // Cari modul berikutnya
                                $modules->data_seek(0);
                                $nextModule = null;
                                $foundCurrent = false;
                                while ($m = $modules->fetch_assoc()) {
                                    if ($foundCurrent) {
                                        $nextModule = $m;
                                        break;
                                    }
                                    if ($m['id'] == $moduleId) {
                                        $foundCurrent = true;
                                    }
                                }
                                ?>
                                
                                <?php if ($prevModule): ?>
                                    <a href="<?php echo url('peserta/learn.php?course_id=' . $courseId . '&module_id=' . $prevModule['id']); ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-arrow-left"></i> Sebelumnya
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <?php
                                $isCurrentCompleted = isset($progress[$moduleId]) && $progress[$moduleId] == 'completed';
                                ?>
                                
                                <?php if (!$isCurrentCompleted): ?>
                                    <form method="POST" action="" class="d-inline">
                                        <button type="submit" name="mark_complete" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Mark as Complete
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completed</span>
                                <?php endif; ?>
                                
                                <?php if ($nextModule): ?>
                                    <a href="<?php echo url('peserta/learn.php?course_id=' . $courseId . '&module_id=' . $nextModule['id']); ?>" 
                                       class="btn btn-primary">
                                        Selanjutnya <i class="bi bi-arrow-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Tidak ada modul tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

