<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Aturan Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; font-size: 14px; color: #6b7280; transition: all 0.2s; border-radius: 12px; }
        .sidebar-item:hover { color: #3b82f6; background-color: #f8fafc; }
        .sidebar-active { color: #3b82f6; font-weight: 600; background-color: #eff6ff; }
    </style>
</head>
<body class="flex">

    <?php $current_page = 'lesson'; ?>

    <aside class="w-64 min-h-screen p-6 border-r border-gray-100 flex flex-col fixed bg-white z-50">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg uppercase">COURSEVA</span>
        </div>

        <nav class="space-y-6 flex-1">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
                <a href="dashboard.php" class="sidebar-item <?= $current_page == 'dashboard' ? 'sidebar-active' : '' ?>"><span>🏠</span> Dashboard</a>
                <a href="history.php" class="sidebar-item <?= $current_page == 'history' ? 'sidebar-active' : '' ?>"><span>🕒</span> History</a>
                <a href="lesson.php" class="sidebar-item <?= $current_page == 'lesson' ? 'sidebar-active' : '' ?>"><span>📖</span> Lesson</a>
                <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            </div>
            
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-4 px-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-[10px]">👩‍💻</div>
                        <div><p class="text-xs font-bold text-gray-700">Sarah</p><p class="text-[9px] text-gray-400 font-medium">Software Developer</p></div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border-2 border-green-400 p-0.5"><div class="w-full h-full bg-gray-200 rounded-full"></div></div>
                        <div><p class="text-xs font-bold text-gray-700">Sahaf</p><p class="text-[9px] text-gray-400 font-medium">Software Developer</p></div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="pt-6 border-t border-gray-100 space-y-2">
            <div class="sidebar-item cursor-pointer"><span>⚙️</span> Profil</div>
            <a href="login.php" class="sidebar-item text-red-500 font-semibold hover:bg-red-50"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8 flex flex-col h-screen">
        
        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <a href="lesson.php" class="text-gray-400 hover:text-gray-600 transition-all text-xl">←</a>
                <h2 class="text-sm font-semibold text-gray-700">Pengenalan Data & Data Literacy</h2>
                <div class="flex items-center gap-4">
                    <span class="text-gray-400">⚲</span>
                    <span class="text-xs font-bold text-gray-700">Modul</span>
                </div>
            </div>

            <div class="w-full h-48 bg-zinc-900 rounded-[2rem] relative overflow-hidden mb-10 shadow-lg border-4 border-white">
                <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="relative z-10 h-full flex flex-col items-center justify-center text-center p-6 text-white">
                    <h1 class="text-2xl font-bold mb-4">Pengenalan Data dan<br>Literacy</h1>
                    <button class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-1.5 rounded-full text-[10px] font-semibold flex items-center gap-2">
                        study more <span class="bg-white text-black rounded-full w-4 h-4 text-[8px] flex items-center justify-center">▶</span>
                    </button>
                </div>
                <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-4xl text-white">✦ ✦</div>
            </div>

            <div class="max-w-5xl">
                <h3 class="text-sm font-bold text-gray-800 mb-4">Role :</h3>
                <div class="text-sm leading-relaxed text-gray-600 text-justify">
                    <p>
                        Ujian Akhir bertujuan untuk menguji pengetahuan Anda tentang semua materi yang telah dipelajari di kelas ini. Terdapat 20 pertanyaan yang harus dikerjakan dalam kuis ini. Beberapa ketentuannya sebagai berikut: Syarat nilai kelulusan : 75% Durasi ujian : 60 menit Apabila tidak memenuhi syarat kelulusan, maka Anda harus menunggu selama 120 menit untuk mengulang pengerjaan kuis kembali. Manfaatkan waktu tunggu tersebut untuk mempelajari kembali materi sebelumnya, ya. Selamat Mengerjakan!
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 py-8 text-[11px] font-bold text-gray-400 tracking-widest uppercase bg-white">
            <a href="lesson.php" class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                <span>☸</span> HOME
            </a>
            <div class="flex items-center gap-2 text-gray-800">
                ATURAN KUIZ
            </div>
            <a href="Quiz.php" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-blue-500 transition-all">
                START <span>☸</span>
            </a>
        </div>
    </main>

</body>
</html>