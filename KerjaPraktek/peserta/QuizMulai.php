<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// 1. Ambil Data Kuis
$quiz_query = "SELECT * FROM QUIZ WHERE quiz_id = ?";
$quiz_res = executeQuery($quiz_query, "i", [$quiz_id]);
$quiz = $quiz_res->fetch_assoc();

if (!$quiz) {
    die("Kuis tidak ditemukan.");
}

// 2. Ambil Semua Pertanyaan & Jawaban
$questions_query = "SELECT * FROM QUIZ_QUESTIONS WHERE quiz_id = ? ORDER BY question_order ASC";
$questions_res = executeQuery($questions_query, "i", [$quiz_id]);

$all_questions = [];
while ($q = $questions_res->fetch_assoc()) {
    // Ambil pilihan jawaban untuk setiap pertanyaan
    $ans_query = "SELECT * FROM QUIZ_ANSWERS WHERE question_id = ?";
    $ans_res = executeQuery($ans_query, "i", [$q['question_id']]);
    $answers = [];
    while ($a = $ans_res->fetch_assoc()) {
        $answers[] = $a;
    }
    $q['choices'] = $answers;
    $all_questions[] = $q;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz: <?= htmlspecialchars($quiz['quiz_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .answer-option:checked + label { border-color: #3b82f6; background-color: #eff6ff; color: #3b82f6; }
    </style>
</head>
<body class="pb-20">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 px-8 py-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <span class="text-xs font-bold bg-blue-100 text-blue-600 px-3 py-1 rounded-full uppercase">Live Quiz</span>
            <h1 class="text-sm font-bold text-gray-800"><?= htmlspecialchars($quiz['quiz_name']) ?></h1>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Waktu Tersisa</p>
                <p id="timer" class="text-sm font-black text-red-500"><?= $quiz['duration_minutes'] ?>:00</p>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto mt-10 px-6">
        <form action="process_quiz.php" method="POST" id="quizForm">
            <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
            <input type="hidden" name="course_id" value="<?= $course_id ?>">

            <?php foreach ($all_questions as $index => $q): ?>
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 mb-8">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="w-8 h-8 bg-blue-600 text-white rounded-xl flex items-center justify-center text-xs font-bold"><?= $index + 1 ?></span>
                        <p class="text-sm font-bold text-gray-700">Pertanyaan <?= $index + 1 ?></p>
                    </div>
                    
                    <h2 class="text-lg text-gray-800 mb-8 leading-relaxed"><?= htmlspecialchars($q['question_text']) ?></h2>

                    <div class="space-y-3">
                        <?php foreach ($q['choices'] as $choice): ?>
                            <div class="relative">
                                <input type="radio" name="question_<?= $q['question_id'] ?>" 
                                       id="ans_<?= $choice['answer_id'] ?>" 
                                       value="<?= $choice['answer_id'] ?>" 
                                       class="answer-option hidden" required>
                                <label for="ans_<?= $choice['answer_id'] ?>" 
                                       class="flex items-center p-4 border-2 border-gray-50 rounded-2xl cursor-pointer hover:bg-gray-50 transition-all text-sm font-medium text-gray-600">
                                    <?= htmlspecialchars($choice['answer_text']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

    <div class="flex justify-center mt-12">
        <button type="submit" id="btnSubmit" class="bg-blue-600 text-white px-12 py-4 rounded-3xl font-bold text-sm shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all transform hover:scale-105">
            Kirim Jawaban Akhir
        </button>
</div>
        </form>
    </main>

    <script>
        // Script Timer Sederhana
        let time = <?= $quiz['duration_minutes'] * 60 ?>;
        const timerElement = document.getElementById('timer');

        const countdown = setInterval(() => {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;
            timerElement.innerHTML = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            if (time <= 0) {
                clearInterval(countdown);
                document.getElementById('quizForm').submit(); // Auto-submit jika waktu habis
            }
            time--;
        }, 1000);
        document.getElementById('quizForm').addEventListener('submit', function(e) {
    // Ambil semua grup pertanyaan
    const totalQuestions = <?= count($all_questions) ?>;
    const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;

    // Cek apakah semua sudah dijawab
    if (answeredQuestions < totalQuestions) {
        e.preventDefault(); // Batalkan pengiriman
        alert('Mohon jawab semua pertanyaan sebelum mengirim!');
        return;
    }

    // Konfirmasi sebelum kirim
    const konfirmasi = confirm('Apakah Anda yakin ingin mengirim semua jawaban? Anda tidak dapat mengubahnya nanti.');
    if (!konfirmasi) {
        e.preventDefault(); // Batalkan jika user menekan 'Cancel'
    }
});
    </script>
</body>
</html>