<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

$pageTitle = "Login - ByteForge LMS";

// Cek jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: peserta/dashboard.php");
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username_email = sanitize($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username_email) || empty($password)) {
        $errors[] = "Email/ID Karyawan dan Password harus diisi.";
    } else {
        $conn = getDBConnection();
        
        // Cek apakah input berupa email atau id_karyawan
        $isEmail = filter_var($username_email, FILTER_VALIDATE_EMAIL);
        
        // Query yang benar untuk ByteForgeDB
        $query = "SELECT * FROM USERS WHERE ";
        if ($isEmail) {
            $query .= "email = ? AND is_active = 1";
        } else {
            $query .= "id_karyawan = ? AND is_active = 1";
        }
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username_email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi password menggunakan password_verify
            if (password_verify($password, $user['kata_sandi'])) {
                // Set session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['id_karyawan'] = $user['id_karyawan'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect ke dashboard
              header("Location: peserta/dashboard.php");
                exit();
            } else {
                $errors[] = "Password salah.";
            }
        } else {
            $errors[] = "Akun tidak ditemukan atau tidak aktif.";
        }
        $stmt->close();
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

    .login-container {
        padding: 80px 0; 
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        background: white;
        border-radius: 30px;
        overflow: hidden;
        border: none;
        max-width: 1000px;
        width: 100%;
        display: flex;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .login-sidebar {
        background-color: #79a6ff;
        background-image: url('assets/img/Background.png');
        background-size: cover;
        background-position: center;
        width: 50%;
        padding: 60px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        position: relative;
        color: white;
    }

    .login-sidebar h2 {
        font-family: 'Serif', 'Georgia', serif;
        font-size: 2.5rem;
        font-weight: bold;
        line-height: 1.2;
        margin-bottom: 20px;
        z-index: 2;
    }

    .login-sidebar p {
        font-size: 1.1rem;
        opacity: 0.9;
        z-index: 2;
    }

    .login-form-area {
        width: 50%;
        padding: 60px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .login-form-area h3 {
        font-weight: 800;
        font-size: 1.8rem;
        margin-bottom: 10px;
    }

    .form-control {
        background-color: #f5f5f5;
        border: none;
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    .btn-signin {
        background-color: #4a89dc;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 600;
        margin-top: 10px;
    }

    .btn-google {
        background-color: white;
        border: 1px solid #ddd;
        padding: 12px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
        color: #555;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 20px 0;
        color: #aaa;
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #eee;
    }

    .divider span {
        padding: 0 10px;
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .login-card { flex-direction: column; }
        .login-sidebar, .login-form-area { width: 100%; padding: 40px; }
        .login-sidebar { min-height: 300px; }
    }
</style>

<div class="container login-container">
    <div class="login-card">
        <div class="login-sidebar">
            <h2>Setiap Langkah Belajar Membawa Perubahan</h2>
            <p>Mari berkembang bersama dan wujudkan potensi terbaikmu di ByteForge.</p>
        </div>

        <div class="login-form-area">
            <h3>Welcome Back</h3>
            <p class="text-muted mb-4">Masukkan email atau ID Karyawan dan password Anda</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2 small">
                    <?php foreach ($errors as $error) echo htmlspecialchars($error) . '<br>'; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Email atau ID Karyawan</label>
                    <input type="text" name="username_email" class="form-control" placeholder="Masukkan email atau ID karyawan" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label small" for="remember">Ingat Saya</label>
                    </div>
                    <a href="#" class="small text-decoration-none">Lupa Password?</a>
                </div>

<div class="d-grid">
    <button type="submit" class="btn btn-signin">Sign In</button>
</div>
            </form>

            <div class="divider">
                <span>Or</span>
            </div>

            <button class="btn btn-google">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="20">
                Sign in with Google
            </button>

            <p class="text-center mt-4 small">
                Belum punya akun? <a href="register.php" class="fw-bold text-decoration-none text-dark">Sign Up</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>