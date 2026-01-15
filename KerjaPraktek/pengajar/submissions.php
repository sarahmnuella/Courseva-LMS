<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Submissions - Courseva";
$userId = $_SESSION['user_id'];
$examId = $_GET['exam_id'] ?? null;

if (!$examId) {
    redirectWithMessage('/pengajar/courses.php', 'Exam tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Cek apakah exam milik pengajar
$query = "SELECT e.*, c.judul as course_judul 
          FROM exams e
          INNER JOIN courses c ON e.course_id = c.id
          WHERE e.id = ? AND c.pengajar_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $examId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();
$stmt->close();

if (!$exam) {
    redirectWithMessage('/pengajar/courses.php', 'Exam tidak ditemukan atau bukan milik Anda.', 'error');
}

// Grade essay
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_essay'])) {
    $attemptId = $_POST['attempt_id'];
    $questionId = $_POST['question_id'];
    $poin = $_POST['poin'] ?? 0;
    
    // Update poin
    $query = "UPDATE exam_answers SET poin_diperoleh = ? WHERE attempt_id = ? AND question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("dii", $poin, $attemptId, $questionId);
    $stmt->execute();
    $stmt->close();
    
    // Recalculate total score
    $query = "SELECT SUM(poin_diperoleh) as total FROM exam_answers WHERE attempt_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalScore = $result->fetch_assoc()['total'];
    $stmt->close();
    
    // Update attempt score
    $query = "UPDATE exam_attempts SET score = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("di", $totalScore, $attemptId);
    $stmt->execute();
    $stmt->close();
    
    redirectWithMessage('/pengajar/submissions.php?exam_id=' . $examId, 'Poin berhasil diupdate!', 'success');
}

// Ambil semua attempts
$query = "SELECT ea.*, u.nama_lengkap, u.email
          FROM exam_attempts ea
          INNER JOIN users u ON ea.user_id = u.id
          WHERE ea.exam_id = ?
          ORDER BY ea.submitted_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $examId);
$stmt->execute();
$attempts = $stmt->get_result();

// Ambil total poin
$query = "SELECT SUM(poin) as total FROM exam_questions WHERE exam_id = ?";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("i", $examId);
$stmt2->execute();
$result = $stmt2->get_result();
$totalPoints = $result->fetch_assoc()['total'];
$stmt2->close();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Submissions: <?php echo htmlspecialchars($exam['judul']); ?></h2>
    
    <div class="card">
        <div class="card-body">
            <?php if ($attempts && $attempts->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>Email</th>
                                <th>Score</th>
                                <th>Status</th>
                                <th>Tanggal Submit</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($attempt = $attempts->fetch_assoc()): ?>
                                <?php
                                $score = $attempt['score'] ?? 0;
                                $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100) : 0;
                                $isPassed = $percentage >= $exam['passing_score'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($attempt['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($attempt['email']); ?></td>
                                    <td>
                                        <strong><?php echo $score; ?> / <?php echo $totalPoints; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $percentage; ?>%</small>
                                    </td>
                                    <td>
                                        <?php if ($attempt['status'] == 'completed'): ?>
                                            <span class="badge bg-<?php echo $isPassed ? 'success' : 'danger'; ?>">
                                                <?php echo $isPassed ? 'PASSED' : 'FAILED'; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><?php echo strtoupper($attempt['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatTanggal($attempt['submitted_at'] ?? $attempt['started_at'], true); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#detailModal<?php echo $attempt['id']; ?>">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Modal Detail -->
                                <div class="modal fade" id="detailModal<?php echo $attempt['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Submission - <?php echo htmlspecialchars($attempt['nama_lengkap']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                // Ambil semua pertanyaan dan jawaban
                                                $query = "SELECT eq.*, ea.jawaban as user_answer, ea.poin_diperoleh 
                                                          FROM exam_questions eq
                                                          LEFT JOIN exam_answers ea ON eq.id = ea.question_id AND ea.attempt_id = ?
                                                          WHERE eq.exam_id = ?
                                                          ORDER BY eq.urutan ASC";
                                                $stmt3 = $conn->prepare($query);
                                                $stmt3->bind_param("ii", $attempt['id'], $examId);
                                                $stmt3->execute();
                                                $questions = $stmt3->get_result();
                                                
                                                $qNum = 1;
                                                while ($question = $questions->fetch_assoc()):
                                                ?>
                                                    <div class="mb-4 p-3 border rounded">
                                                        <h6>Pertanyaan <?php echo $qNum; ?> (<?php echo $question['poin']; ?> poin)</h6>
                                                        <p><?php echo nl2br(htmlspecialchars($question['pertanyaan'])); ?></p>
                                                        
                                                        <div class="mb-2">
                                                            <strong>Jawaban:</strong>
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
                                                            <strong>Poin Diperoleh:</strong>
                                                            <?php if ($question['tipe'] == 'essay'): ?>
                                                                <form method="POST" action="" class="d-inline">
                                                                    <input type="hidden" name="attempt_id" value="<?php echo $attempt['id']; ?>">
                                                                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                                                    <div class="input-group" style="max-width: 200px;">
                                                                        <input type="number" class="form-control" name="poin" 
                                                                               value="<?php echo $question['poin_diperoleh'] ?? 0; ?>" 
                                                                               min="0" max="<?php echo $question['poin']; ?>" step="0.5">
                                                                        <span class="input-group-text">/ <?php echo $question['poin']; ?></span>
                                                                        <button type="submit" name="grade_essay" class="btn btn-success">
                                                                            <i class="bi bi-check"></i>
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            <?php else: ?>
                                                                <span class="badge bg-<?php echo ($question['poin_diperoleh'] ?? 0) > 0 ? 'success' : 'danger'; ?>">
                                                                    <?php echo $question['poin_diperoleh'] ?? 0; ?> / <?php echo $question['poin']; ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php
                                                    $qNum++;
                                                endwhile;
                                                $stmt3->close();
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada submission.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

