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

// 1. Ambil data user lengkap termasuk fotoProfil dari database
$query = "SELECT nama_lengkap, id_karyawan, email, nomor_telepon, created_at, fotoProfil FROM USERS WHERE user_id = ?";
$result = executeQuery($query, "i", [$user_id]);
$user = $result->fetch_assoc();

// 2. Ambil statistik belajar user
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM USER_COURSE_PROGRESS WHERE user_id = ? AND status = 'completed') as completed,
    (SELECT COUNT(*) FROM USER_COURSE_PROGRESS WHERE user_id = ?) as total_enrolled";
$stats = executeQuery($stats_query, "ii", [$user_id, $user_id])->fetch_assoc();

// 3. Query Daftar Teman (Friends) untuk Sidebar
$friend_query = "SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Courseva</title>
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
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
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
            <a href="profile.php" class="sidebar-item sidebar-active"><span>⚙️</span> Profil</a>
            <a href="../logout.php" class="sidebar-item text-red-500 hover:bg-red-50"><span>🚪</span> Keluar</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="flex-1"></div>
            <a href="profile.php" class="flex items-center gap-4 group cursor-pointer">
                <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?= htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:shadow-md transition overflow-hidden">
                    <?php if (!empty($user['fotoProfil'])): ?>
                        <img src="../assets/img/profiles/<?= htmlspecialchars($user['fotoProfil']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <span>👤</span>
                    <?php endif; ?>
                </div>
            </a>
        </div>

        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-slate-800 mb-8">Profil Pengguna</h1>

            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-blue-600 h-40 relative">
                    <div class="absolute -bottom-12 left-10">
                        <div class="w-28 h-28 bg-white rounded-[2rem] p-1.5 shadow-xl">
                            <div class="w-full h-full bg-slate-50 rounded-[1.75rem] overflow-hidden flex items-center justify-center border border-slate-100">
                                <?php if (!empty($user['fotoProfil'])): ?>
                                    <img src="../assets/img/profiles/<?= htmlspecialchars($user['fotoProfil']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <span class="text-4xl">👤</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-20 p-12">
                    <div class="flex justify-between items-start mb-12">
                        <div>
                            <h2 class="text-2xl font-black text-slate-800"><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                            <p class="text-slate-400 text-sm mt-1 font-medium">ID Karyawan: <span class="text-slate-600 font-bold"><?= htmlspecialchars($user['id_karyawan']) ?></span></p>
                        </div>
                        <a href="edit_profil.php" class="px-8 py-3 bg-blue-50 text-blue-600 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white transition-all shadow-sm flex items-center gap-2">
                            <span>✏️</span> Edit Profil
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                        <div class="space-y-8">
                            <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-2 h-2 bg-blue-500 rounded-full"></span> Informasi Kontak
                            </h3>
                            <div class="space-y-6">
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-black mb-1.5 tracking-wider">Email Address</p>
                                    <p class="text-sm text-slate-700 font-bold"><?= htmlspecialchars($user['email']) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-black mb-1.5 tracking-wider">Nomor Telepon</p>
                                    <p class="text-sm text-slate-700 font-bold"><?= htmlspecialchars($user['nomor_telepon']) ?></p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-400 uppercase font-black mb-1.5 tracking-wider">Bergabung Sejak</p>
                                    <p class="text-sm text-slate-700 font-bold"><?= date('d F Y', strtotime($user['created_at'])) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <h3 class="text-[10px] font-black text-slate-300 uppercase tracking-[0.2em] flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full"></span> Aktivitas Belajar
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-6 rounded-[2rem] border border-blue-100/50 shadow-sm">
                                    <p class="text-3xl font-black text-blue-600 mb-1"><?= $stats['total_enrolled'] ?></p>
                                    <p class="text-[9px] text-blue-400 font-black uppercase tracking-widest">Kursus Diambil</p>
                                </div>
                                <div class="bg-green-50 p-6 rounded-[2rem] border border-green-100/50 shadow-sm">
                                    <p class="text-3xl font-black text-green-600 mb-1"><?= $stats['completed'] ?></p>
                                    <p class="text-[9px] text-green-400 font-black uppercase tracking-widest">Kursus Selesai</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>