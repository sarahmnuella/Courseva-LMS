<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

$pageTitle = "Register - PT Artavista";

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    $basePath = getBasePath();
    header("Location: {$basePath}/" . $_SESSION['role'] . "/dashboard.php");
    exit();
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

    /* Container Putih Utama */
    .register-card {
        background: white;
        border-radius: 40px;
        width: 100%;
        max-width: 1100px;
        display: flex;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border: 10px solid white; /* Border putih tebal sesuai gambar */
        padding: 0; 
    }

    /* Sisi Kiri (Formulir) */
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

    /* Styling Input */
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

    /* Tombol Sign Up */
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

    /* Divider Or */
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

    /* Sisi Kanan (Background Sama dengan Body) */
    .image-section {
        width: 50%;
        /* Menggunakan gambar yang sama persis dengan body */
        background-image: url('assets/img/Background.png');
        background-size: cover;
        background-position: center;
        position: relative;
        border-radius: 30px; /* Lengkungan di dalam gambar agar sesuai mockup */
        margin: 10px; /* Memberi jarak sedikit agar border putih card terlihat */
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 60px;
        color: white;
    }

    /* Overlay gradasi agar teks terbaca tetap kontras */
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

    /* Responsive */
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
            <p class="subtitle">Enter below details to create an account</p>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control-custom" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="nomor_telepon" class="form-control-custom" placeholder="Enter your number Phone" required>
                </div>

                <div class="form-group">
                    <label>Nomor Induk Karyawan / Id Karyawan</label>
                    <input type="text" name="nik" class="form-control-custom" placeholder="Enter Your Id Karyawan" required>
                </div>

                <div class="form-group">
                    <label>E-Mail</label>
                    <input type="email" name="email" class="form-control-custom" placeholder="Enter your mail" required>
                </div>

                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" class="form-control-custom" placeholder="Enter password" required>
                </div>

                <div class="form-group">
                    <label>Konfirmasi Kata Sandi</label>
                    <input type="password" name="confirm_password" class="form-control-custom" placeholder="Confirm your password" required>
                </div>

                <button type="submit" class="btn-signup">Sign Up</button>
            </form>

            <div class="divider">
                <span>Or</span>
            </div>

            <p class="login-link">
                Already have an account ? <a href="login.php">Login Here</a>
            </p>
        </div>

        <div class="image-section">
            <div class="text-overlay">
                <h2>Setiap Langkah<br>Belajar Membawa<br>Perubahan</h2>
                <p>Mari berkembang bersama dan wujudkan potensi terbaikmu di PT Artavista.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>