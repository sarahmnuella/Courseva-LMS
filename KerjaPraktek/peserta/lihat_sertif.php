<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Sertifikat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ffffff; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; font-size: 14px; color: #6b7280; transition: all 0.2s; }
        .sidebar-active { color: #3b82f6; font-weight: 600; background-color: #eff6ff; border-radius: 12px; }
    </style>
</head>
<body class="flex">

     <aside class="w-64 min-h-screen p-6 border-r border-gray-100 flex flex-col fixed bg-white z-50">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg uppercase">COURSEVA</span>
        </div>

        <nav class="space-y-6 flex-1">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
                <div class="sidebar-item cursor-pointer"><span>🏠</span><a href="dashboard.php">Dashboard</a></div>
                <div class="sidebar-item sidebar-active cursor-pointer"><span>🕒</span> <a href="history.php">History</a></div>
                <div class="sidebar-item cursor-pointer"><span>📖</span><a href="lesson.php">Lesson</a></div>
                <div class="sidebar-item cursor-pointer"><span>📋</span> <a href="task.php">Task</a></div>
                </ul>
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
            <div class="sidebar-item cursor-pointer text-red-500 font-semibold hover:bg-red-50"><span>🚪</span> Logout</div>
        </div>
    </aside>
    <main class="flex-1 ml-64 p-8">
        
        <div class="flex items-center gap-4 mb-8">
            <div class="relative flex-1">
                <span class="absolute left-4 top-3.5 text-gray-300 text-sm">🔍</span>
                <input type="text" placeholder="Search your course here..." class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-100 shadow-sm outline-none text-sm placeholder:italic">
            </div>
            <div class="w-10 h-10 border border-gray-100 rounded-xl flex items-center justify-center text-gray-400 cursor-pointer">⚲</div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-2 text-gray-800 font-bold">
                <span class="cursor-pointer text-lg">←</span>
                <h2 class="text-sm">Dasar Statistik Bisnis</h2>
            </div>
            <a href="#" class="text-blue-500 text-[10px] font-bold underline">See All</a>
        </div>

        <div class="flex flex-col items-center mb-10">
            <div class="w-full max-w-2xl bg-white shadow-2xl rounded-sm overflow-hidden border border-gray-100">
                 <img src="assets/img/Logo Artavista.png" alt="Sertifikat" class="w-full h-auto">
                <div class="hidden bg-gradient-to-br from-blue-900 to-blue-700 p-1 relative aspect-[1.4/1]">
                     <div class="bg-white w-full h-full flex flex-col items-center justify-center p-10 text-center border-[10px] border-blue-50">
                        <p class="text-blue-900 font-serif text-3xl mb-4 italic">CERTIFICATE</p>
                        <p class="text-xs tracking-[0.3em] text-gray-400 mb-8 uppercase">of completion</p>
                        <h3 class="text-4xl font-light text-gray-800 mb-2">Sarah Manuella</h3>
                        <div class="w-32 h-0.5 bg-blue-500 mb-6"></div>
                        <p class="text-xs text-gray-500 mb-10">29 NOVEMBER 2025</p>
                     </div>
                </div>
            </div>

            <button class="mt-8 flex items-center gap-2 bg-white border border-gray-200 px-6 py-2.5 rounded-xl shadow-sm hover:bg-gray-50 transition-all text-sm font-bold text-gray-700">
                <span class="text-lg">📥</span> Download Sertifikat
            </button>
        </div>

        <div class="max-w-4xl text-[12px] leading-relaxed text-gray-700 space-y-4">
            <p>
                Kelas ini ditujukan sebagai dasar dalam pengambilan keputusan bisnis. Setelah mengikuti kelas ini, peserta diharapkan mampu menelaah konsep dasar statistik serta menerapkannya untuk analisis data sederhana dalam konteks bisnis.
            </p>
            
            <p class="font-bold text-gray-900">Materi Yang Dipelajari:</p>
            <ul class="list-disc pl-5 space-y-3">
                <li>
                    <span class="font-bold text-gray-900">Berkenalan Dengan Statistik Bisnis:</span><br>
                    <span class="text-gray-500">Mengidentifikasi Konsep Dasar Statistik Dan Perannya Dalam Dunia Bisnis. (1 Jam 30 Menit)</span>
                </li>
                <li>
                    <span class="font-bold text-gray-900">Jenis Dan Sumber Data Bisnis:</span><br>
                    <span class="text-gray-500">Memahami Jenis-Jenis Data, Sumber Data, Serta Pemanfaatannya Dalam Analisis Bisnis. (1 Jam 25 Menit)</span>
                </li>
                <li>
                    <span class="font-bold text-gray-900">Statistik Deskriptif:</span><br>
                    <span class="text-gray-500">Menjelaskan Konsep Statistik Deskriptif Seperti Mean, Median, Modus, Dan Visualisasi Data Untuk Merangkum Informasi Bisnis. (2 Jam 30 Menit)</span>
                </li>
                <li>
                    <span class="font-bold text-gray-900">Dasar Statistik Inferensial:</span><br>
                    <span class="text-gray-500">Mengidentifikasi Konsep Dasar Statistik Inferensial Serta Contoh Penerapannya Dalam Pengambilan Keputusan Bisnis. (2 Jam 25 Menit)</span>
                </li>
            </ul>

            <div class="pt-2">
                <p class="font-bold text-gray-900">Evaluasi Pembelajaran:</p>
                <ul class="list-disc pl-5">
                    <li class="text-gray-500">Ujian Akhir Kelas</li>
                </ul>
            </div>

            <p class="pt-2 font-medium">Total Jam Yang Dibutuhkan Untuk Menyelesaikan Kelas Ini Adalah 10 Jam.</p>
        </div>

    </main>

</body>
</html>