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
    <title>Exam Mode: <?= htmlspecialchars($quiz['quiz_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #fdfdfe; }
        .answer-card input:checked + label {
            border-color: #3b82f6;
            background-color: #f0f7ff;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1);
        }
        .nav-number.active { background-color: #3b82f6; color: white; border-color: #3b82f6; }
        .nav-number.answered { background-color: #dcfce7; color: #166534; border-color: #86efac; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <nav class="bg-white/80 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 px-8 py-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <div class="p-2 bg-blue-600 rounded-lg shadow-lg shadow-blue-200">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <h1 class="text-sm font-bold text-slate-800 tracking-tight"><?= htmlspecialchars($quiz['quiz_name']) ?></h1>
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Ujian Kompetensi</p>
            </div>
        </div>
        
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3 px-4 py-2 bg-red-50 rounded-2xl border border-red-100">
                <svg class="w-4 h-4 text-red-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span id="timer" class="text-sm font-bold text-red-600 tabular-nums">00:00</span>
            </div>
        </div>
    </nav>

    <div class="flex flex-1 max-w-7xl mx-auto w-full gap-8 p-8">
        
        <aside class="w-72 sticky top-24 h-fit hidden lg:block">
            <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Navigasi Soal</h3>
                <div class="grid grid-cols-4 gap-3">
                    <?php foreach ($all_questions as $index => $q): ?>
                        <a href="#q-<?= $index + 1 ?>" 
                           id="nav-<?= $q['question_id'] ?>"
                           class="nav-number w-12 h-12 rounded-xl border-2 border-slate-50 flex items-center justify-center text-sm font-bold text-slate-400 transition-all hover:border-blue-200 hover:text-blue-500">
                            <?= $index + 1 ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-50 space-y-3">
                    <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-slate-100 border border-slate-200"></span> Belum Dijawab
                    </div>
                    <div class="flex items-center gap-3 text-[11px] font-semibold text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-green-100 border border-green-300"></span> Sudah Dijawab
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1">
            <form action="process_quiz.php" method="POST" id="quizForm">
                <input type="hidden" name="quiz_id" value="<?= $quiz_id ?>">
                <input type="hidden" name="course_id" value="<?= $course_id ?>">

                <div class="space-y-10">
                    <?php foreach ($all_questions as $index => $q): ?>
                        <section id="q-<?= $index + 1 ?>" class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100 transition-all hover:shadow-md">
                            <div class="flex items-center gap-4 mb-8">
                                <span class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-xs font-bold tracking-wide uppercase">Pertanyaan <?= $index + 1 ?></span>
                                <div class="h-[1px] flex-1 bg-slate-50"></div>
                            </div>
                            
                            <h2 class="text-xl text-slate-800 font-semibold mb-10 leading-relaxed">
                                <?= htmlspecialchars($q['question_text']) ?>
                            </h2>

                            <div class="grid grid-cols-1 gap-4">
                                <?php foreach ($q['choices'] as $choice): ?>
                                    <div class="answer-card relative group">
                                        <input type="radio" 
                                               name="question_<?= $q['question_id'] ?>" 
                                               id="ans_<?= $choice['answer_id'] ?>" 
                                               value="<?= $choice['answer_id'] ?>" 
                                               class="answer-option hidden" 
                                               onchange="markAnswered(<?= $q['question_id'] ?>)"
                                               required>
                                        <label for="ans_<?= $choice['answer_id'] ?>" 
                                               class="flex items-center p-5 border-2 border-slate-50 rounded-[1.5rem] cursor-pointer hover:bg-slate-50 hover:border-slate-200 transition-all text-slate-600 font-medium group-active:scale-[0.98]">
                                            <span class="w-10 h-10 rounded-full border-2 border-slate-100 flex items-center justify-center mr-4 text-xs font-bold group-hover:border-blue-200 transition-all">
                                                </span>
                                            <?= htmlspecialchars($choice['answer_text']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="mt-16 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-slate-800">Selesai Mengerjakan?</h4>
                        <p class="text-xs text-slate-400">Pastikan semua soal telah terjawab sebelum mengirim.</p>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-10 py-4 rounded-2xl font-bold text-sm shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all transform hover:scale-105 active:scale-95">
                        Kumpulkan Jawaban Sekarang
                    </button>
                </div>
            </form>
        </main>
    </div>

    <script>
        // Timer Logic
        let time = <?= $quiz['duration_minutes'] * 60 ?>;
        const timerElement = document.getElementById('timer');

        const countdown = setInterval(() => {
            let minutes = Math.floor(time / 60);
            let seconds = time % 60;
            timerElement.innerHTML = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            if (time <= 0) {
                clearInterval(countdown);
                document.getElementById('quizForm').submit();
            }
            time--;
        }, 1000);

        // UI Tracking Logic
        function markAnswered(questionId) {
            const navElement = document.getElementById('nav-' + questionId);
            if (navElement) {
                navElement.classList.add('answered');
            }
        }

        // Form Validation & Confirmation
        document.getElementById('quizForm').addEventListener('submit', function(e) {
            const totalQuestions = <?= count($all_questions) ?>;
            const answeredQuestions = document.querySelectorAll('input[type="radio"]:checked').length;

            if (answeredQuestions < totalQuestions) {
                e.preventDefault();
                alert('⚠️ Anda belum menjawab ' + (totalQuestions - answeredQuestions) + ' soal. Mohon lengkapi semua jawaban!');
                return;
            }

            const konfirmasi = confirm('Apakah Anda yakin ingin mengirim semua jawaban? Tindakan ini tidak dapat dibatalkan.');
            if (!konfirmasi) {
                e.preventDefault();
            }
        });

        // Highlight active navigation on scroll
        window.addEventListener('scroll', () => {
            let current = "";
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                if (pageYOffset >= sectionTop - 150) {
                    current = section.getAttribute('id');
                }
            });

            document.querySelectorAll('.nav-number').forEach(a => {
                a.classList.remove('active');
                if (a.getAttribute('href') === '#' + current) {
                    a.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>