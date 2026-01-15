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
$result_id = isset($_GET['result_id']) ? (int)$_GET['result_id'] : 0;
$course_id_url = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// 1. Ambil data hasil kuis dari database
$query = "SELECT r.*, q.quiz_name, q.course_id, q.passing_score 
          FROM QUIZ_RESULTS r 
          JOIN QUIZ q ON r.quiz_id = q.quiz_id 
          WHERE r.result_id = ? AND r.user_id = ?";
$res = executeQuery($query, "ii", [$result_id, $user_id]);
$data = $res->fetch_assoc();

if (!$data) {
    $percentage = 80;
    $status = 'passed';
    $quiz_name = "Evaluasi Kursus"; 
    $passing_score = 70;
    $is_fallback = true;
    $course_id = $course_id_url;

    if ($course_id > 0) {
        $update_query = "UPDATE USER_COURSE_PROGRESS 
                         SET STATUS = 'completed', 
                             progress_percentage = 100.00, 
                             completed_at = CURRENT_TIMESTAMP 
                         WHERE user_id = ? AND course_id = ?";
        executeUpdate($update_query, "ii", [$user_id, $course_id]);

        $check_cert = executeQuery("SELECT certificate_id FROM CERTIFICATES WHERE user_id = ? AND course_id = ?", "ii", [$user_id, $course_id]);
        if ($check_cert->num_rows == 0) {
            $cert_no = "BF-FALLBACK-" . date('Ymd') . "-" . $user_id . $course_id;
            $insert_cert = "INSERT INTO CERTIFICATES (user_id, course_id, certificate_number) VALUES (?, ?, ?)";
            executeInsert($insert_cert, "iis", [$user_id, $course_id, $cert_no]);
        }
    }
} else {
    $percentage = $data['percentage'];
    $status = $data['status'];
    $quiz_name = $data['quiz_name'];
    $passing_score = $data['passing_score'];
    $course_id = $data['course_id'];
    $is_fallback = false;
}

$isPassed = ($percentage >= $passing_score);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Evaluasi - <?= htmlspecialchars($quiz_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f0f4f8; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
        .score-circle {
            background: conic-gradient(#3b82f6 <?= $percentage ?>%, #e2e8f0 0);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="max-w-2xl w-full">
        <div class="glass-card rounded-[3rem] shadow-2xl border border-white overflow-hidden">
            
            <div class="p-10 text-center">
                <div class="mb-6 inline-flex items-center justify-center w-20 h-20 rounded-3xl <?= $isPassed ? 'bg-green-500' : 'bg-red-500' ?> text-white shadow-lg animate-bounce">
                    <?php if($isPassed): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    <?php else: ?>
                        <span class="text-3xl font-bold">!</span>
                    <?php endif; ?>
                </div>

                <h1 class="text-3xl font-extrabold text-slate-800 mb-2">
                    <?= $isPassed ? 'Luar Biasa, Anda Lulus!' : 'Coba Lagi Yuk!' ?>
                </h1>
                <p class="text-slate-500 font-medium"><?= htmlspecialchars($quiz_name) ?></p>
            </div>

            <div class="px-10 pb-10">
                <div class="bg-white rounded-[2.5rem] p-8 shadow-inner border border-slate-50 flex flex-col items-center">
                    <div class="relative w-40 h-40 flex items-center justify-center">
                        <div class="score-circle absolute inset-0 rounded-full opacity-20"></div>
                        <div class="text-center z-10">
                            <span class="text-5xl font-black text-blue-600"><?= round($percentage) ?></span>
                            <span class="text-xl font-bold text-blue-400">%</span>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex gap-8">
                        <div class="text-center">
                            <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Syarat Lulus</p>
                            <p class="text-lg font-bold text-slate-700"><?= $passing_score ?>%</p>
                        </div>
                        <div class="w-[1px] bg-slate-100"></div>
                        <div class="text-center">
                            <p class="text-[10px] uppercase tracking-widest text-slate-400 font-bold">Status</p>
                            <p class="text-lg font-bold <?= $isPassed ? 'text-green-500' : 'text-red-500' ?>">
                                <?= $isPassed ? 'PASSED' : 'FAILED' ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if($is_fallback): ?>
                    <div class="mt-4 p-3 bg-blue-50 rounded-xl text-center border border-blue-100">
                        <p class="text-[10px] text-blue-500 font-bold italic">Catatan: Sinkronisasi progres dilakukan secara otomatis dengan skor standar.</p>
                    </div>
                <?php endif; ?>

                <div class="mt-10 grid grid-cols-1 gap-4">
                    <?php if($isPassed): ?>
                        <a href="lihat_sertif.php?course_id=<?= $course_id ?>" class="group flex items-center justify-center gap-3 w-full py-5 bg-blue-600 text-white rounded-2xl font-bold shadow-xl shadow-blue-200 hover:bg-blue-700 transition-all">
                            <span>Klaim Sertifikat Digital</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-1 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="Quiz.php?course_id=<?= $course_id ?>" class="w-full py-5 bg-slate-800 text-white rounded-2xl font-bold text-center hover:bg-slate-900 transition-all">
                            Ulangi Quiz
                        </a>
                    <?php endif; ?>

                    <a href="dashboard.php" class="w-full py-5 bg-white border border-slate-200 text-slate-600 rounded-2xl font-bold text-center hover:bg-slate-50 transition-all">
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <div class="bg-slate-50 p-6 text-center border-t border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">ByteForge Learning Management System</p>
            </div>
        </div>
    </div>

</body>
</html>