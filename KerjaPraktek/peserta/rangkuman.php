<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// 1. Proteksi Halaman: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama_lengkap'] ?? 'User';
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// 2. Safety Check: Pastikan user memang sudah selesai semua modul
$total_mod_res = executeQuery("SELECT COUNT(*) as total FROM MODULES WHERE course_id = ?", "i", [$course_id]);
$total_mod = $total_mod_res->fetch_assoc()['total'];

$done_mod_res = executeQuery("SELECT COUNT(*) as done FROM USER_MODULE_PROGRESS ump 
                               JOIN MODULES m ON ump.module_id = m.module_id 
                               WHERE ump.user_id = ? AND m.course_id = ? AND ump.is_completed = 1", "ii", [$user_id, $course_id]);
$done_mod = $done_mod_res->fetch_assoc()['done'];

if ($total_mod == 0 || $done_mod < $total_mod) {
    header("Location: lesson.php?id=$course_id"); 
    exit();
}

// 3. Ambil Data Course
$course = executeQuery("SELECT * FROM COURSES WHERE course_id = ?", "i", [$course_id])->fetch_assoc();

// 4. Query Daftar Teman (Friends) untuk Sidebar
$friend_query = "SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);

// 5. Ambil quiz_id untuk link tombol mulai
$quiz_res = executeQuery("SELECT quiz_id FROM QUIZ WHERE course_id = ? LIMIT 1", "i", [$course_id]);
$quiz_data = $quiz_res->fetch_assoc();
$quiz_id = $quiz_data['quiz_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rangkuman Akhir: <?= htmlspecialchars($course['course_name']) ?></title>
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
             <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
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

    <main class="flex-1 ml-64 p-8 min-h-screen flex flex-col items-center justify-center">
        <div class="absolute top-8 right-8">
            <a href="profil.php" class="flex items-center gap-4 group cursor-pointer">
                <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?= htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:shadow-md transition">
                    <span>👤</span>
                </div>
            </a>
        </div>

        <div class="max-w-3xl w-full bg-white p-12 rounded-[3.5rem] shadow-xl border border-gray-100 text-center mt-10">
            <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-8 text-3xl font-bold italic">
                i
            </div>
            <h1 class="text-3xl font-black text-gray-800 mb-6 uppercase tracking-tight">Rangkuman Kursus</h1>
            
            <div class="text-left text-gray-600 space-y-4 mb-10 leading-relaxed">
                <p>Anda telah berjaya melalui setiap fasa pembelajaran dalam kursus <strong><?= htmlspecialchars($course['course_name']) ?></strong>. Berikut adalah perkara utama yang telah dipelajari:</p>
                <ul class="list-disc ml-5 space-y-2 text-sm font-medium">
                    <li>Pemahaman mendalam tentang struktur teori dan praktikal kursus.</li>
                    <li>Penyelesaian tugasan berasaskan video dan dokumen PDF.</li>
                    <li>Kesediaan mental untuk ujian akhir.</li>
                </ul>
                <div class="bg-yellow-50 p-6 rounded-3xl border-l-8 border-yellow-400 text-xs">
                    <strong class="text-yellow-700">Nota Penting:</strong> Quiz ini memerlukan markah minimum <span class="font-bold underline">70</span> untuk lulus dan mendapatkan sertifikat. Sila pastikan sambungan internet anda stabil sebelum memulai.
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <a href="Quiz.php?quiz_id=<?= $quiz_id ?>&course_id=<?= $course_id ?>" class="bg-blue-600 text-white px-10 py-5 rounded-3xl font-bold text-lg hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1 active:scale-95">
                    🚀 Mulakan Quiz Sekarang
                </a>
                <a href="lesson.php?id=<?= $course_id ?>" class="text-gray-400 font-bold text-xs hover:text-blue-600 transition-all uppercase tracking-widest">
                    Ulang Kaji Materi
                </a>
            </div>
        </div>
    </main>

</body>
</html>