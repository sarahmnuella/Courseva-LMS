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
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 1. Ambil Data Course
$course_query = "SELECT * FROM COURSES WHERE course_id = ?";
$course_res = executeQuery($course_query, "i", [$course_id]);
$course = $course_res->fetch_assoc();

if (!$course) {
    die("<div style='text-align:center; padding:50px;'><h2>Kursus tidak ditemukan!</h2><a href='dashboard.php'>Kembali ke Dashboard</a></div>");
}

// 2. Ambil Daftar Modul
$module_query = "SELECT * FROM MODULES WHERE course_id = ? ORDER BY module_order ASC";
$module_res = executeQuery($module_query, "i", [$course_id]);
$modules = [];
while ($row = $module_res->fetch_assoc()) {
    // Cek status penyelesaian tiap modul
    $progress_query = "SELECT is_completed FROM USER_MODULE_PROGRESS WHERE user_id = ? AND module_id = ?";
    $progress_res = executeQuery($progress_query, "ii", [$user_id, $row['module_id']]);
    $progress_data = $progress_res->fetch_assoc();
    
    $row['is_done'] = ($progress_data && $progress_data['is_completed'] == 1);
    $modules[] = $row;
}

// 3. Hitung Progres Keseluruhan
$total_modules = count($modules);
$completed_count = 0;
foreach ($modules as $m) {
    if ($m['is_done']) $completed_count++;
}

$perc = ($total_modules > 0) ? ($completed_count / $total_modules) * 100 : 0;

// 4. SINKRONISASI KE DATABASE (Tracking Progress)
$check_p = executeQuery("SELECT progress_id FROM USER_COURSE_PROGRESS WHERE user_id = ? AND course_id = ?", "ii", [$user_id, $course_id]);

if ($check_p->num_rows > 0) {
    $status_current = ($perc >= 100) ? 'completed' : 'in_progress';
    $update_p = "UPDATE USER_COURSE_PROGRESS SET progress_percentage = ?, status = ?, last_accessed = CURRENT_TIMESTAMP WHERE user_id = ? AND course_id = ?";
    executeUpdate($update_p, "dsii", [$perc, $status_current, $user_id, $course_id]);
} else {
    $insert_p = "INSERT INTO USER_COURSE_PROGRESS (user_id, course_id, status, progress_percentage, started_at) VALUES (?, ?, 'in_progress', ?, CURRENT_TIMESTAMP)";
    executeInsert($insert_p, "iid", [$user_id, $course_id, $perc]);
}

// 5. QUERY DAFTAR TEMAN (FRIENDS) - Sesuai Sidebar Dashboard
$friend_query = "SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);

