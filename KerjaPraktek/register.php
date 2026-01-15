<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

$pageTitle = "Register - ByteForge LMS";

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
    $nomor_telepon = sanitize($_POST['nomor_telepon'] ?? '');
    $id_karyawan = sanitize($_POST['nik'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($nama_lengkap) || empty($nomor_telepon) || empty($id_karyawan) || empty($email) || empty($password)) {
        $errors[] = "Semua field harus diisi.";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak sama.";
    }
    
    // Jika tidak ada error, cek duplikasi dan insert
    if (empty($errors)) {
        $conn = getDBConnection();
        
        // Cek email sudah terdaftar
        $checkEmail = "SELECT user_id FROM USERS WHERE email = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email sudah terdaftar.";
        }
        $stmt->close();
        
        // Cek ID Karyawan sudah terdaftar
        $checkNIK = "SELECT user_id FROM USERS WHERE id_karyawan = ?";
        $stmt = $conn->prepare($checkNIK);
        $stmt->bind_param("s", $id_karyawan);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "ID Karyawan sudah terdaftar.";
        }
        $stmt->close();
        
        // Jika tidak ada duplikasi, insert data
        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $insertQuery = "INSERT INTO USERS (nama_lengkap, id_karyawan, nomor_telepon, email, kata_sandi, is_active) 
                           VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sssss", $nama_lengkap, $id_karyawan, $nomor_telepon, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = true;
                // Redirect ke login setelah 2 detik
                header("refresh:2;url=login.php");
            } else {
                $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
            }
            $stmt->close();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-image: url('assets/img/Background.png');
        background-size: cover;
        background-position: center;
        margin: 0;
        padding: 0;
    }

    .main-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }

    .register-card {
        background: white;
        border-radius: 40px;
        width: 100%;
        max-width: 1100px;
        display: flex;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border: 10px solid white;
        padding: 0; 
    }

    .form-section {
        width: 50%;
        padding: 50px 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: white; 
    }

    .form-section h1 {
        font-weight: 800;
        font-size: 2.2rem;
        margin-bottom: 5px;
        color: #000;
    }

    .form-section p.subtitle {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        font-size: 0.85rem;
        margin-bottom: 8px;
        color: #333;
    }

    .form-control-custom {
        width: 100%;
        background-color: #f1f4f9;
        border: none;
        padding: 12px 15px;
        border-radius: 12px;
        font-size: 0.9rem;
        color: #333;
        box-sizing: border-box;
    }

    .btn-signup {
        background-color: #4a89dc;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 12px;
        font-weight: 700;
        width: 100%;
        margin-top: 15px;
        cursor: pointer;
        transition: 0.3s;
    }

    .btn-signup:hover {
        background-color: #357ebd;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 20px 0;
        color: #aaa;
        font-size: 0.8rem;
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #eee;
    }

    .divider span {
        padding: 0 10px;
    }

    .login-link {
        text-align: center;
        font-size: 0.85rem;
        color: #333;
    }

    .login-link a {
        color: #000;
        font-weight: 800;
        text-decoration: none;
    }

    .image-section {
        width: 50%;
        background-image: url('assets/img/Background.png');
        background-size: cover;
        background-position: center;
        position: relative;
        border-radius: 30px;
        margin: 10px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 60px;
        color: white;
    }

    .image-section::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0; top: 0;
        background: linear-gradient(to top, rgba(74, 137, 220, 0.4), transparent);
        border-radius: 30px;
        z-index: 1;
    }

    .text-overlay {
        position: relative;
        z-index: 2;
    }

    .text-overlay h2 {
        font-family: 'Playfair Display', serif;
        font-size: 2.8rem;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 20px;
    }

    .text-overlay p {
        font-size: 1.1rem;
        line-height: 1.5;
        opacity: 0.9;
    }

    @media (max-width: 992px) {
        .register-card { flex-direction: column-reverse; }
        .form-section, .image-section { width: 100%; }
        .image-section { height: 350px; padding: 30px; margin: 10px 10px 0 10px; }
    }
</style>

<div class="main-wrapper">
    <div class="register-card">
        <div class="form-section">
            <h1>Hello User !</h1>
            <p class="subtitle">Masukkan detail di bawah untuk membuat akun</p>

            <?php if ($success): ?>
                <div class="alert alert-success py-2 small">
                    Registrasi berhasil! Anda akan diarahkan ke halaman login...
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2 small">
                    <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control-custom" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control-custom" placeholder="Masukkan nomor telepon" required>
                </div>

                <div class="form-group">
                    <label>ID Karyawan</label>
                    <input type="text" name="nik" class="form-control-custom" placeholder="Masukkan ID Karyawan" required>
                </div>

                <div class="form-group">
                    <label>E-Mail</label>
                    <input type="email" name="email" class="form-control-custom" placeholder="Masukkan email" required>
                </div>

                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" class="form-control-custom" placeholder="Masukkan password (min. 6 karakter)" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Kata Sandi</label>
                    <input type="password" name="confirm_password" class="form-control-custom" placeholder="Konfirmasi password" required>
                </div>

                <button type="submit" class="btn-signup">Sign Up</button>
            </form>

            <div class="divider">
                <span>Or</span>
            </div>

            <p class="login-link">
                Sudah punya akun? <a href="login.php">Login Here</a>
            </p>
        </div>

        <div class="image-section">
            <div class="text-overlay">
                <h2>Setiap Langkah<br>Belajar Membawa<br>Perubahan</h2>
                <p>Mari berkembang bersama dan wujudkan potensi terbaikmu di ByteForge.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>