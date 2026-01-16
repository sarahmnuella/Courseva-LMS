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
    $progress_query = "SELECT is_completed FROM USER_MODULE_PROGRESS WHERE user_id = ? AND module_id = ?";
    $progress_res = executeQuery($progress_query, "ii", [$user_id, $row['module_id']]);
    $progress_data = $progress_res->fetch_assoc();
    
    $row['is_done'] = ($progress_data && $progress_data['is_completed'] == 1);
    $modules[] = $row;
}

$total_modules = count($modules);
$completed_count = 0;
foreach ($modules as $m) {
    if ($m['is_done']) $completed_count++;
}

$perc = ($total_modules > 0) ? ($completed_count / $total_modules) * 100 : 0;

// 3. Ambil data user lengkap (untuk foto profil)
$user_data = executeQuery("SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id = ?", "i", [$user_id])->fetch_assoc();
$user_name = $user_data['nama_lengkap'];
$user_photo = $user_data['fotoProfil'];

// 4. Query Daftar Teman
$friend_result = executeQuery("SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id != ? LIMIT 3", "i", [$user_id]);

$all_completed = ($total_modules > 0 && $completed_count >= $total_modules);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - <?= htmlspecialchars($course['course_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 10px; transition: 0.2s; color: #64748b; font-size: 14px; font-weight: 500; }
        .sidebar-item:hover { background-color: #f1f5f9; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #2563eb; font-weight: 600; }
        .module-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: 1px solid #e2e8f0; }
        .module-card:hover { transform: translateY(-4px); box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.05); border-color: #3b82f6; }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 h-screen bg-white p-6 border-r border-slate-200 flex flex-col fixed left-0 top-0 z-50">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-9 h-9 object-contain" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-slate-800 tracking-tight text-xl">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Main Menu</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
 <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>
    <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>            
            
            <div class="mt-8">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Study Group</p>
                <div class="space-y-3 px-2">
                    <?php while($friend = $friend_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full bg-slate-100 overflow-hidden border border-slate-200">
                            <?php if($friend['fotoProfil']): ?>
                                <img src="../assets/img/profiles/<?= $friend['fotoProfil'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-[10px]">👤</div>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs text-slate-600 font-medium truncate"><?= htmlspecialchars($friend['nama_lengkap']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </nav>

        <div class="mt-auto pt-6 border-t border-slate-100">
            <a href="profile.php" class="sidebar-item mb-1"><span>⚙️</span> Profil</a>
            <a href="../logout.php" class="sidebar-item text-red-500 hover:bg-red-50"><span>🚪</span> Keluar</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8 lg:p-12">
        <div class="flex items-center justify-between mb-10">
            <div class="flex items-center gap-5">
                <a href="dashboard.php" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-600 transition-all">←</a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><?= htmlspecialchars($course['course_name']) ?></h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-bold rounded uppercase tracking-wider"><?= htmlspecialchars($course['level']) ?></span>
                        <span class="text-xs text-slate-400 font-medium">Progress: <?= $completed_count ?> dari <?= $total_modules ?> Modul Selesai</span>
                    </div>
                </div>
            </div>
            
            <a href="profile.php" class="flex items-center gap-3 bg-white pr-4 pl-1.5 py-1.5 rounded-full border border-slate-200 hover:shadow-sm transition-all">
                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100 border border-slate-200">
                    <?php if($user_photo): ?>
                        <img src="../assets/img/profiles/<?= $user_photo ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-sm">👤</div>
                    <?php endif; ?>
                </div>
                <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($user_name) ?></span>
            </a>
        </div>

        <div class="mb-10">
            <div class="flex justify-between items-end mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Completion Progress</span>
                <span class="text-sm font-bold text-blue-600"><?= round($perc) ?>%</span>
            </div>
            <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                <div class="h-full bg-blue-600 transition-all duration-1000" style="width: <?= $perc ?>%"></div>
            </div>
        </div>

        <?php if ($all_completed): ?>
            <div class="bg-white border-2 border-blue-500/20 p-6 rounded-2xl mb-12 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-6">
                    <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center text-3xl">🏆</div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Kurikulum Selesai!</h2>
                        <p class="text-sm text-slate-500">Anda telah menguasai seluruh materi. Siap untuk sertifikasi?</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="rangkuman.php?course_id=<?= $course_id ?>" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl font-bold text-sm hover:bg-blue-700 transition-all shadow-md shadow-blue-200">
                        Buka Quiz & Sertifikat →
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($modules)): ?>
            <div class="bg-white rounded-3xl p-16 text-center border border-slate-200">
                <div class="text-4xl mb-4">📂</div>
                <h2 class="text-xl font-bold text-slate-800 mb-1">Modul Belum Tersedia</h2>
                <p class="text-slate-400 text-sm">Materi sedang disiapkan oleh instruktur.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($modules as $mod): ?>
                    <div class="module-card bg-white p-6 rounded-2xl relative overflow-hidden">
                        <?php if ($mod['is_done']): ?>
                            <div class="absolute top-0 right-0 p-2">
                                <span class="bg-green-500 text-white p-1 rounded-bl-xl rounded-tr-xl block shadow-sm">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-10 h-10 <?= $mod['is_done'] ? 'bg-green-50 text-green-600' : 'bg-blue-50 text-blue-600' ?> rounded-lg flex items-center justify-center font-bold text-sm">
                                <?= str_pad($mod['module_order'], 2, '0', STR_PAD_LEFT) ?>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest"><?= $mod['estimated_duration_minutes'] ?> Menit Baca</span>
                        </div>

                        <h3 class="font-bold text-slate-800 mb-2 leading-snug h-12 overflow-hidden"><?= htmlspecialchars($mod['module_name']) ?></h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-6 line-clamp-2 italic">
                            <?= htmlspecialchars($mod['module_description'] ?: 'Pelajari kompetensi utama dalam bagian ini.') ?>
                        </p>
                        
                        <a href="learn.php?course_id=<?= $course_id ?>&module_id=<?= $mod['module_id'] ?>" 
                           class="flex items-center justify-center w-full py-2.5 rounded-xl text-xs font-bold transition-all <?= $mod['is_done'] ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-blue-600 text-white hover:bg-blue-700' ?>">
                            <?= $mod['is_done'] ? 'Review Materi' : 'Mulai Belajar' ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$all_completed): ?>
            <div class="mt-10 p-5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center gap-4 opacity-75">
                <span class="text-xl">🔒</span>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest">Quiz Akhir Terkunci hingga seluruh modul selesai</p>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

</body>
</html>