<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Hasil Exam - Courseva";
$userId = $_SESSION['user_id'];
$attemptId = $_GET['attempt_id'] ?? null;

if (!$attemptId) {
    redirectWithMessage('/peserta/dashboard.php', 'Attempt tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Ambil detail attempt
$query = "SELECT ea.*, e.*, c.id as course_id, c.judul as course_judul 
          FROM exam_attempts ea
          INNER JOIN exams e ON ea.exam_id = e.id
          INNER JOIN courses c ON e.course_id = c.id
          WHERE ea.id = ? AND ea.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $attemptId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$attempt = $result->fetch_assoc();
$stmt->close();

if (!$attempt) {
    redirectWithMessage('/peserta/dashboard.php', 'Attempt tidak ditemukan.', 'error');
}

// Hitung total poin
$query = "SELECT SUM(poin) as total FROM exam_questions WHERE exam_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $attempt['exam_id']);
$stmt->execute();
$result = $stmt->get_result();
$totalPoints = $result->fetch_assoc()['total'];
$stmt->close();

$score = $attempt['score'] ?? 0;
$percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100) : 0;
$passingScore = $attempt['passing_score'];
$isPassed = $percentage >= $passingScore;

// Ambil semua pertanyaan dan jawaban
$query = "SELECT eq.*, ea.jawaban as user_answer, ea.poin_diperoleh 
          FROM exam_questions eq
          LEFT JOIN exam_answers ea ON eq.id = ea.question_id AND ea.attempt_id = ?
          WHERE eq.exam_id = ?
          ORDER BY eq.urutan ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $attemptId, $attempt['exam_id']);
$stmt->execute();
$questions = $stmt->get_result();

// Cek apakah sudah ada sertifikat
$query = "SELECT * FROM certificates WHERE user_id = ? AND course_id = ?";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("ii", $userId, $attempt['course_id']);
$stmt2->execute();
$result = $stmt2->get_result();
$certificate = $result->fetch_assoc();
$stmt2->close();

// Generate sertifikat jika pass dan belum ada
if ($isPassed && !$certificate) {
    $certNumber = generateCertificateNumber($attempt['course_id'], $userId);
    
    // Generate PDF (simplified - dalam implementasi nyata perlu library seperti TCPDF atau FPDF)
    $certificatePath = '../uploads/certificates/' . uniqid() . '_certificate.pdf';
    
    // Simpan ke database
    $query = "INSERT INTO certificates (user_id, course_id, certificate_number, file_path, issued_at) 
              VALUES (?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $userId, $attempt['course_id'], $certNumber, $certificatePath);
    $stmt->execute();
    $stmt->close();
    
    $certificate = [
        'certificate_number' => $certNumber,
        'file_path' => $certificatePath
    ];
}

// Cek attempts tersisa
$query = "SELECT COUNT(*) as total FROM exam_attempts WHERE user_id = ? AND exam_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $userId, $attempt['exam_id']);
$stmt->execute();
$result = $stmt->get_result();
$totalAttempts = $result->fetch_assoc()['total'];
$stmt->close();

$remainingAttempts = $attempt['max_attempts'] - $totalAttempts;
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="card">
        <div class="card-header bg-<?php echo $isPassed ? 'success' : 'danger'; ?> text-white">
            <h4 class="mb-0">Hasil Exam: <?php echo htmlspecialchars($attempt['judul']); ?></h4>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <h2>Score: <?php echo $score; ?> / <?php echo $totalPoints; ?></h2>
                <h3 class="text-<?php echo $isPassed ? 'success' : 'danger'; ?>">
                    <?php echo $percentage; ?>% 
                    <?php if ($isPassed): ?>
                        <span class="badge bg-success">PASSED</span>
                    <?php else: ?>
                        <span class="badge bg-danger">FAILED</span>
                    <?php endif; ?>
                </h3>
                <p class="text-muted">Passing Score: <?php echo $passingScore; ?>%</p>
            </div>
            
            <?php if ($isPassed && $certificate): ?>
                <div class="alert alert-success">
                    <h5><i class="bi bi-trophy"></i> Selamat! Anda lulus exam!</h5>
                    <p>Certificate Number: <strong><?php echo htmlspecialchars($certificate['certificate_number']); ?></strong></p>
                    <a href="/peserta/certificates.php" class="btn btn-success">
                        <i class="bi bi-download"></i> Lihat Sertifikat
                    </a>
                </div>
            <?php elseif (!$isPassed && $remainingAttempts > 0): ?>
                <div class="alert alert-warning">
                    <h5>Anda belum lulus exam.</h5>
                    <p>Remaining Attempts: <?php echo $remainingAttempts; ?></p>
                    <a href="/peserta/exam.php?exam_id=<?php echo $attempt['exam_id']; ?>" class="btn btn-primary">
                        Retake Exam
                    </a>
                </div>
            <?php endif; ?>
            
            <hr>
            
            <h5>Review Jawaban</h5>
            <?php
            $qNum = 1;
            while ($question = $questions->fetch_assoc()):
            ?>
                <div class="mb-3 p-3 border rounded">
                    <h6>Pertanyaan <?php echo $qNum; ?> (<?php echo $question['poin']; ?> poin)</h6>
                    <p><?php echo nl2br(htmlspecialchars($question['pertanyaan'])); ?></p>
                    
                    <div class="mb-2">
                        <strong>Jawaban Anda:</strong>
                        <?php if ($question['tipe'] == 'multiple_choice'): ?>
                            <?php
                            $options = json_decode($question['opsi_jawaban'], true);
                            echo htmlspecialchars($options[$question['user_answer']] ?? '-');
                            ?>
                        <?php elseif ($question['tipe'] == 'true_false'): ?>
                            <?php echo $question['user_answer'] == 'true' ? 'True' : 'False'; ?>
                        <?php else: ?>
                            <p><?php echo nl2br(htmlspecialchars($question['user_answer'] ?? '-')); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-2">
                        <strong>Jawaban Benar:</strong>
                        <?php if ($question['tipe'] == 'multiple_choice'): ?>
                            <?php
                            $options = json_decode($question['opsi_jawaban'], true);
                            echo htmlspecialchars($options[$question['jawaban_benar']] ?? '-');
                            ?>
                        <?php elseif ($question['tipe'] == 'true_false'): ?>
                            <?php echo $question['jawaban_benar'] == 'true' ? 'True' : 'False'; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <strong>Poin Diperoleh:</strong> 
                        <span class="badge bg-<?php echo $question['poin_diperoleh'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $question['poin_diperoleh']; ?> / <?php echo $question['poin']; ?>
                        </span>
                    </div>
                </div>
            <?php
                $qNum++;
            endwhile;
            ?>
            
            <div class="mt-4">
                <a href="/peserta/learn.php?course_id=<?php echo $attempt['course_id']; ?>" class="btn btn-outline-primary">
                    Kembali ke Course
                </a>
                <a href="/peserta/dashboard.php" class="btn btn-primary">
                    Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

