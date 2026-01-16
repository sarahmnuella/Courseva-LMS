<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
session_start();

// Proteksi Halaman: Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// 1. Ambil data user saat ini untuk ditampilkan di form
$query = "SELECT * FROM USERS WHERE user_id = ?";
$user = executeQuery($query, "i", [$user_id])->fetch_assoc();

// 2. Logika Update Data (CRUD & Upload Gambar)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $nama = $_POST['nama_lengkap'];
    $telepon = $_POST['nomor_telepon'];
    $email = $_POST['email'];
    $foto_nama = $user['fotoProfil']; // Gunakan foto lama sebagai default

    // Logika Upload Gambar ke Server
    if (isset($_FILES['fotoProfil']) && $_FILES['fotoProfil']['error'] == 0) {
        $target_dir = "../assets/img/profiles/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES["fotoProfil"]["name"], PATHINFO_EXTENSION);
        $new_filename = "profile_" . $user_id . "_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;

        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array(strtolower($file_extension), $allowed)) {
            if (move_uploaded_file($_FILES["fotoProfil"]["tmp_name"], $target_file)) {
                // Hapus foto lama agar tidak memenuhi server
                if (!empty($user['fotoProfil']) && file_exists($target_dir . $user['fotoProfil'])) {
                    unlink($target_dir . $user['fotoProfil']);
                }
                $foto_nama = $new_filename;
            }
        } else {
            $message = "Format file tidak didukung.";
        }
    }

    // Update ke Database
    $sql_update = "UPDATE USERS SET nama_lengkap = ?, nomor_telepon = ?, email = ?, fotoProfil = ? WHERE user_id = ?";
    if (executeUpdate($sql_update, "ssssi", [$nama, $telepon, $email, $foto_nama, $user_id])) {
        $_SESSION['nama_lengkap'] = $nama; // Update session nama
        
        // --- LOGIKA REDIRECT: Balik ke halaman profil setelah simpan ---
        header("Location: profil.php?status=success");
        exit();
    } else {
        $message = "Gagal memperbarui data.";
    }
}

// 3. Query Friends untuk Sidebar
$friend_result = executeQuery("SELECT nama_lengkap FROM USERS WHERE user_id != ? LIMIT 3", "i", [$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Courseva</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px; transition: 0.3s; color: #64748b; font-size: 14px; }
        .sidebar-item:hover { background-color: #eff6ff; color: #3b82f6; }
        .sidebar-active { background-color: #eff6ff; color: #3b82f6; font-weight: 600; }
    </style>
</head>
<body class="flex">

    <aside class="w-64 h-screen bg-white p-6 border-r border-gray-100 flex flex-col fixed left-0 top-0 z-50 shadow-sm">
        <div class="flex items-center gap-3 mb-10 px-2">
            <img src="../assets/img/Logo Artavista.png" alt="Logo" class="w-10 h-10 object-contain rounded-lg shadow-sm" onerror="this.src='https://via.placeholder.com/40'">
            <span class="font-bold text-blue-900 tracking-wide">COURSEVA</span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Overview</p>
            <a href="dashboard.php" class="sidebar-item"><span>🏠</span> Dashboard</a>
            <a href="history.php" class="sidebar-item"><span>🕒</span> History</a>
            <a href="dashboard.php" class="sidebar-item"><span>📖</span> Lesson</a>
            <a href="task.php" class="sidebar-item"><span>📋</span> Task</a>
            
            <div class="mt-8">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Friends</p>
                <div class="space-y-3 px-2">
                    <?php while($friend = $friend_result->fetch_assoc()): ?>
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 bg-green-100 rounded-full flex items-center justify-center text-[10px]">👤</div>
                        <span class="text-xs text-gray-600 truncate"><?= htmlspecialchars($friend['nama_lengkap']) ?></span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </nav>

        <div class="mt-auto pt-6 border-t border-gray-100 space-y-1">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2 px-2">Account</p>
            <a href="profile.php" class="sidebar-item sidebar-active"><span>⚙️</span> Profil</a>
            <a href="../logout.php" class="sidebar-item text-red-500 hover:bg-red-50"><span>🚪</span> Keluar</a>
        </div>
    </aside>

    <main class="flex-1 ml-64 p-8">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center gap-4 mb-8">
                <a href="profile.php" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm border border-gray-100 text-gray-400 hover:text-blue-600 transition">←</a>
                <h1 class="text-2xl font-bold text-slate-800">Edit Profil</h1>
            </div>

            <?php if(!empty($message)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm border border-red-100"><?= $message ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-10 space-y-8">
                    <div class="flex items-center gap-8 pb-8 border-b border-slate-50">
                        <div class="w-24 h-24 bg-slate-100 rounded-[1.5rem] overflow-hidden border-4 border-white shadow-lg">
                            <?php if(!empty($user['fotoProfil'])): ?>
                                <img src="../assets/img/profiles/<?= $user['fotoProfil'] ?>" id="imgPreview" class="w-full h-full object-cover">
                            <?php else: ?>
                                <div id="imgPlaceholder" class="w-full h-full flex items-center justify-center text-4xl">👤</div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Ganti Foto Profil</label>
                            <input type="file" name="fotoProfil" id="inputFoto" accept="image/*" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-600 cursor-pointer">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-100 outline-none text-sm font-medium text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">ID Karyawan</label>
                            <input type="text" value="<?= htmlspecialchars($user['id_karyawan']) ?>" readonly class="w-full px-5 py-3 bg-slate-100 border border-slate-100 rounded-2xl text-sm text-slate-400 cursor-not-allowed">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-100 outline-none text-sm font-medium text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider ml-1">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" value="<?= htmlspecialchars($user['nomor_telepon']) ?>" required class="w-full px-5 py-3 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-2 focus:ring-blue-100 outline-none text-sm font-medium text-slate-700">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="w-full md:w-auto px-12 py-4 bg-blue-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition shadow-lg shadow-blue-100">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Preview Foto Real-time
        const inputFoto = document.getElementById('inputFoto');
        const imgPreview = document.getElementById('imgPreview');
        const imgPlaceholder = document.getElementById('imgPlaceholder');

        inputFoto.onchange = evt => {
            const [file] = inputFoto.files;
            if (file) {
                if (imgPreview) {
                    imgPreview.src = URL.createObjectURL(file);
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = URL.createObjectURL(file);
                    newImg.className = "w-full h-full object-cover";
                    imgPlaceholder.parentNode.appendChild(newImg);
                    imgPlaceholder.remove();
                }
            }
        }
    </script>
</body>
</html>