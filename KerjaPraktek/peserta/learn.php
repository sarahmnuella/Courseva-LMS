<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

// Ambil ID dari URL (Contoh: learn.php?course_id=1&module_id=5)
$course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$module_id = isset($_GET['module_id']) ? (int)$_GET['module_id'] : 0;

// 1. Ambil Data Modul & Course untuk Header
$query_mod = "SELECT m.*, c.course_name 
              FROM MODULES m 
              JOIN COURSES c ON m.course_id = c.course_id 
              WHERE m.module_id = ? AND m.course_id = ?";
$mod_res = executeQuery($query_mod, "ii", [$module_id, $course_id]);
$module_data = $mod_res->fetch_assoc();

// Jika data tidak ditemukan
if (!$module_data) {
    die("<div style='text-align:center; padding:50px;'><h2>Konten Modul Tidak Ditemukan.</h2><a href='dashboard.php'>Kembali</a></div>");
}

// 2. Ambil Isi Konten dari Database (Tabel MODULE_CONTENT)
$query_content = "SELECT * FROM MODULE_CONTENT WHERE module_id = ? ORDER BY content_order ASC";
$content_res = executeQuery($query_content, "i", [$module_id]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belajar: <?= htmlspecialchars($module_data['module_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .content-area::-webkit-scrollbar { width: 6px; }
        .content-area::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 px-8 py-4 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="lesson.php?id=<?= $course_id ?>" class="text-gray-400 hover:text-blue-600 transition">← Kembali</a>
            <div class="h-6 w-[1px] bg-gray-200"></div>
            <div>
                <h1 class="text-sm font-bold text-gray-800"><?= htmlspecialchars($module_data['module_name']) ?></h1>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($module_data['course_name']) ?></p>
            </div>
        </div>
        <a href="Quiz.php?course_id=<?= $course_id ?>" class="bg-blue-600 text-white px-6 py-2 rounded-full text-xs font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-100">Ambil Quiz</a>
    </nav>

    <div class="max-w-5xl mx-auto py-10 px-6">
        <?php if ($content_res->num_rows > 0): ?>
            <div class="space-y-12">
                <?php while ($content = $content_res->fetch_assoc()): ?>
                    <section class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                            <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                            <?= htmlspecialchars($content['content_title']) ?>
                        </h2>

                        <?php if ($content['content_type'] == 'video' && !empty($content['content_url'])): ?>
                            <div class="aspect-video bg-black rounded-[2rem] overflow-hidden mb-8 shadow-2xl">
                                <iframe class="w-full h-full" src="<?= $content['content_url'] ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php endif; ?>

                        <div class="prose prose-blue max-w-none text-gray-600 leading-relaxed text-justify">
                            <?= nl2br($content['content_body']) ?>
                        </div>

                        <?php if ($content['content_type'] == 'pdf' && !empty($content['content_url'])): ?>
                            <div class="mt-8 p-4 bg-blue-50 rounded-2xl flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📄</span>
                                    <p class="text-xs font-bold text-blue-700 uppercase">Materi PDF Tersedia</p>
                                </div>
                                <a href="<?= $content['content_url'] ?>" target="_blank" class="text-xs font-bold bg-white text-blue-600 px-4 py-2 rounded-xl shadow-sm hover:bg-blue-100 transition">Buka Dokumen</a>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-20">
                <span class="text-6xl block mb-6">📔</span>
                <h2 class="text-xl font-bold text-gray-800">Isi Modul Belum Diunggah</h2>
                <p class="text-gray-400 text-sm mt-2">Instruktur sedang menyusun materi terbaik untuk Anda.</p>
            </div>
        <?php endif; ?>

        <div class="mt-16 flex items-center justify-between border-t border-gray-100 pt-10">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Selesaikan materi untuk membuka sertifikat</p>
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="text-blue-600 font-bold text-xs hover:underline">Kembali ke Atas ↑</button>
        </div>
    </div>

</body>
</html>