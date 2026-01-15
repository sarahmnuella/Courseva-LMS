<?php
// Gunakan ../ untuk keluar dari folder 'peserta'
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Jika belum login, arahkan balik ke login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama_lengkap'] ?? 'User';

// --- LOGIKA SEARCH ---
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = "";
$params = [$user_id];
$types = "i";

if (!empty($search)) {
    $search_query = " AND c.course_name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}

// Query mengambil daftar kursus
$query = "SELECT c.course_id, c.course_name, c.level, c.thumbnail_url, 
                 IFNULL(p.progress_percentage, 0) as progress_percentage, 
                 IFNULL(p.status, 'not_started') as status
          FROM COURSES c
          LEFT JOIN USER_COURSE_PROGRESS p ON c.course_id = p.course_id AND p.user_id = ?
          WHERE c.is_published = TRUE" . $search_query . " LIMIT 6";

$result = executeQuery($query, $types, $params);
$allCourses = [];
while ($row = $result->fetch_assoc()) {
    $allCourses[] = $row;
}

// --- QUERY DAFTAR TEMAN (FRIENDS) ---
// Mengambil data user lain dari database
$friend_query = "SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3";
$friend_result = executeQuery($friend_query, "i", [$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed left-0 top-0">
        <div class="flex items-center gap-3 mb-10">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item sidebar-active"><span>🏠</span> Dashboard</a>
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

    <main class="flex-1 ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="relative w-full max-w-2xl">
                <form action="dashboard.php" method="GET" class="relative">
                    <span class="absolute left-4 top-3.5 text-gray-400">🔍</span>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search your courses here..." class="w-full pl-12 pr-20 py-3.5 rounded-2xl shadow-sm focus:ring-2 focus:ring-blue-100 outline-none text-sm bg-white transition-all">
                    <?php if(!empty($search)): ?>
                        <a href="dashboard.php" class="absolute right-16 top-3.5 text-xs text-gray-400 hover:text-red-500">Clear</a>
                    <?php endif; ?>
                    <button type="submit" class="absolute right-4 top-2 bg-blue-600 text-white px-4 py-1.5 rounded-xl text-[10px] font-bold mt-1">Go</button>
                </form>
            </div>
            
            <a href="profil.php" class="flex items-center gap-4 group cursor-pointer">
                <span class="text-sm font-medium text-gray-600 group-hover:text-blue-600 transition">Halo, <?php echo htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center group-hover:shadow-md transition">
                    <span>👤</span>
                </div>
            </a>
        </div>

        <div class="bg-blue-600 rounded-[2.5rem] p-12 text-white relative overflow-hidden mb-12 shadow-2xl shadow-blue-100 border border-blue-500">
            <div class="relative z-10 max-w-lg">
                <p class="text-[10px] font-bold opacity-70 mb-3 uppercase tracking-[0.2em]">Online Learning Platform</p>
                <h1 class="text-4xl font-extrabold leading-tight mb-8">Sharpen Your Skills With Professional Online Courses</h1>
                <button class="bg-white text-blue-600 px-8 py-3 rounded-2xl text-xs font-bold flex items-center gap-3 hover:bg-yellow-400 hover:text-white transition-all transform hover:scale-105">
                    Explore Now <span class="bg-blue-600 text-white rounded-full w-5 h-5 text-[10px] flex items-center justify-center">→</span>
                </button>
            </div>
            <div class="absolute right-0 top-0 h-full w-1/3 opacity-10 flex items-center justify-center text-9xl italic font-black">✦</div>
        </div>

        <div class="flex justify-between items-center mb-8">
            <h3 class="font-bold text-gray-800 text-xl tracking-tight">Continue Learning</h3>
            <a href="lesson.php" class="text-blue-600 text-xs font-bold uppercase tracking-wider hover:underline">View All Courses</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if (empty($allCourses)): ?>
                <div class="col-span-3 bg-white p-20 rounded-[3rem] border-2 border-dashed border-gray-100 flex flex-col items-center justify-center text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center text-4xl mb-6">🔭</div>
                    <h4 class="text-gray-800 font-bold text-lg">Oops! Kursus Tidak Ditemukan</h4>
                    <p class="text-gray-400 text-sm mt-2 max-w-xs">Kami tidak dapat menemukan kursus dengan kata kunci "<?= htmlspecialchars($search) ?>".</p>
                    <a href="dashboard.php" class="mt-6 text-blue-600 text-xs font-bold hover:underline">Reset Pencarian</a>
                </div>
            <?php else: ?>
                <?php foreach ($allCourses as $course): ?>
                <div class="bg-white p-5 rounded-[2.5rem] shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-50 group">
                    <div class="h-40 bg-gray-50 rounded-[2rem] mb-6 overflow-hidden relative">
                        <img src="<?php echo !empty($course['thumbnail_url']) ? $course['thumbnail_url'] : 'https://via.placeholder.com/400x200'; ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/90 backdrop-blur-md text-blue-600 text-[9px] px-3 py-1.5 rounded-full font-black uppercase tracking-widest shadow-sm">
                                <?php echo htmlspecialchars($course['level']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <h4 class="text-base font-bold text-gray-800 line-clamp-2 mb-6 h-12 leading-snug">
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </h4>
                    
                    <div class="mt-auto">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Progress</span>
                            <span class="text-[10px] font-bold text-blue-600"><?php echo round($course['progress_percentage']); ?>%</span>
                        </div>
                        
                        <div class="h-2 bg-gray-50 rounded-full overflow-hidden mb-6">
                            <div class="h-full bg-blue-500 rounded-full transition-all duration-1000" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
                        </div>

                        <a href="lesson.php?id=<?php echo $course['course_id']; ?>" 
                           class="block text-center py-3.5 <?php echo ($course['progress_percentage'] > 0) ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-slate-50 text-slate-500'; ?> text-[10px] font-black rounded-2xl hover:opacity-90 transition-all uppercase tracking-[0.1em]">
                            <?php echo ($course['progress_percentage'] > 0) ? 'Lanjutkan Belajar' : 'Mulai Belajar'; ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>