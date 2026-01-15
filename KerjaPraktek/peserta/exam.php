<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Exam - Courseva";
$userId = $_SESSION['user_id'];
$examId = $_GET['exam_id'] ?? null;

if (!$examId) {
    redirectWithMessage('/peserta/dashboard.php', 'Exam tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Ambil detail exam
$query = "SELECT e.*, c.id as course_id, c.judul as course_judul 
          FROM exams e
          INNER JOIN courses c ON e.course_id = c.id
          WHERE e.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $examId);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();
$stmt->close();

if (!$exam) {
    redirectWithMessage('/peserta/dashboard.php', 'Exam tidak ditemukan.', 'error');
}

// Cek apakah sudah enroll
if (!isEnrolled($userId, $exam['course_id'])) {
    redirectWithMessage('/peserta/courses.php', 'Anda belum terdaftar di course ini.', 'error');
}

// Cek apakah semua modul sudah selesai
if (!isAllModulesCompleted($userId, $exam['course_id'])) {
    redirectWithMessage('/peserta/learn.php?course_id=' . $exam['course_id'], 'Selesaikan semua modul terlebih dahulu.', 'error');
}

// Cek attempts
$query = "SELECT COUNT(*) as total FROM exam_attempts WHERE user_id = ? AND exam_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $examId);
$stmt->execute();
$result = $stmt->get_result();
$totalAttempts = $result->fetch_assoc()['total'];
$stmt->close();

$remainingAttempts = $exam['max_attempts'] - $totalAttempts;

if ($remainingAttempts <= 0) {
    redirectWithMessage('/peserta/dashboard.php', 'Anda sudah mencapai batas maksimal attempts.', 'error');
}

// Cek apakah ada attempt yang sedang berjalan
$query = "SELECT * FROM exam_attempts 
          WHERE user_id = ? AND exam_id = ? AND status = 'in_progress' 
          ORDER BY started_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $examId);
$stmt->execute();
$result = $stmt->get_result();
$currentAttempt = $result->fetch_assoc();
$stmt->close();

// Start new attempt
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['start_exam'])) {
    $query = "INSERT INTO exam_attempts (user_id, exam_id, status, started_at, expires_at) 
              VALUES (?, ?, 'in_progress', NOW(), DATE_ADD(NOW(), INTERVAL ? MINUTE))";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $examId, $exam['durasi']);
    $stmt->execute();
    $attemptId = $conn->insert_id;
    $stmt->close();
    
    $basePath = getBasePath();
    header("Location: {$basePath}/peserta/exam.php?exam_id=$examId&attempt_id=$attemptId");
    exit();
}

// Submit exam
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_exam']) && isset($_POST['attempt_id'])) {
    $attemptId = $_POST['attempt_id'];
    
    // Update attempt status
    $query = "UPDATE exam_attempts SET status = 'completed', submitted_at = NOW() WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $attemptId, $userId);
    $stmt->execute();
    $stmt->close();
    
    // Simpan jawaban dan hitung score
    $totalScore = 0;
    $totalPoints = 0;
    
    // Ambil semua pertanyaan
    $query = "SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY urutan ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $examId);
    $stmt->execute();
    $questions = $stmt->get_result();
    
    while ($question = $questions->fetch_assoc()) {
        $totalPoints += $question['poin'];
        $pointsEarned = 0;
        
        // Ambil jawaban user
        $answerKey = 'answer_' . $question['id'];
        $userAnswer = $_POST[$answerKey] ?? '';
        
        if ($question['tipe'] == 'multiple_choice' || $question['tipe'] == 'true_false') {
            // Auto grade
            if ($userAnswer == $question['jawaban_benar']) {
                $pointsEarned = $question['poin'];
            }
        } elseif ($question['tipe'] == 'essay') {
            // Manual grade, default 0
            $pointsEarned = 0;
        }
        
        $totalScore += $pointsEarned;
        
        // Simpan jawaban
        $query = "INSERT INTO exam_answers (attempt_id, question_id, jawaban, poin_diperoleh, created_at) 
                  VALUES (?, ?, ?, ?, NOW())
                  ON DUPLICATE KEY UPDATE jawaban = ?, poin_diperoleh = ?";
        $stmt2 = $conn->prepare($query);
        $stmt2->bind_param("iisdisd", $attemptId, $question['id'], $userAnswer, $pointsEarned, $userAnswer, $pointsEarned);
        $stmt2->execute();
        $stmt2->close();
    }
    
    $stmt->close();
    
    // Update score di attempt
    $query = "UPDATE exam_attempts SET score = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $totalScore, $attemptId);
    $stmt->execute();
    $stmt->close();
    
    redirectWithMessage('/peserta/exam_result.php?attempt_id=' . $attemptId, 'Exam berhasil disubmit!', 'success');
}

