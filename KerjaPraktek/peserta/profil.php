<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user lengkap dari database
$query = "SELECT nama_lengkap, id_karyawan, email, nomor_telepon, created_at FROM USERS WHERE user_id = ?";
$result = executeQuery($query, "i", [$user_id]);
$user = $result->fetch_assoc();

// Ambil statistik belajar user
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM USER_COURSE_PROGRESS WHERE user_id = ? AND status = 'completed') as completed,
    (SELECT COUNT(*) FROM USER_COURSE_PROGRESS WHERE user_id = ?) as total_enrolled";
$stats = executeQuery($stats_query, "ii", [$user_id, $user_id])->fetch_assoc();
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
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 min-h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed">
        <div class="flex items-center gap-2 mb-10">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg">B</div>
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
        </div>
        <nav class="space-y-2 flex-1">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
            <a href="lesson.php" class="sidebar-item"><span>📖</span> Lesson</a>
            <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 px-2">Account</p>
            <a href="profile.php" class="sidebar-item sidebar-active"><span>⚙️</span> Profil</a>
            <a href="../logout.php" class="sidebar-item text-red-400"><span>🚪</span> Keluar</a>
        </nav>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold text-gray-800 mb-8">Profil Pengguna</h1>

            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-blue-600 h-32 relative">
                    <div class="absolute -bottom-12 left-10">
                        <div class="w-24 h-24 bg-white rounded-3xl p-1 shadow-lg">
                            <div class="w-full h-full bg-blue-100 rounded-[1.25rem] flex items-center justify-center text-3xl">👤</div>
                        </div>
                    </div>
                </div>

                <div class="pt-16 p-10">
                    <div class="flex justify-between items-start mb-10">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($user['nama_lengkap']) ?></h2>
                            <p class="text-gray-400 text-sm">ID Karyawan: <?= htmlspecialchars($user['id_karyawan']) ?></p>
                        </div>
                        <button class="px-6 py-2 border border-blue-500 text-blue-500 rounded-full text-xs font-bold hover:bg-blue-50 transition">Edit Profil</button>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Informasi Kontak</h3>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Email Address</p>
                                <p class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($user['email']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Nomor Telepon</p>
                                <p class="text-sm text-gray-700 font-medium"><?= htmlspecialchars($user['nomor_telepon']) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold mb-1">Bergabung Sejak</p>
                                <p class="text-sm text-gray-700 font-medium"><?= date('d F Y', strtotime($user['created_at'])) ?></p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Aktivitas Belajar</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-50 p-4 rounded-2xl">
                                    <p class="text-2xl font-bold text-blue-600"><?= $stats['total_enrolled'] ?></p>
                                    <p class="text-[10px] text-blue-400 font-bold uppercase">Kursus Diambil</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-2xl">
                                    <p class="text-2xl font-bold text-green-600"><?= $stats['completed'] ?></p>
                                    <p class="text-[10px] text-green-400 font-bold uppercase">Kursus Selesai</p>
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