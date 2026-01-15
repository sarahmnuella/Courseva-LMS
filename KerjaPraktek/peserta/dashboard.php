<?php
// Gunakan ../ untuk keluar dari folder 'peserta'
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Jika belum login, arahkan balik ke login.php yang ada di root (luar folder peserta)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['nama_lengkap'] ?? 'User';

// ... kode query database tetap sama ...

// Sekaligus mengambil progress user jika mereka sudah mulai belajar
$query = "SELECT c.course_id, c.course_name, c.level, c.thumbnail_url, 
                 IFNULL(p.progress_percentage, 0) as progress_percentage, 
                 IFNULL(p.status, 'not_started') as status
          FROM COURSES c
          LEFT JOIN USER_COURSE_PROGRESS p ON c.course_id = p.course_id AND p.user_id = ?
          WHERE c.is_published = TRUE
          LIMIT 6";

$result = executeQuery($query, "i", [$user_id]);
$allCourses = [];
while ($row = $result->fetch_assoc()) {
    $allCourses[] = $row;
}
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
            <a href="dashboard.php" class="sidebar-item sidebar-active"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
            <a href="lesson.php" class="sidebar-item"><span>📖</span> Lesson</a>
            <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-8 mb-4 px-2">Account</p>
            <a href="profil.php" class="sidebar-item"><span>⚙️</span> Profil</a>
            <a href="logout.php" class="sidebar-item text-red-400"><span>🚪</span> Keluar</a>
        </nav>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="relative w-full max-w-2xl">
                <span class="absolute left-4 top-3 text-gray-400">🔍</span>
                <input type="text" placeholder="Search your courses here..." class="w-full pl-12 pr-4 py-3 rounded-2xl shadow-sm focus:ring-1 focus:ring-blue-300 outline-none text-sm bg-white">
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-600">Halo, <?php echo htmlspecialchars($user_name); ?>!</span>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center cursor-pointer">
                    <span>👤</span>
                </div>
            </div>
        </div>

        <div class="bg-blue-600 rounded-[2rem] p-10 text-white relative overflow-hidden mb-8 shadow-xl shadow-blue-100">
            <div class="relative z-10 max-w-md">
                <p class="text-[10px] font-medium opacity-80 mb-2 uppercase tracking-widest">Online Course</p>
                <h1 class="text-3xl font-bold leading-tight mb-6">Sharpen Your Skills With Professional Online Courses</h1>
                <button class="bg-black text-white px-6 py-2 rounded-full text-xs font-semibold flex items-center gap-2 hover:bg-gray-800 transition">Join Now <span class="bg-white text-black rounded-full w-4 h-4 text-[10px] flex items-center justify-center">→</span></button>
            </div>
            <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-6xl italic">✦ ✦</div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-800 text-lg">Continue Learning</h3>
            <a href="lesson.php" class="text-blue-500 text-sm font-semibold">View All</a>
        </div>

<div class="grid grid-cols-3 gap-6">
    <?php if (empty($allCourses)): ?>
        <div class="col-span-3 bg-white p-10 rounded-3xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-center">
            <div class="text-4xl mb-4">📚</div>
            <h4 class="text-gray-800 font-bold">Tidak ada kursus tersedia</h4>
            <p class="text-gray-500 text-sm mt-2">Maaf, saat ini belum ada kursus yang dipublikasikan.</p>
        </div>
    <?php else: ?>
        <?php foreach ($allCourses as $course): ?>
        <div class="bg-white p-4 rounded-3xl shadow-sm hover:shadow-md transition border border-gray-50">
            <div class="h-32 bg-gray-100 rounded-2xl mb-4 overflow-hidden">
                <img src="<?php echo !empty($course['thumbnail_url']) ? $course['thumbnail_url'] : 'https://via.placeholder.com/300x150'; ?>" class="w-full h-full object-cover">
            </div>
            
            <span class="bg-blue-100 text-blue-600 text-[10px] px-3 py-1 rounded-full font-bold uppercase">
                <?php echo htmlspecialchars($course['level']); ?>
            </span>
            
            <h4 class="mt-3 text-sm font-bold text-gray-800 line-clamp-2 h-10">
                <?php echo htmlspecialchars($course['course_name']); ?>
            </h4>
            
            <div class="mt-4 flex items-center justify-between">
                <span class="text-[10px] font-bold text-gray-400">Progress</span>
                <span class="text-[10px] font-bold text-blue-600"><?php echo round($course['progress_percentage']); ?>%</span>
            </div>
            
            <div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 rounded-full" style="width: <?php echo $course['progress_percentage']; ?>%"></div>
            </div>

            <a href="lesson.php?id=<?php echo $course['course_id']; ?>" 
               class="mt-4 block text-center py-2 <?php echo ($course['progress_percentage'] > 0) ? 'bg-blue-600 text-white' : 'bg-gray-50 text-gray-700'; ?> text-[11px] font-bold rounded-xl hover:opacity-90 transition">
                <?php echo ($course['progress_percentage'] > 0) ? 'Lanjutkan Belajar' : 'Mulai Belajar'; ?>
            </a>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

    </main>
</body>
</html>