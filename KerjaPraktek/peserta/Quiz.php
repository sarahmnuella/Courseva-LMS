<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama_lengkap'] ?? 'User';
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// 1. Ambil Data Course untuk judul header
$course_query = "SELECT course_name FROM COURSES WHERE course_id = ?";
$course_res = executeQuery($course_query, "i", [$course_id]);
$course = $course_res->fetch_assoc();

// 2. Ambil Data Quiz berdasarkan course_id
$quiz_query = "SELECT * FROM QUIZ WHERE course_id = ? LIMIT 1";
$quiz_res = executeQuery($quiz_query, "i", [$course_id]);
$quiz = $quiz_res->fetch_assoc();

// 3. Query Daftar Teman (Friends) untuk Sidebar
$friend_query = "SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/logo.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide text-lg">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
 <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>
             <a href="task.php" class="sidebar-item sidebar-active"><span>📋</span> Task</a>
            
            <div class="mt-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-3 px-2">
                    <?php while($friend = $friend_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center text-[10px]">👤</div>
                        <span class="text-xs text-gray-600 truncate"><?= htmlspecialchars($friend['nama_lengkap']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </nav>

        <div class="mt-auto pt-6 border-t border-gray-100 space-y-1">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-2">Account</p>
            <a href="profil.php" class="sidebar-item"><span>⚙️</span> Profil</a>
            <a href="../logout.php" class="sidebar-item text-red-500 hover:bg-red-50"><span>🚪</span> Keluar</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8 flex flex-col min-h-screen">
        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <a href="lesson.php?id=<?= $course_id ?>" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-blue-600 transition">←</a>
                    <h2 class="text-sm font-bold text-gray-700"><?= htmlspecialchars($course['course_name'] ?? 'Course tidak ditemukan') ?></h2>
                </div>
                
                <a href="profil.php" class="flex items-center gap-4 group cursor-pointer">
                    <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?= htmlspecialchars($user_name); ?>!</span>
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:shadow-md transition">
                        <span>👤</span>
                    </div>
                </a>
            </div>

            <div class="w-full h-48 bg-zinc-900 rounded-[2.5rem] relative overflow-hidden mb-10 shadow-lg border-4 border-white">
                <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="relative z-10 h-full flex flex-col items-center justify-center text-center p-6 text-white">
                    <h1 class="text-2xl font-bold mb-4 italic uppercase tracking-tighter">
                        <?= $quiz ? htmlspecialchars($quiz['quiz_name']) : 'Quiz Belum Tersedia' ?>
                    </h1>
                    <p class="text-white/60 text-[10px] uppercase tracking-widest font-bold">Ujian Kompetensi Akhir Modul</p>
                </div>
                <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-4xl text-white">✦ ✦</div>
            </div>

            <div class="max-w-5xl">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 italic">Ketentuan Ujian :</h3>
                <div class="text-sm leading-relaxed text-gray-600 text-justify bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm">
                    <?php if ($quiz): ?>
                        <p class="mb-6 font-medium">
                            Selamat datang di <strong><?= htmlspecialchars($quiz['quiz_name']) ?></strong>. <?= htmlspecialchars($quiz['quiz_description']) ?>
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100">
                                <p class="text-[10px] text-blue-400 font-bold uppercase mb-1">Total Soal</p>
                                <p class="text-lg font-bold text-blue-700"><?= $quiz['total_questions'] ?> Pertanyaan</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-2xl border border-green-100">
                                <p class="text-[10px] text-green-400 font-bold uppercase mb-1">Passing Score</p>
                                <p class="text-lg font-bold text-green-700"><?= $quiz['passing_score'] ?>% Min.</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-2xl border border-purple-100">
                                <p class="text-[10px] text-purple-400 font-bold uppercase mb-1">Waktu</p>
                                <p class="text-lg font-bold text-purple-700"><?= $quiz['duration_minutes'] ?> Menit</p>
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-6 rounded-2xl border-l-4 border-yellow-400">
                            <p class="text-xs italic text-yellow-700 leading-relaxed font-medium">
                                <strong>Perhatian:</strong> Jika hasil akhir di bawah skor kelulusan, Anda diwajibkan mengulang kembali. Pastikan koneksi internet stabil sebelum menekan tombol START.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="flex flex-col items-center py-10">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-3xl mb-4">📭</div>
                            <p class="font-bold text-gray-400">Maaf, tidak ada quiz untuk modul ini saat ini.</p>
                            <p class="text-[10px] uppercase tracking-widest mt-2 font-bold text-gray-300">Hubungi instruktur untuk informasi lebih lanjut</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 py-8 text-[11px] font-black text-gray-400 tracking-[0.2em] uppercase bg-transparent">
            <a href="dashboard.php" class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                <span>☸</span> HOME
            </a>
            <div class="flex items-center gap-2 text-gray-800">
                <?= $quiz ? 'ATURAN KUIZ' : 'QUIZ KOSONG' ?>
            </div>
            <?php if ($quiz): ?>
                <a href="QuizMulai.php?quiz_id=<?= $quiz['quiz_id'] ?>&course_id=<?= $course_id ?>" class="flex items-center gap-2 cursor-pointer text-blue-600 hover:text-blue-800 transition-all font-black">
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