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
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// --- LOGIKA TANDAI SELESAI (SINKRON KE DATABASE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mark_as_completed'])) {
    // 1. Update/Insert ke USER_MODULE_PROGRESS
    $query_check = "SELECT * FROM USER_MODULE_PROGRESS WHERE user_id = ? AND module_id = ?";
    $res_check = executeQuery($query_check, "ii", [$user_id, $module_id]);

    if ($res_check->num_rows > 0) {
        executeUpdate("UPDATE USER_MODULE_PROGRESS SET is_completed = TRUE, completed_at = NOW() WHERE user_id = ? AND module_id = ?", "ii", [$user_id, $module_id]);
    } else {
        executeInsert("INSERT INTO USER_MODULE_PROGRESS (user_id, module_id, is_completed, completed_at) VALUES (?, ?, TRUE, NOW())", "ii", [$user_id, $module_id]);
    }

    // 2. Update Persentase di USER_COURSE_PROGRESS
    $total_mod = executeQuery("SELECT COUNT(*) as total FROM MODULES WHERE course_id = ?", "i", [$course_id])->fetch_assoc()['total'];
    $done_mod = executeQuery("SELECT COUNT(*) as done FROM USER_MODULE_PROGRESS ump JOIN MODULES m ON ump.module_id = m.module_id WHERE ump.user_id = ? AND m.course_id = ? AND ump.is_completed = 1", "ii", [$user_id, $course_id])->fetch_assoc()['done'];

    $percentage = ($done_mod / $total_mod) * 100;
    $status = ($percentage >= 100) ? 'completed' : 'in_progress';

    executeUpdate("UPDATE USER_COURSE_PROGRESS SET progress_percentage = ?, status = ? WHERE user_id = ? AND course_id = ?", "dsii", [$percentage, $status, $user_id, $course_id]);

    header("Location: learn.php?course_id=$course_id&module_id=$module_id&success=1");
    exit();
}

// --- AMBIL DATA DARI DATABASE ---
// 1. Data Modul
$module_data = executeQuery("SELECT m.*, c.course_name FROM MODULES m JOIN COURSES c ON m.course_id = c.course_id WHERE m.module_id = ?", "i", [$module_id])->fetch_assoc();

// 2. Isi Konten Modul
$content_res = executeQuery("SELECT * FROM MODULE_CONTENT WHERE module_id = ? ORDER BY content_order ASC", "i", [$module_id]);

// 3. Status Progress Modul
$is_done = executeQuery("SELECT is_completed FROM USER_MODULE_PROGRESS WHERE user_id = ? AND module_id = ?", "ii", [$user_id, $module_id])->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belajar: <?= htmlspecialchars($module_data['module_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 font-['Inter']">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="lesson.php?id=<?= $course_id ?>" class="text-gray-400 hover:text-blue-600 transition">← Kembali</a>
            <div>
                <h1 class="text-sm font-bold text-gray-800"><?= htmlspecialchars($module_data['module_name']) ?></h1>
                <p class="text-[10px] text-gray-400 uppercase"><?= htmlspecialchars($module_data['course_name']) ?></p>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-10 px-6">
        <div class="space-y-10">
            <?php if ($content_res->num_rows > 0): ?>
                <?php while ($content = $content_res->fetch_assoc()): ?>
                    <section class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 transition-all hover:shadow-md">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-blue-500 rounded-full"></span>
                            <?= htmlspecialchars($content['content_title']) ?>
                        </h2>

                        <?php if ($content['content_type'] == 'video'): ?>
                            <div class="aspect-video bg-black rounded-[2rem] overflow-hidden mb-8 shadow-lg">
                                <iframe class="w-full h-full" src="<?= $content['content_url'] ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>

                        <div class="prose prose-slate max-w-none text-gray-600 leading-relaxed text-justify">
                            <?= nl2br($content['content_body']) ?>
                        </div>

                        <?php if ($content['content_type'] == 'pdf'): ?>
                            <div class="mt-8 p-4 bg-blue-50 rounded-2xl flex items-center justify-between">
                                <p class="text-xs font-bold text-blue-700">📄 DOKUMEN MATERI TERSEDIA</p>
                                <a href="<?= $content['content_url'] ?>" target="_blank" class="text-xs font-bold bg-white text-blue-600 px-4 py-2 rounded-xl shadow-sm hover:bg-blue-100 transition">Buka PDF</a>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-20 bg-white rounded-[2.5rem] border border-dashed border-gray-200">
                    <p class="text-gray-400 font-medium">Isi modul belum diunggah oleh instruktur.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-16 pt-10 border-t border-gray-200 flex flex-col items-center">
            <?php if (isset($is_done['is_completed']) && $is_done['is_completed'] == 1): ?>
                <div class="flex items-center gap-3 text-green-600 font-bold bg-green-50 px-8 py-4 rounded-3xl shadow-sm">
                    <span class="text-2xl">✔</span> 
                    <span>Anda telah menyelesaikan modul ini</span>
                </div>
                <a href="lesson.php?id=<?= $course_id ?>" class="mt-4 text-blue-600 text-xs font-bold hover:underline">Lanjut Pilih Modul Lain</a>
            <?php else: ?>
                <form method="POST">
                    <button type="submit" name="mark_as_completed" class="bg-green-500 text-white px-12 py-4 rounded-3xl font-bold text-sm shadow-xl shadow-green-100 hover:bg-green-600 transition-all transform hover:scale-105 active:scale-95">
                        ✅ Selesaikan Modul & Simpan Progres
                    </button>
                </form>
                <p class="text-[10px] text-gray-400 mt-4 uppercase tracking-widest">Klik untuk mencatat kemajuan belajar Anda</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>