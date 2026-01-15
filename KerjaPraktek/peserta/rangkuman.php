<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - Rangkuman Kelas</title>
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
                <a href="#" class="sidebar-item"><span>📋</span> Task</a>
            </div>
            
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-4 px-2">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-[10px]">👩‍💻</div>
                        <div><p class="text-xs font-bold text-gray-700">Sarah</p></div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="pt-6 border-t border-gray-100 space-y-2">
            <a href="profile.php" class="sidebar-item"><span>⚙️</span> Profil</a>
            <a href="login.php" class="sidebar-item text-red-500 font-semibold"><span>🚪</span> Logout</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8 flex flex-col h-screen">
        
        <div class="flex-1 overflow-y-auto pr-4">
            <div class="flex items-center justify-between mb-8">
                <a href="lesson.php" class="text-gray-400 text-xl">←</a>
                <h2 class="text-sm font-semibold text-gray-700 text-center flex-1">Pengenalan Data & Data Literacy</h2>
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">⚲</span>
                    <span class="text-xs font-bold text-gray-700">Modul</span>
                </div>
            </div>

            <div class="w-full h-44 bg-zinc-900 rounded-[2.5rem] relative overflow-hidden mb-10 shadow-lg border-4 border-white">
                <div class="absolute inset-0 opacity-40 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]"></div>
                <div class="relative z-10 h-full flex flex-col items-center justify-center text-center p-6 text-white">
                    <h1 class="text-2xl font-bold mb-3">Pengenalan Data dan<br>Literacy</h1>
                    <button class="bg-white/10 backdrop-blur-md border border-white/20 px-5 py-1.5 rounded-full text-[10px] font-semibold flex items-center gap-2">
                        study more <span class="bg-white text-black rounded-full w-4 h-4 text-[8px] flex items-center justify-center">▶</span>
                    </button>
                </div>
                <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-4xl text-white">✦ ✦</div>
            </div>

            <div class="max-w-5xl">
                <h3 class="text-lg font-bold text-gray-800 mb-6 italic">Rangkuman Kelas</h3>
                
                <div class="space-y-8 text-xs leading-relaxed text-gray-700 text-justify">
                    <section>
                        <p class="font-bold text-gray-900 mb-2">Apa itu Data?</p>
                        <p>Data adalah sekumpulan fakta, angka, simbol, teks, atau informasi mentah yang diperoleh dari hasil pengamatan, pengukuran, maupun pencatatan suatu objek atau peristiwa. Data pada dasarnya belum memiliki makna yang jelas apabila belum diolah, namun menjadi dasar utama dalam proses analisis dan pengambilan keputusan. Dalam konteks sistem informasi dan teknologi, data dapat diolah, disimpan, dan diproses menggunakan berbagai metode dan alat sehingga menghasilkan informasi yang bernilai, akurat, dan mudah dipahami oleh pengguna.</p>
                    </section>

                    <section>
                        <p class="font-bold text-gray-900 mb-2">Dasar Statistik untuk Data</p>
                        <p>Statistik adalah cabang ilmu yang mempelajari cara mengumpulkan, mengolah, menganalisis, dan menyajikan data agar dapat menghasilkan informasi yang bermakna. Dalam pengolahan data, statistik berperan penting untuk memahami pola, kecenderungan, serta hubungan antar data yang dianalisis. Dasar statistik mencakup konsep-konsep seperti pengukuran data, perhitungan nilai rata-rata, median, modus, serta penyebaran data. Dengan memahami dasar statistik, data yang awalnya bersifat mentah dapat diinterpretasikan secara lebih objektif dan digunakan sebagai dasar dalam pengambilan keputusan.</p>
                    </section>

                    <section>
                        <p class="font-bold text-gray-900 mb-2">Alur Kerja Data</p>
                        <p>Alur kerja data adalah rangkaian proses sistematik yang menggambarkan bagaimana data dikumpulkan, diolah, dianalisis, hingga disajikan menjadi informasi yang berguna. Alur ini membantu memastikan bahwa data diproses secara terstruktur, akurat, dan dapat dipertanggungjawabkan. Secara umum, alur kerja data dimulai dari pengumpulan data, dilanjutkan dengan pembersihan dan pengolahan data, kemudian analisis data, serta diakhiri dengan penyajian hasil analisis dalam bentuk laporan atau visualisasi. Dengan memahami alur kerja data, proses pengolahan data dapat berjalan lebih efektif dan mendukung pengambilan keputusan berbasis data.</p>
                    </section>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 py-8 text-[11px] font-bold text-gray-400 tracking-widest uppercase bg-white mt-4">
            <a href="lesson.php" class="flex items-center gap-2 cursor-pointer hover:text-blue-500 transition-all">
                <span>⚙</span> BACK
            </a>
            <div class="flex items-center gap-2 text-gray-800">
                RANGKUMAN KELAS
            </div>
            <a href="lesson.php" class="flex items-center gap-2 cursor-pointer text-gray-700 hover:text-blue-500 transition-all">
                KEMBALI KE BERANDA KELAS <span>⚙</span>
            </a>
        </div>
    </main>

</body>
</html>