// Cek apakah semua modul sudah selesai untuk membuka Quiz
$all_completed = ($total_modules > 0 && $completed_count >= $total_modules);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - <?= htmlspecialchars($course['course_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
        .module-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="flex">

    <aside class="w-64 h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide text-lg">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
            <a href="dashboard.php" class="sidebar-item"><span>📖</span> Lesson</a>
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

    <main class="flex-1 ml-64 p-10">
        <div class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-4">
                <a href="dashboard.php" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-blue-600 transition">←</a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($course['course_name']) ?></h1>
                    <p class="text-xs text-slate-400 mt-1 uppercase font-semibold tracking-wider">Materi Kursus • <?= $completed_count ?>/<?= $total_modules ?> Selesai</p>
                </div>
            </div>
            <a href="profil.php" class="flex items-center gap-4 group cursor-pointer">
                <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?= htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:shadow-md transition">
                    <span>👤</span>
                </div>
            </a>
        </div>

        <div class="w-full h-2 bg-gray-200 rounded-full mb-12 overflow-hidden">
            <div class="h-full bg-blue-500 transition-all duration-1000" style="width: <?= $perc ?>%"></div>
        </div>

        <?php if (empty($modules)): ?>
            <div class="bg-white rounded-[3rem] p-16 text-center shadow-sm border border-slate-100">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">📭</div>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Modul Belum Tersedia</h2>
                <p class="text-slate-400 text-xs max-w-xs mx-auto">Maaf, instruktur belum mengunggah materi untuk kursus ini. Silakan kembali lagi nanti.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($modules as $mod): ?>
                    <div class="module-card bg-white p-6 rounded-[2.5rem] border <?= $mod['is_done'] ? 'border-green-200' : 'border-slate-100' ?> shadow-sm transition-all duration-300 relative group">
                        
                        <?php if ($mod['is_done']): ?>
                            <div class="absolute top-6 right-6 w-6 h-6 bg-green-500 text-white rounded-full flex items-center justify-center text-[10px] shadow-lg shadow-green-100">✓</div>
                        <?php endif; ?>

                        <div class="flex justify-between items-start mb-6">
                            <div class="w-12 h-12 <?= $mod['is_done'] ? 'bg-green-50' : 'bg-blue-50' ?> rounded-2xl flex items-center justify-center text-blue-600 font-bold group-hover:bg-blue-600 group-hover:text-white transition-all">
                                <?= $mod['module_order'] ?>
                            </div>
                            <span class="text-[9px] font-bold text-slate-300 bg-slate-50 px-3 py-1 rounded-full uppercase italic tracking-tighter"><?= $mod['estimated_duration_minutes'] ?> Mins</span>
                        </div>

                        <h3 class="font-bold text-slate-800 mb-3 leading-tight"><?= htmlspecialchars($mod['module_name']) ?></h3>
                        <p class="text-[11px] text-slate-400 leading-relaxed line-clamp-2 mb-6"><?= htmlspecialchars($mod['module_description'] ?: 'Pelajari materi ini untuk menguasai kompetensi baru.') ?></p>
                        
                        <a href="learn.php?course_id=<?= $course_id ?>&module_id=<?= $mod['module_id'] ?>" 
                           class="flex items-center justify-center w-full py-3 <?= $mod['is_done'] ? 'bg-green-50 text-green-600' : 'bg-slate-50 text-slate-500' ?> group-hover:bg-blue-600 group-hover:text-white rounded-2xl text-[10px] font-bold transition-all uppercase tracking-widest">
                            <?= $mod['is_done'] ? 'Lihat Kembali' : 'Belajar Sekarang →' ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-20">
                <?php if ($all_completed): ?>
                    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-12 rounded-[3.5rem] text-center shadow-2xl shadow-blue-200 relative overflow-hidden">
                        <div class="absolute top-0 right-0 p-10 opacity-10 text-white text-8xl italic">✦</div>
                        <div class="relative z-10">
                            <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-6 text-4xl">🏆</div>
                            <h2 class="text-3xl font-bold text-white mb-3">Langkah Terakhir!</h2>
                            <p class="text-blue-100 mb-10 max-w-md mx-auto text-sm leading-relaxed">Selamat! Anda telah menyelesaikan seluruh modul. Silakan tinjau rangkuman materi sebelum memulai Quiz Akhir.</p>
                            <a href="rangkuman.php?course_id=<?= $course_id ?>" class="inline-block bg-white text-blue-600 px-12 py-4 rounded-3xl font-bold text-sm hover:bg-yellow-400 hover:text-white transition-all transform hover:scale-105 shadow-xl">
                                Buka Rangkuman & Quiz 🚀
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-slate-100 p-10 rounded-[3rem] text-center border-2 border-dashed border-slate-200">
                        <div class="text-3xl mb-4 opacity-50">🔒</div>
                        <h3 class="text-slate-400 font-bold text-xs uppercase tracking-widest">Akses Quiz Terkunci</h3>
                        <p class="text-slate-400 text-[10px] mt-1">Selesaikan semua modul untuk membuka ujian kompetensi (<?= $completed_count ?>/<?= $total_modules ?>)</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>