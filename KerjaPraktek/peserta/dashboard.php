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
        .sidebar-active { color: #3b82f6; font-weight: 600; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 min-h-screen bg-white p-6 border-r border-gray-100 flex flex-col">
        <div class="flex items-center gap-2 mb-10">
            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-lg">B</div>
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
        </div>

        <nav class="space-y-6 flex-1">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Overview</p>
                <ul class="space-y-4 text-sm text-gray-500">
                    <li class="sidebar-active flex items-center gap-3"><span class="w-5 h-5 opacity-70">🏠</span> Dashboard</li>
                    <li class="flex items-center gap-3"><span class="w-5 h-5">🕒</span><a href="history.php">history</a></li>
                    <li class="flex items-center gap-3"><span class="w-5 h-5">📖</span> <a href="lesson.php">Lesson</a></li>
                    <li class="flex items-center gap-3"><span class="w-5 h-5">📋</span> Task</li>
                </ul>
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4">Friends</p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-200"></div>
                        <div><p class="text-xs font-bold text-gray-700">Sarah</p><p class="text-[10px] text-gray-400">Software Developer</p></div>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-green-200 border-2 border-green-400"></div>
                        <div><p class="text-xs font-bold text-gray-700">Sahaf</p><p class="text-[10px] text-gray-400">Software Developer</p></div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="pt-10 border-t border-gray-50 text-sm text-gray-500 flex items-center gap-3">
            <span>⚙️</span> Profil
        </div>
    </aside>

    <main class="flex-1 p-8">
        <div class="flex items-center justify-between mb-8">
            <div class="relative w-full max-w-2xl">
                <span class="absolute left-4 top-3 text-gray-400">🔍</span>
                <input type="text" placeholder="Search your courses here..." class="w-full pl-12 pr-4 py-3 rounded-2xl border-none shadow-sm focus:ring-1 focus:ring-blue-300 outline-none text-sm italic bg-white">
            </div>
            <div class="w-10 h-10 bg-white rounded-xl shadow-sm flex items-center justify-center cursor-pointer">
                <span>🏮</span>
            </div>
        </div>

        <div class="bg-blue-500 rounded-[2rem] p-10 text-white relative overflow-hidden mb-8">
            <div class="relative z-10 max-w-md">
                <p class="text-[10px] font-medium opacity-80 mb-2 uppercase tracking-widest">Online Course</p>
                <h1 class="text-3xl font-bold leading-tight mb-6">Sharpen Your Skills With Professional Online Courses</h1>
                <button class="bg-black text-white px-6 py-2 rounded-full text-xs font-semibold flex items-center gap-2">Join Now <span class="bg-white text-black rounded-full w-4 h-4 text-[10px] flex items-center justify-center">→</span></button>
            </div>
            <div class="absolute right-10 top-1/2 -translate-y-1/2 opacity-20 text-6xl italic">✦ ✦</div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-800">Continue Learning</h3>
            <div class="flex gap-2">
                <button class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-xs">〈</button>
                <button class="w-8 h-8 rounded-full border border-gray-200 flex items-center justify-center text-xs">〉</button>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <div class="bg-white p-4 rounded-3xl shadow-sm">
                <div class="h-32 bg-gray-900 rounded-2xl mb-4 overflow-hidden">
                    <img src="https://via.placeholder.com/300x150" class="w-full h-full object-cover opacity-60">
                </div>
                <span class="bg-purple-100 text-purple-600 text-[10px] px-3 py-1 rounded-full font-bold">FRONTEND</span>
                <h4 class="mt-3 text-sm font-bold text-gray-800">Pengenalan Data & Data Literacy</h4>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-green-500"></div>
                    <div><p class="text-[10px] font-bold text-gray-700">Dicoding Indonesia</p></div>
                </div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div class="w-1/2 h-full bg-blue-400"></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl shadow-sm">
                <div class="h-32 bg-yellow-400 rounded-2xl mb-4 overflow-hidden">
                    <img src="https://via.placeholder.com/300x150" class="w-full h-full object-cover">
                </div>
                <span class="bg-purple-100 text-purple-600 text-[10px] px-3 py-1 rounded-full font-bold">FRONTEND</span>
                <h4 class="mt-3 text-sm font-bold text-gray-800">Fundamental Data Untuk Pengambilan Keputusan</h4>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-green-500"></div>
                    <div><p class="text-[10px] font-bold text-gray-700">Kelas Terbuka</p></div>
                </div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div class="w-3/4 h-full bg-blue-400"></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-3xl shadow-sm">
                <div class="h-32 bg-blue-100 rounded-2xl mb-4 overflow-hidden">
                    <img src="https://via.placeholder.com/300x150" class="w-full h-full object-cover">
                </div>
                <span class="bg-purple-100 text-purple-600 text-[10px] px-3 py-1 rounded-full font-bold">FRONTEND</span>
                <h4 class="mt-3 text-sm font-bold text-gray-800">Analisis Data Menggunakan SQL Dasar</h4>
                <div class="mt-4 flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-green-500"></div>
                    <div><p class="text-[10px] font-bold text-gray-700">Sandhika Galih</p></div>
                </div>
                <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div class="w-1/3 h-full bg-blue-400"></div>
                </div>
            </div>
        </div>

    </main>
</body>
</html>