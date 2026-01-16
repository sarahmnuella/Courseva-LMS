<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

// Ambil ID Course dari URL
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// 1. Ambil Data Course untuk judul header
$course_query = "SELECT course_name FROM COURSES WHERE course_id = ?";
$course_res = executeQuery($course_query, "i", [$course_id]);
$course = $course_res->fetch_assoc();

// 2. Ambil Data Quiz berdasarkan course_id
$quiz_query = "SELECT * FROM QUIZ WHERE course_id = ? LIMIT 1";
$quiz_res = executeQuery($quiz_query, "i", [$course_id]);
$quiz = $quiz_res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Aturan Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; font-size: 14px; color: #6b7280; transition: all 0.2s; border-radius: 12px; }
        .sidebar-item:hover { color: #3b82f6; background-color: #f8fafc; }
        .sidebar-active { color: #3b82f6; font-weight: 600; background-color: #eff6ff; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 min-h-screen p-6 border-r border-gray-100 flex flex-col fixed bg-white z-50">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg uppercase">COURSEVA</span>
        </div>
        <nav class="space-y-6 flex-1">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
 <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>
             <a href="task.php" class="sidebar-item sidebar-active"><span>📋</span> Task</a>
        </nav>
    </aside>

    <main class="flex-1 ml-64 p-8 flex flex-col min-h-screen">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <a href="lesson.php?id=<?= $course_id ?>" class="text-gray-400 hover:text-gray-600 transition-all text-xl">←</a>
                <h2 class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($course['course_name'] ?? 'Course tidak ditemukan') ?></h2>
                <div class="flex items-center gap-4">
                    <span class="text-gray-400">⚲</span>
                    <span class="text-xs font-bold text-gray-700">Modul Quiz</span>
                </div>
            </div>

            <div class="w-full h-48 bg-zinc-900 rounded-[2rem] relative overflow-hidden mb-10 shadow-lg border-4 border-white">
                <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="relative z-10 h-full flex flex-col items-center justify-center text-center p-6 text-white">
                    <h1 class="text-2xl font-bold mb-4"><?= $quiz ? htmlspecialchars($quiz['quiz_name']) : 'Quiz Belum Tersedia' ?></h1>
                    <p class="text-white/60 text-[10px] uppercase tracking-widest">Persiapkan diri Anda sebaik mungkin</p>
                </div>
                <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-4xl text-white">✦ ✦</div>
            </div>

            <div class="max-w-5xl">
                <h3 class="text-sm font-bold text-gray-800 mb-4 italic">Ketentuan Ujian :</h3>
                <div class="text-sm leading-relaxed text-gray-600 text-justify bg-gray-50 p-8 rounded-[2rem] border border-gray-100">
                    <?php if ($quiz): ?>
                        <p class="mb-4">
                            Selamat datang di <strong><?= htmlspecialchars($quiz['quiz_name']) ?></strong>. <?= htmlspecialchars($quiz['quiz_description']) ?>
                        </p>
                        <ul class="space-y-2 list-disc ml-5 font-medium">
                            <li>Jumlah Pertanyaan: <strong><?= $quiz['total_questions'] ?> Soal</strong></li>
                            <li>Syarat Kelulusan: <strong><?= $quiz['passing_score'] ?>%</strong></li>
                            <li>Durasi Ujian: <strong><?= $quiz['duration_minutes'] ?> Menit</strong></li>
                        </ul>
                        <p class="mt-4 text-xs italic text-blue-500">
                            *Jika tidak lulus, Anda dapat mengulang kembali setelah mempelajari materi. Manfaatkan kesempatan ini sebaik-baiknya.
                        </p>
                    <?php else: ?>
                        <div class="flex flex-col items-center py-10">
                            <span class="text-4xl mb-4">📭</span>
                            <p class="font-bold text-gray-400">Maaf, tidak ada quiz untuk modul ini saat ini.</p>
                            <p class="text-[10px] uppercase tracking-widest mt-2">Nantikan tugas akhir di akhir kursus!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 py-8 text-[11px] font-bold text-gray-400 tracking-widest uppercase bg-white">
            <a href="dashboard.php" class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                <span>☸</span> HOME
            </a>
            <div class="flex items-center gap-2 text-gray-800">
                <?= $quiz ? 'ATURAN KUIZ' : 'QUIZ KOSONG' ?>
            </div>
            <?php if ($quiz): ?>
                <a href="Quiz.php?quiz_id=<?= $quiz['quiz_id'] ?>&course_id=<?= $course_id ?>" class="flex items-center gap-2 cursor-pointer text-blue-600 hover:text-blue-800 transition-all">
                    START <span>☸</span>
                </a>
            <?php else: ?>
                <div class="text-gray-300 flex items-center gap-2 cursor-not-allowed">
                    START <span>☸</span>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>