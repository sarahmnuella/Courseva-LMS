<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

// Ambil ID dari URL (Sesuai gambar Anda: lesson.php?id=1)
// Pastikan variabelnya 'id', bukan 'course_id' agar cocok dengan URL di browser
$id_dari_url = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Query Data Course
$course_query = "SELECT * FROM COURSES WHERE course_id = ?";
$course_res = executeQuery($course_query, "i", [$id_dari_url]);
$course = $course_res->fetch_assoc();

// 2. Query Daftar Modul
$modules = [];
if ($course) {
    $module_query = "SELECT * FROM MODULES WHERE course_id = ? ORDER BY module_order ASC";
    $module_res = executeQuery($module_query, "i", [$id_dari_url]);
    while ($row = $module_res->fetch_assoc()) {
        $modules[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Learning Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; font-size: 14px; color: #6b7280; transition: all 0.2s; border-radius: 12px; }
        .sidebar-active { color: #3b82f6; font-weight: 600; background-color: #eff6ff; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 min-h-screen p-6 border-r border-gray-100 flex flex-col fixed bg-white z-50 shadow-sm">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg uppercase">COURSEVA</span>
        </div>
        <nav class="space-y-2 flex-1">
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
 <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>            <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
        </nav>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <?php if (!$course): ?>
            <div class="flex flex-col items-center justify-center min-h-[70vh] text-center">
                <div class="w-24 h-24 bg-red-50 text-red-500 rounded-full flex items-center justify-center text-5xl mb-6 shadow-sm">🔭</div>
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Ups! Kursus Lenyap</h2>
                <p class="text-slate-500 text-sm max-w-xs leading-relaxed mb-8">
                    ID Kursus <strong>(<?= $id_dari_url ?>)</strong> tidak terdaftar di database kami. Silakan kembali ke Dashboard.
                </p>
                <a href="dashboard.php" class="px-8 py-3 bg-blue-600 text-white rounded-2xl font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">Kembali Ke Dashboard</a>
            </div>
        <?php else: ?>
            
            <div class="flex items-center justify-between mb-8">
                <a href="dashboard.php" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-blue-600 transition">←</a>
                <h2 class="text-sm font-semibold text-gray-700"><?= htmlspecialchars($course['course_name']) ?></h2>
                <div class="px-4 py-1.5 bg-blue-50 text-blue-600 rounded-full text-[10px] font-bold uppercase tracking-wider">
                    <?= htmlspecialchars($course['level']) ?>
                </div>
            </div>

            <?php if (empty($modules)): ?>
                <div class="bg-white rounded-[3rem] p-16 text-center shadow-sm border border-slate-100">
                    <div class="relative w-32 h-32 mx-auto mb-8">
                        <div class="absolute inset-0 bg-blue-100 rounded-full animate-ping opacity-20"></div>
                        <div class="relative w-full h-full bg-blue-50 rounded-full flex items-center justify-center text-5xl">🛠️</div>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-800 mb-3">Modul Belum Disediakan</h2>
                    <p class="text-slate-500 text-sm max-w-sm mx-auto leading-relaxed mb-8">
                        Maaf, materi untuk kursus ini belum tersedia. Mohon menunggu secara berkala, tim kami sedang menyiapkannya untuk Anda!
                    </p>
                    <a href="dashboard.php" class="px-10 py-3.5 bg-slate-100 text-slate-600 rounded-2xl text-xs font-bold hover:bg-slate-200 transition">Lihat Kursus Lain</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($modules as $mod): ?>
                    <div class="bg-white p-6 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-blue-500/5 transition-all group">
                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <?= $mod['module_order'] ?>
                            </div>
                            <span class="text-[9px] font-bold text-slate-400 bg-slate-50 px-3 py-1 rounded-full uppercase italic"><?= $mod['estimated_duration_minutes'] ?> Mins</span>
                        </div>
                        <h3 class="font-bold text-slate-800 mb-3 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($mod['module_name']) ?></h3>
                        <p class="text-[11px] text-slate-400 leading-relaxed line-clamp-2 mb-6"><?= htmlspecialchars($mod['module_description'] ?: 'Pelajari materi ini untuk menguasai keterampilan baru secara bertahap.') ?></p>
                        <a href="learn.php?course_id=<?= $course['course_id'] ?>&module_id=<?= $mod['module_id'] ?>" class="flex items-center justify-center w-full py-3 bg-slate-50 group-hover:bg-blue-600 group-hover:text-white text-slate-500 rounded-2xl text-[10px] font-bold transition-all uppercase tracking-widest">
                            Belajar Sekarang <span class="ml-2">→</span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>
</body>
</html>