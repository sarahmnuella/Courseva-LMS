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

// 1. Ambil Data User untuk Header & Sidebar
$user_data = executeQuery("SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id = ?", "i", [$user_id])->fetch_assoc();
$user_name = $user_data['nama_lengkap'];
$user_photo = $user_data['fotoProfil'];

// 2. Logika Search & Filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$level_filter = isset($_GET['level']) ? $_GET['level'] : '';

$query_str = "SELECT * FROM COURSES WHERE is_published = 1";
$params = [];
$types = "";

if (!empty($search)) {
    $query_str .= " AND (course_name LIKE ? OR course_description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($level_filter)) {
    $query_str .= " AND level = ?";
    $params[] = $level_filter;
    $types .= "s";
}

$query_str .= " ORDER BY created_at DESC";
$courses_res = executeQuery($query_str, $types, $params);

// 3. Query Daftar Teman Sidebar
$friend_result = executeQuery("SELECT nama_lengkap, fotoProfil FROM USERS WHERE user_id != ? LIMIT 3", "i", [$user_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses - Courseva</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
        .course-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-64 h-screen bg-white p-6 border-r border-slate-200 flex flex-col fixed left-0 top-0 z-50">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-9 h-9 object-contain" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-slate-800 tracking-tight text-xl">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Menu</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
            <a href="courses.php" class="sidebar-item sidebar-active"><span>📖</span> Lesson</a>
               <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
            <div class="mt-8">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-3 px-2">
                    <?php while($f = $friend_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full overflow-hidden bg-slate-100">
                            <?php if($f['fotoProfil']): ?>
                                <img src="../assets/img/profiles/<?= $f['fotoProfil'] ?>" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center text-[10px]">👤</div>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs text-slate-600 font-medium truncate"><?= htmlspecialchars($f['nama_lengkap']) ?></span>
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
            <div>
                <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Eksplorasi Kursus</h1>
                <p class="text-sm text-slate-500 mt-1">Temukan materi baru untuk meningkatkan kompetensi Anda.</p>
            </div>
            
            <a href="profile.php" class="flex items-center gap-3 bg-white pr-4 pl-1.5 py-1.5 rounded-full border border-slate-200 hover:shadow-sm transition-all">
                <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-100">
                    <?php if($user_photo): ?>
                        <img src="../assets/img/profiles/<?= $user_photo ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-sm">👤</div>
                    <?php endif; ?>
                </div>
                <span class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($user_name) ?></span>
            </a>
        </div>

        <div class="flex flex-col md:flex-row gap-4 mb-10">
            <form action="" method="GET" class="flex-1 relative">
                <span class="absolute left-4 top-3.5 text-slate-400">🔍</span>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul atau topik kursus..." class="w-full pl-12 pr-4 py-3 rounded-2xl bg-white border border-slate-200 focus:ring-2 focus:ring-blue-100 outline-none text-sm transition-all">
            </form>
            <div class="flex gap-2">
                <a href="courses.php" class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition-all">Semua</a>
                <a href="?level=beginner" class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-600 hover:bg-green-50 hover:text-green-600 transition-all">Pemula</a>
                <a href="?level=intermediate" class="px-5 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-bold text-slate-600 hover:bg-blue-50 hover:text-blue-600 transition-all">Menengah</a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($courses_res->num_rows > 0): ?>
                <?php while ($c = $courses_res->fetch_assoc()): ?>
                <div class="course-card bg-white rounded-[2.5rem] p-5 shadow-sm border border-slate-100 transition-all duration-300 group">
                    <div class="h-44 bg-slate-100 rounded-[2rem] mb-6 overflow-hidden relative">
                        <?php if ($c['thumbnail_url']): ?>
                            <img src="<?= htmlspecialchars($c['thumbnail_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-300 text-4xl font-bold italic">COURSEVA</div>
                        <?php endif; ?>
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1.5 bg-white/90 backdrop-blur-sm text-[10px] font-black text-blue-600 rounded-full uppercase tracking-widest shadow-sm border border-slate-100"><?= htmlspecialchars($c['level']) ?></span>
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-slate-800 mb-3 px-1 leading-tight h-12 line-clamp-2"><?= htmlspecialchars($c['course_name']) ?></h3>
                    <p class="text-xs text-slate-400 px-1 mb-6 line-clamp-2 leading-relaxed italic"><?= htmlspecialchars($c['course_description']) ?></p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                        <div class="flex items-center gap-1.5">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter italic">ID: #<?= $c['course_id'] ?></span>
                        </div>
                        <a href="lesson.php?id=<?= $c['course_id'] ?>" class="px-6 py-2.5 bg-blue-600 text-white text-[10px] font-bold rounded-xl uppercase tracking-widest hover:bg-blue-700 transition-all shadow-md shadow-blue-100">Lihat Detail</a>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full py-20 text-center">
                    <div class="text-5xl mb-4">📂</div>
                    <h3 class="text-lg font-bold text-slate-800">Tidak ada kursus ditemukan</h3>
                    <p class="text-sm text-slate-400">Coba gunakan kata kunci lain atau pilih level yang berbeda.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>