// Jika ada attempt_id, tampilkan exam
$attemptId = $_GET['attempt_id'] ?? null;
$attempt = null;
if ($attemptId) {
    $query = "SELECT * FROM exam_attempts WHERE id = ? AND user_id = ? AND exam_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $attemptId, $userId, $examId);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();
    
    if (!$attempt || $attempt['status'] != 'in_progress') {
        redirectWithMessage('/peserta/exam.php?exam_id=' . $examId, 'Attempt tidak valid atau sudah selesai.', 'error');
    }
    
    // Cek apakah waktu sudah habis
    $now = new DateTime();
    $expiresAt = new DateTime($attempt['expires_at']);
    if ($now > $expiresAt) {
        // Auto submit
        $query = "UPDATE exam_attempts SET status = 'completed', submitted_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $attemptId);
        $stmt->execute();
        $stmt->close();
        
        redirectWithMessage('/peserta/exam_result.php?attempt_id=' . $attemptId, 'Waktu exam sudah habis.', 'info');
    }
    
    // Ambil pertanyaan
    $query = "SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY urutan ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $examId);
    $stmt->execute();
    $questions = $stmt->get_result();
    
    // Ambil jawaban yang sudah ada
    $query = "SELECT question_id, jawaban FROM exam_answers WHERE attempt_id = ?";
    $stmt2 = $conn->prepare($query);
    $stmt2->bind_param("i", $attemptId);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $existingAnswers = [];
    while ($ans = $result->fetch_assoc()) {
        $existingAnswers[$ans['question_id']] = $ans['jawaban'];
    }
    $stmt2->close();
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <?php if (!$attemptId): ?>
        <!-- Start Exam -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><?php echo htmlspecialchars($exam['judul']); ?></h4>
            </div>
            <div class="card-body">
                <h5>Course: <?php echo htmlspecialchars($exam['course_judul']); ?></h5>
                <p><?php echo nl2br(htmlspecialchars($exam['deskripsi'])); ?></p>
                
                <div class="alert alert-info">
                    <h6>Informasi Exam:</h6>
                    <ul class="mb-0">
                        <li>Durasi: <?php echo $exam['durasi']; ?> menit</li>
                        <li>Passing Score: <?php echo $exam['passing_score']; ?>%</li>
                        <li>Max Attempts: <?php echo $exam['max_attempts']; ?></li>
                        <li>Remaining Attempts: <?php echo $remainingAttempts; ?></li>
                    </ul>
                </div>
                
                <form method="POST" action="">
                    <button type="submit" name="start_exam" class="btn btn-primary btn-lg">
                        Mulai Exam
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Take Exam -->
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><?php echo htmlspecialchars($exam['judul']); ?></h4>
                <div id="timer" class="h5 mb-0"></div>
            </div>
            <div class="card-body">
                <form method="POST" action="" id="examForm">
                    <input type="hidden" name="attempt_id" value="<?php echo $attemptId; ?>">
                    <input type="hidden" name="submit_exam" value="1">
                    
                    <?php
                    $questionNum = 1;
                    while ($question = $questions->fetch_assoc()):
                        $answerKey = 'answer_' . $question['id'];
                        $existingAnswer = $existingAnswers[$question['id']] ?? '';
                    ?>
                        <div class="mb-4 p-3 border rounded">
                            <h5>Pertanyaan <?php echo $questionNum; ?> (<?php echo $question['poin']; ?> poin)</h5>
                            <p><?php echo nl2br(htmlspecialchars($question['pertanyaan'])); ?></p>
                            
                            <?php if ($question['tipe'] == 'multiple_choice'): ?>
                                <?php
                                $options = json_decode($question['opsi_jawaban'], true);
                                foreach ($options as $key => $option):
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" 
                                               name="<?php echo $answerKey; ?>" 
                                               id="q<?php echo $question['id']; ?>_<?php echo $key; ?>" 
                                               value="<?php echo $key; ?>"
                                               <?php echo $existingAnswer == $key ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="q<?php echo $question['id']; ?>_<?php echo $key; ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                                
                            <?php elseif ($question['tipe'] == 'true_false'): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="<?php echo $answerKey; ?>" 
                                           id="q<?php echo $question['id']; ?>_true" 
                                           value="true"
                                           <?php echo $existingAnswer == 'true' ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_true">True</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="<?php echo $answerKey; ?>" 
                                           id="q<?php echo $question['id']; ?>_false" 
                                           value="false"
                                           <?php echo $existingAnswer == 'false' ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="q<?php echo $question['id']; ?>_false">False</label>
                                </div>
                                
                            <?php elseif ($question['tipe'] == 'essay'): ?>
                                <textarea class="form-control" name="<?php echo $answerKey; ?>" 
                                          rows="5" required><?php echo htmlspecialchars($existingAnswer); ?></textarea>
                            <?php endif; ?>
                        </div>
                    <?php
                        $questionNum++;
                    endwhile;
                    ?>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Yakin ingin submit exam?');">
                            Submit Exam
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        // Timer countdown
        const expiresAt = new Date('<?php echo $attempt['expires_at']; ?>').getTime();
        
        function updateTimer() {
            const now = new Date().getTime();
            const distance = expiresAt - now;
            
            if (distance < 0) {
                document.getElementById('timer').innerHTML = 'Waktu Habis!';
                document.getElementById('examForm').submit();
                return;
            }
            
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('timer').innerHTML = 
                String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
        }
        
        setInterval(updateTimer, 1000);
        updateTimer();
        </script>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

