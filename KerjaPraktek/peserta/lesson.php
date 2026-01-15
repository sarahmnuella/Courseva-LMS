<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Learning Page</title>
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

    <?php 
        // Mengambil parameter halaman dari URL, default ke 'lesson'
        $page = isset($_GET['page']) ? $_GET['page'] : 'lesson';
    ?>

    <aside class="w-64 min-h-screen p-6 border-r border-gray-100 flex flex-col fixed bg-white z-50">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg uppercase">COURSEVA</span>
        </div>

        <nav class="space-y-6 flex-1">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
                <a href="dashboard.php" class="sidebar-item <?= $page == 'dashboard' ? 'sidebar-active' : '' ?>"><span>🏠</span> Dashboard</a>
                <a href="history.php" class="sidebar-item <?= $page == 'history' ? 'sidebar-active' : '' ?>"><span>🕒</span> History</a>
                <a href="lesson.php?page=lesson" class="sidebar-item <?= $page == 'lesson' ? 'sidebar-active' : '' ?>"><span>📖</span> Lesson</a>
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
            <div class="sidebar-item cursor-pointer text-red-500 font-semibold hover:bg-red-50"><span>🚪</span> Logout</div>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
        
        <div class="flex items-center justify-between mb-8">
            <a href="history.php" class="text-gray-400 hover:text-gray-600 transition-all text-xl">←</a>
            <h2 class="text-sm font-semibold text-gray-700">Pengenalan Data & Data Literacy</h2>
            <div class="flex items-center gap-4">
                <span class="text-gray-400">⚲</span>
                <span class="text-xs font-bold text-gray-700">Modul</span>
            </div>
        </div>

        <div class="w-full h-64 bg-zinc-900 rounded-[2rem] relative overflow-hidden mb-10 shadow-xl border-4 border-white">
            <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
            <div class="relative z-10 h-full flex flex-col items-center justify-center text-center p-6">
                <h1 class="text-white text-3xl font-bold mb-4">Pengenalan Data dan<br>Literacy</h1>
                <button class="bg-white/10 backdrop-blur-md text-white border border-white/20 px-6 py-2 rounded-full text-xs font-semibold flex items-center gap-2 hover:bg-white/20 transition-all">
                    study more <span class="bg-white text-black rounded-full w-4 h-4 text-[10px] flex items-center justify-center">▶</span>
                </button>
            </div>
            <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-5xl text-white">✦ ✦</div>
        </div>

        <div class="max-w-5xl mx-auto">
            <h3 class="text-xl font-bold text-gray-800 mb-6 italic">introduction Class</h3>
            
            <div class="space-y-6 text-sm leading-relaxed text-gray-600 text-justify">
                <p>
                    Kelas Pengenalan Data dan Data Literacy dirancang sebagai pengantar bagi siapa pun yang ingin mulai memahami peran data dalam dunia kerja dan pengambilan keputusan. Dalam kelas ini, peserta akan diperkenalkan dengan konsep dasar data, jenis-jenis data, serta bagaimana data digunakan untuk menghasilkan informasi yang bernilai dan relevan.
                </p>
                <p>
                    Peserta akan mempelajari dasar-dasar data literacy, mulai dari cara membaca, memahami, hingga menafsirkan data dengan benar. Materi mencakup pemahaman sumber data, kualitas data, serta penggunaan data dalam konteks bisnis dan organisasi. Materi disusun secara bertahap dan mudah dipahami, sehingga cocok untuk pemula yang belum memiliki latar belakang analisis data.
                </p>
                <p>
                    Setiap topik disampaikan dengan pendekatan praktis agar peserta dapat langsung mengaitkan konsep yang dipelajari dengan situasi nyata di lingkungan kerja. Peserta akan dilatih untuk berpikir kritis terhadap data, mengenali potensi bias, serta memahami bagaimana data dapat mendukung pengambilan keputusan yang lebih tepat.
                </p>
                <p>
                    Selain pemahaman teknis dasar, kelas ini juga membahas mindset data-driven dan etika dalam penggunaan data. Peserta akan dikenalkan dengan praktik terbaik dalam pengelolaan dan pemanfaatan data, sehingga tidak hanya mampu memahami data, tetapi juga siap menerapkan data literacy sebagai bagian dari budaya kerja profesional.
                </p>
            </div>

            <div class="mt-16 flex items-center justify-between border-t border-gray-100 pt-8 text-[11px] font-bold text-gray-400 tracking-widest uppercase">
                <div class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                    <span>☸</span> HOME
                </div>
                <div class="flex items-center gap-2 text-gray-800">
                    PENGENALAN KELAS
                </div>
                <div class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                    <a href="task.php">Quiz</a> <span>☸</span>
                </div>
            </div>
        </div>
    </main>

</body>
</html>