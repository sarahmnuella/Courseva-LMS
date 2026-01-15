<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; font-size: 14px; color: #6b7280; transition: all 0.2s; border-radius: 12px; }
        .sidebar-item:hover { color: #3b82f6; background-color: #f8fafc; }
        .sidebar-active { color: #3b82f6; font-weight: 600; background-color: #eff6ff; }
        
        /* Custom radio button style */
        input[type="radio"]:checked + label {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #3b82f6;
        }
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
            </nav>
        <div class="pt-6 border-t border-gray-100 space-y-2">
            <div class="sidebar-item cursor-pointer"><span>⚙️</span> Profil</div>
            <a href="login.php" class="sidebar-item text-red-500 font-semibold hover:bg-red-50"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8 flex flex-col h-screen">
        
        <div class="flex-1">
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <a href="quiz_rules.php" class="text-gray-400 hover:text-gray-600 transition-all text-xl">←</a>
                    <div class="bg-blue-50 px-4 py-2 rounded-xl border border-blue-100">
                        <span class="text-xs font-bold text-blue-600">Time Left: 59:45</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-gray-700">Question 1/20</span>
                    <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="w-5 h-full bg-blue-500"></div>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl">
                <span class="inline-block px-3 py-1 bg-purple-100 text-purple-600 text-[10px] font-black rounded-lg mb-4 italic uppercase">SOAL NO 1</span>
                
                <h2 class="text-xl font-bold text-gray-800 mb-8 leading-relaxed">
                    Manakah dari berikut ini yang paling tepat mendefinisikan konsep dasar dari "Data Literacy"?
                </h2>

                <form action="#" class="space-y-4">
                    <div class="relative">
                        <input type="radio" id="opt1" name="quiz" class="hidden peer" checked>
                        <label for="opt1" class="flex items-center p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-300 transition-all text-sm font-medium text-gray-600">
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg mr-4 text-xs font-bold group-peer-checked:border-blue-500">A</span>
                            Kemampuan untuk mengumpulkan data dalam jumlah besar secara otomatis.
                        </label>
                    </div>

                    <div class="relative">
                        <input type="radio" id="opt2" name="quiz" class="hidden peer">
                        <label for="opt2" class="flex items-center p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-300 transition-all text-sm font-medium text-gray-600">
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg mr-4 text-xs font-bold">B</span>
                            Kemampuan untuk membaca, memahami, membuat, dan mengomunikasikan data sebagai informasi.
                        </label>
                    </div>

                    <div class="relative">
                        <input type="radio" id="opt3" name="quiz" class="hidden peer">
                        <label for="opt3" class="flex items-center p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-300 transition-all text-sm font-medium text-gray-600">
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg mr-4 text-xs font-bold">C</span>
                            Proses menghapus data yang tidak relevan dari database perusahaan.
                        </label>
                    </div>

                    <div class="relative">
                        <input type="radio" id="opt4" name="quiz" class="hidden peer">
                        <label for="opt4" class="flex items-center p-4 border border-gray-200 rounded-2xl cursor-pointer hover:border-blue-300 transition-all text-sm font-medium text-gray-600">
                            <span class="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg mr-4 text-xs font-bold">D</span>
                            Hanya terbatas pada kemampuan teknis dalam menggunakan bahasa pemrograman SQL.
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 py-8 text-[11px] font-bold text-gray-400 tracking-widest uppercase bg-white">
            <button class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all opacity-50 cursor-not-allowed">
                <span>☸</span> PREVIOUS
            </button>
            <div class="flex items-center gap-2 text-gray-800">
                QUIZ RUNNING
            </div>
            <button class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-blue-500 transition-all font-black">
                NEXT <span>☸</span>
            </button>
        </div>
    </main>

</body>
</html>