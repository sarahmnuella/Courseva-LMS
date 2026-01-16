<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// 1. Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// --- AMBIL DATA USER LENGKAP (Termasuk Foto) ---
$user_query = "SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id = ?";
$user_data = executeQuery($user_query, "i", [$user_id])->fetch_assoc();
$user_name = $user_data['nama_lengkap'] ?? 'User';
$user_photo = $user_data['fotoProfil'];

// 2. Query Statistik (Kartu di atas)
$query_stats = "SELECT 
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as finished,
    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as ongoing,
    COUNT(*) as total
    FROM USER_COURSE_PROGRESS WHERE user_id = ?";
$res_stats = executeQuery($query_stats, "i", [$user_id])->fetch_assoc();

// 3. Query Daftar Progress (Tabel)
$query_history = "SELECT c.course_id, c.course_name, c.level, 
                         p.status, p.progress_percentage, p.completed_at, p.last_accessed
                  FROM USER_COURSE_PROGRESS p
                  JOIN COURSES c ON p.course_id = c.course_id
                  WHERE p.user_id = ?
                  ORDER BY p.last_accessed DESC";
$result_history = executeQuery($query_history, "i", [$user_id]);

// 4. Query Daftar Teman (Friends)
$friend_query = "SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
    </style>
</head>
<body class="flex text-slate-700">

    <aside class="w-64 h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed left-0 top-0 z-50">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item sidebar-active"><span>🕒</span> History</a>
             <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>
            <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
            <div class="mt-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-3 px-2">
                    <?php while($friend = $friend_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden border border-gray-100">
                            <?php if(!empty($friend['fotoProfil'])): ?>
                                <img src="../assets/img/profiles/<?= htmlspecialchars($friend['fotoProfil']) ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="bg-green-100 w-full h-full flex items-center justify-center text-[10px]">👤</div>
                            <?php endif; ?>
                        </div>
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

    <main class="flex-1 ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="relative w-full max-w-2xl">
                <span class="absolute left-4 top-3.5 text-gray-300">🔍</span>
                <input type="text" placeholder="Search your history..." class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-100 shadow-sm focus:ring-1 focus:ring-blue-100 outline-none text-sm bg-white">
            </div>
            <a href="profil.php" class="flex items-center gap-4 group cursor-pointer">
                <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?= htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 rounded-xl overflow-hidden border border-gray-200 shadow-sm transition group-hover:shadow-md">
                    <?php if(!empty($user_photo)): ?>
                        <img src="../assets/img/profiles/<?= htmlspecialchars($user_photo) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-blue-100 flex items-center justify-center">
                            <span>👤</span>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600">🏆</div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase"><?= $res_stats['finished'] ?> Finished</p>
                    <p class="text-sm font-bold text-gray-800">Completed Courses</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600">⏳</div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase"><?= $res_stats['ongoing'] ?> On Going</p>
                    <p class="text-sm font-bold text-gray-800">Learning Progress</p>
                </div>
            </div>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-50 flex items-center gap-4">
                <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center text-green-600">📚</div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase"><?= $res_stats['total'] ?> Total</p>
                    <p class="text-sm font-bold text-gray-800">Enrolled Courses</p>
                </div>
            </div>
        </div>

        <h3 class="font-bold text-gray-800 text-lg mb-6 tracking-tight">Learning History</h3>

        <div class="bg-white rounded-[2.5rem] p-6 shadow-sm border border-gray-50 overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                        <th class="pb-6 px-4">Course Name & Last Access</th>
                        <th class="pb-6 px-4 text-center">Progress</th>
                        <th class="pb-6 px-4 text-center">Status</th>
                        <th class="pb-6 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php if ($result_history->num_rows == 0): ?>
                        <tr><td colspan="4" class="py-10 text-center text-gray-400">Belum ada riwayat belajar.</td></tr>
                    <?php else: ?>
                        <?php while ($c = $result_history->fetch_assoc()): 
                            $isDone = ($c['status'] == 'completed');
                        ?>
                        <tr class="group hover:bg-gray-50 transition-all border-b border-gray-50 last:border-0">
                            <td class="py-5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full <?= $isDone ? 'bg-green-400' : 'bg-blue-400' ?>"></div>
                                    <div>
                                        <p class="font-bold text-gray-700 leading-tight"><?= htmlspecialchars($c['course_name']) ?></p>
                                        <p class="text-[10px] text-gray-400 mt-1">Akses terakhir: <?= date('d/m/Y', strtotime($c['last_accessed'])) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-4 text-center">
                                <span class="text-xs font-bold text-gray-600"><?= round($c['progress_percentage']) ?>%</span>
                            </td>
                            <td class="py-5 px-4 text-center">
                                <span class="text-[9px] font-black px-3 py-1 rounded-lg italic <?= $isDone ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' ?>">
                                    <?= strtoupper($c['status']) ?>
                                </span>
                            </td>
                            <td class="py-5 px-4 text-right">
                                <?php if ($isDone): ?>
                                    <a href="lihat_sertif.php?course_id=<?= $c['course_id'] ?>" class="text-[10px] font-bold text-purple-500 border border-purple-100 px-4 py-2 rounded-xl hover:bg-purple-500 hover:text-white transition-all shadow-sm">LIHAT SERTIFIKAT</a>
                                <?php else: ?>
                                    <a href="lesson.php?id=<?= $c['course_id'] ?>" class="text-[10px] font-bold text-blue-400 border border-blue-100 px-4 py-2 rounded-xl hover:bg-blue-500 hover:text-white transition-all shadow-sm">LANJUTKAN</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>