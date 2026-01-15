<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Manage Questions - Courseva";
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

// Delete question
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_question'])) {
    $questionId = $_POST['question_id'];
    $query = "DELETE FROM exam_questions WHERE id = ? AND exam_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $questionId, $examId);
    $stmt->execute();
    $stmt->close();
    
    redirectWithMessage('/pengajar/manage_questions.php?exam_id=' . $examId, 'Pertanyaan berhasil dihapus.', 'success');
}

// Ambil semua pertanyaan
$query = "SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY urutan ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $examId);
$stmt->execute();
$questions = $stmt->get_result();

// Add/Edit question
$questionId = $_GET['question_id'] ?? null;
$question = null;
if ($questionId) {
    $query = "SELECT * FROM exam_questions WHERE id = ? AND exam_id = ?";
    $stmt2 = $conn->prepare($query);
    $stmt2->bind_param("ii", $questionId, $examId);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $question = $result->fetch_assoc();
    $stmt2->close();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['delete_question'])) {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $pertanyaan = sanitize($_POST['pertanyaan'] ?? '');
        $tipe = $_POST['tipe'] ?? 'multiple_choice';
        $poin = $_POST['poin'] ?? 1;
        $urutan = $_POST['urutan'] ?? 1;
        $jawaban_benar = sanitize($_POST['jawaban_benar'] ?? '');
        
        if (empty($pertanyaan)) {
            $errors[] = "Pertanyaan harus diisi.";
        }
        
        $opsi_jawaban = null;
        if ($tipe == 'multiple_choice') {
            $options = [];
            for ($i = 1; $i <= 4; $i++) {
                $option = sanitize($_POST['option_' . $i] ?? '');
                if (!empty($option)) {
                    $options['option_' . $i] = $option;
                }
            }
            if (count($options) < 2) {
                $errors[] = "Minimal 2 opsi jawaban untuk multiple choice.";
            }
            $opsi_jawaban = json_encode($options);
        }
        
        if (empty($errors)) {
            if ($question) {
                // Update
                $query = "UPDATE exam_questions SET pertanyaan = ?, tipe = ?, opsi_jawaban = ?, 
                          jawaban_benar = ?, poin = ?, urutan = ?, updated_at = NOW() 
                          WHERE id = ? AND exam_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssiiii", $pertanyaan, $tipe, $opsi_jawaban, $jawaban_benar, $poin, $urutan, $questionId, $examId);
            } else {
                // Insert
                $query = "INSERT INTO exam_questions (exam_id, pertanyaan, tipe, opsi_jawaban, jawaban_benar, poin, urutan, created_at) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issssii", $examId, $pertanyaan, $tipe, $opsi_jawaban, $jawaban_benar, $poin, $urutan);
            }
            
            if ($stmt->execute()) {
                $stmt->close();
                redirectWithMessage('/pengajar/manage_questions.php?exam_id=' . $examId, 'Pertanyaan berhasil disimpan!', 'success');
            } else {
                $errors[] = "Terjadi kesalahan saat menyimpan pertanyaan.";
            }
            $stmt->close();
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Manage Questions: <?php echo htmlspecialchars($exam['judul']); ?></h2>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $question ? 'Edit' : 'Tambah'; ?> Pertanyaan</h5>
                </div>
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
                            <label for="pertanyaan" class="form-label">Pertanyaan <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="pertanyaan" name="pertanyaan" rows="3" required><?php echo htmlspecialchars($question['pertanyaan'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipe" class="form-label">Tipe <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tipe" name="tipe" required>
                                        <option value="multiple_choice" <?php echo ($question['tipe'] ?? '') == 'multiple_choice' ? 'selected' : ''; ?>>Multiple Choice</option>
                                        <option value="true_false" <?php echo ($question['tipe'] ?? '') == 'true_false' ? 'selected' : ''; ?>>True/False</option>
                                        <option value="essay" <?php echo ($question['tipe'] ?? '') == 'essay' ? 'selected' : ''; ?>>Essay</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="poin" class="form-label">Poin <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="poin" name="poin" 
                                           value="<?php echo $question['poin'] ?? '1'; ?>" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="urutan" class="form-label">Urutan <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="urutan" name="urutan" 
                                           value="<?php echo $question['urutan'] ?? '1'; ?>" min="1" required>
                                </div>
                            </div>
                        </div>
                        
                        <div id="multipleChoiceSection" style="display: none;">
                            <?php
                            $options = $question ? json_decode($question['opsi_jawaban'], true) : [];
                            for ($i = 1; $i <= 4; $i++):
                                $optionKey = 'option_' . $i;
                                $optionValue = $options[$optionKey] ?? '';
                            ?>
                                <div class="mb-3">
                                    <label for="option_<?php echo $i; ?>" class="form-label">Opsi <?php echo $i; ?></label>
                                    <div class="input-group">
                                        <div class="input-group-text">
                                            <input type="radio" name="jawaban_benar" value="option_<?php echo $i; ?>" 
                                                   <?php echo ($question['jawaban_benar'] ?? '') == 'option_' . $i ? 'checked' : ''; ?> required>
                                        </div>
                                        <input type="text" class="form-control" id="option_<?php echo $i; ?>" 
                                               name="option_<?php echo $i; ?>" value="<?php echo htmlspecialchars($optionValue); ?>">
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                        
                        <div id="trueFalseSection" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Jawaban Benar</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jawaban_benar" value="true" 
                                               id="true_answer" <?php echo ($question['jawaban_benar'] ?? '') == 'true' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="true_answer">True</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jawaban_benar" value="false" 
                                               id="false_answer" <?php echo ($question['jawaban_benar'] ?? '') == 'false' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="false_answer">False</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div id="essaySection" style="display: none;">
                            <div class="alert alert-info">
                                Essay questions akan dinilai secara manual oleh pengajar.
                            </div>
                            <input type="hidden" name="jawaban_benar" value="">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Pertanyaan</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- List Questions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Pertanyaan</h5>
                </div>
                <div class="card-body">
                    <?php if ($questions && $questions->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Urutan</th>
                                        <th>Pertanyaan</th>
                                        <th>Tipe</th>
                                        <th>Poin</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($q = $questions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $q['urutan']; ?></td>
                                            <td><?php echo htmlspecialchars(substr($q['pertanyaan'], 0, 50)) . '...'; ?></td>
                                            <td><?php echo ucfirst(str_replace('_', ' ', $q['tipe'])); ?></td>
                                            <td><?php echo $q['poin']; ?></td>
                                            <td>
                                                <a href="/pengajar/manage_questions.php?exam_id=<?php echo $examId; ?>&question_id=<?php echo $q['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Yakin ingin menghapus pertanyaan ini?');">
                                                    <input type="hidden" name="question_id" value="<?php echo $q['id']; ?>">
                                                    <button type="submit" name="delete_question" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pertanyaan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/pengajar/submissions.php?exam_id=<?php echo $examId; ?>" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-check-circle"></i> Lihat Submissions
                    </a>
                    <a href="/pengajar/edit_course.php?id=<?php echo $exam['course_id']; ?>" class="btn btn-outline-secondary w-100">
                        Kembali ke Course
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tipe').addEventListener('change', function() {
    const tipe = this.value;
    document.getElementById('multipleChoiceSection').style.display = tipe === 'multiple_choice' ? 'block' : 'none';
    document.getElementById('trueFalseSection').style.display = tipe === 'true_false' ? 'block' : 'none';
    document.getElementById('essaySection').style.display = tipe === 'essay' ? 'block' : 'none';
});

document.getElementById('tipe').dispatchEvent(new Event('change'));
</script>

<?php include '../includes/footer.php'; ?>

