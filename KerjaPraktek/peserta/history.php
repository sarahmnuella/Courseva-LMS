<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courseva - History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #fcfcfd; }
        .active-link { color: #3b82f6; font-weight: 600; background-color: #eff6ff; border-radius: 12px; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 min-h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed shadow-sm">
        <div class="flex items-center gap-2 mb-10 px-2">
            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs shadow-md">B</div>
            <span class="font-bold text-blue-900 tracking-wide text-lg">COURSEVA</span>
        </div>

        <nav class="space-y-8 flex-1">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-4 px-2">Overview</p>
                <ul class="space-y-1 text-sm text-gray-500">
                    <li class="flex items-center gap-3 px-3 py-3 cursor-pointer hover:text-blue-500 transition-all"><span>🏠</span> <a href="dashboard.php">Dashboard</a></li>
                    <li class="active-link flex items-center gap-3 px-3 py-3 cursor-pointer transition-all"><span>🕒</span> <a href="history.php">History</a></li>
                    <li class="flex items-center gap-3 px-3 py-3 cursor-pointer hover:text-blue-500 transition-all"><span>📖</span> Lesson</li>
                    <li class="flex items-center gap-3 px-3 py-3 cursor-pointer hover:text-blue-500 transition-all"><span>📋</span> Task</li>
                </ul>
            </div>

            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.15em] mb-4 px-2">Friends</p>
                <ul class="space-y-4 px-2">
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center text-[10px]">👩‍💻</div>
                        <div><p class="text-xs font-bold text-gray-700">Sarah</p><p class="text-[9px] text-gray-400 font-medium">Software Developer</p></div>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full border-2 border-green-400 p-0.5"><div class="w-full h-full bg-gray-200 rounded-full"></div></div>
                        <div><p class="text-xs font-bold text-gray-700">Sahaf</p><p class="text-[9px] text-gray-400 font-medium">Software Developer</p></div>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center text-[10px]">👩‍💻</div>
                        <div><p class="text-xs font-bold text-gray-700">Putri</p><p class="text-[9px] text-gray-400 font-medium">Software Developer</p></div>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="space-y-2 pt-6 border-t border-gray-100">
            <div class="flex items-center gap-3 px-3 py-2 text-sm text-gray-500 cursor-pointer hover:bg-gray-50 rounded-lg transition-all"><span>⚙️</span> Profil</div>
            <div class="flex items-center gap-3 px-3 py-2 text-sm text-red-500 font-semibold cursor-pointer hover:bg-red-50 rounded-lg transition-all"><span>🚪</span> Logout</div>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
        
        <div class="flex items-center gap-4 mb-10">
            <div class="relative flex-1">
                <span class="absolute left-4 top-3.5 text-gray-300">🔍</span>
                <input type="text" placeholder="Search your course here..." class="w-full pl-12 pr-4 py-3.5 rounded-2xl border border-gray-100 shadow-sm focus:ring-1 focus:ring-blue-100 outline-none text-sm bg-white placeholder:italic">
            </div>
            <button class="w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center text-gray-400">
                <span class="text-xl">⚲</span>
            </button>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-10">
            <?php 
                $stats = [
                    ['title' => '2/8 Finished', 'sub' => 'Developer', 'color' => 'bg-purple-100', 'icon' => '🔔'],
                    ['title' => '2/8 On Going', 'sub' => 'Finishing', 'color' => 'bg-blue-100', 'icon' => '🔔'],
                    ['title' => '2/8 Finished', 'sub' => 'Back', 'color' => 'bg-purple-100', 'icon' => '🔔'],
                ];
                foreach ($stats as $s): 
            ?>
            <div class="bg-white p-5 rounded-3xl shadow-sm border border-gray-50 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 <?= $s['color'] ?> rounded-2xl flex items-center justify-center text-blue-500 opacity-80"><?= $s['icon'] ?></div>
                    <div>
                        <p class="text-[11px] font-bold text-gray-400 tracking-wide uppercase"><?= $s['title'] ?></p>
                        <p class="text-sm font-bold text-gray-800"><?= $s['sub'] ?></p>
                    </div>
                </div>
                <span class="text-gray-300 font-bold">⋮</span>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-gray-800 text-lg">Your Progress</h3>
            <a href="#" class="text-blue-500 text-xs font-bold underline">See All</a>
        </div>

        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-50">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-50">
                        <th class="pb-6 px-4">Instructor Name & Date</th>
                        <th class="pb-6 px-4">Course Type</th>
                        <th class="pb-6 px-4 text-center">Course Title</th>
                        <th class="pb-6 px-4 text-right">Actions</th>
                    </tr>
                </thead>
<tbody class="text-sm">
    <?php
    $courses = [
        ['title' => 'Pengenalan Data & Data Literacy', 'type' => 'LANJUTKAN', 'status' => 'blue'],
        ['title' => 'Fundamental Data Untuk Pengambilan Keputusan', 'type' => 'LANJUTKAN', 'status' => 'blue'],
        ['title' => 'Dasar Statistik Bisnis', 'type' => 'SELESAI', 'status' => 'purple'],
        ['title' => 'Analisis Data Menggunakan SQL Dasar', 'type' => 'SELESAI', 'status' => 'purple'],
        ['title' => 'Data Cleaning Dan Validasi Data', 'type' => 'LANJUTKAN', 'status' => 'blue'],
        ['title' => 'Visualisasi Dan Pelaporan Data', 'type' => 'MULAI', 'status' => 'indigo'],
        ['title' => 'Etika Dan Keamanan Data', 'type' => 'MULAI', 'status' => 'indigo'],
    ];

    foreach ($courses as $c):
        if ($c['type'] == 'SELESAI') {
            $btnLabel = 'LIHAT SERTIFIKAT';
            $targetPage = 'lihat_sertif.php'; 
        } else {
            $btnLabel = 'LIHAT DETAIL';
            $targetPage = 'detail_kursus.php'; 
        }
    ?>
    <tr class="group hover:bg-gray-50 transition-all border-b border-gray-50 last:border-0">
        <td class="py-5 px-4">
            <div class="flex items-center gap-3">
                <div class="w-5 h-5 rounded-full border-2 border-green-400"></div>
                <div>
                    <p class="font-bold text-gray-700 leading-tight"><?= $c['title'] ?></p>
                    <p class="text-[10px] text-gray-400 font-medium mt-1">25/2/2025</p>
                </div>
            </div>
        </td>
        <td class="py-5 px-4">
            <span class="text-[9px] font-black px-3 py-1 bg-purple-100 text-purple-600 rounded-lg tracking-tighter italic">
                <?= $c['type'] ?>
            </span>
        </td>
        <td class="py-5 px-4 text-center">
            <p class="text-xs text-gray-500 font-medium">Understanding Concept Of React</p>
        </td>
        <td class="py-5 px-4 text-right">
            <a href="<?= $targetPage ?>" class="inline-block">
                <button class="text-[10px] font-bold text-blue-400 border border-blue-100 px-4 py-1.5 rounded-lg hover:bg-blue-500 hover:text-white transition-all uppercase">
                    <?= $btnLabel ?>
                </button>
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
            </table>
        </div>

    </main>

</body>
</html>