<?php
// Pastikan BASE_PATH sudah didefinisikan
if (!defined('BASE_PATH')) {
    // Deteksi folder project dari SCRIPT_NAME
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $scriptDir = str_replace('\\', '/', $scriptDir);
    $scriptDir = trim($scriptDir, '/');
    
    if (empty($scriptDir)) {
        define('BASE_PATH', '/KerjaPraktek');
    } else {
        // Ambil folder terakhir
        $parts = explode('/', $scriptDir);
        define('BASE_PATH', '/' . $parts[0]);
    }
}

if (!isset($pageTitle)) {
    $pageTitle = "Courseva - Platform Pembelajaran Online";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --nav-bg: #c2dcff; 
            --primary-blue: #3498db;
            --text-dark: #2c3e50;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0;
            padding: 0;
        }

        .navbar-custom {
            background-color: var(--nav-bg);
            padding: 20px 0; /* Padding lebih tebal untuk kesan premium */
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000; /* Memastikan navbar di atas konten lain */
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Logo lebih besar dan posisi lebih ke kiri */
        .navbar-brand img {
            height: 55px; /* Ukuran ditingkatkan dari 40px */
            transition: transform 0.3s ease;
        }

        .navbar-brand img:hover {
            transform: scale(1.05);
        }

        /* Menu Navigasi Tengah */
        .nav-link-custom {
            color: var(--text-dark) !important;
            font-weight: 600; /* Font lebih tebal */
            margin: 0 20px;
            font-size: 1.05rem;
            transition: 0.3s;
        }

        .nav-link-custom:hover {
            color: var(--primary-blue) !important;
        }

        /* Tombol Login & Sign Up */
        .btn-login {
            color: var(--primary-blue) !important;
            font-weight: 700;
            text-decoration: none;
            margin-right: 25px;
            font-size: 1rem;
        }

        .btn-signup {
            background-color: #349aff; 
            color: white !important;
            font-weight: 700;
            padding: 12px 30px; /* Tombol lebih besar */
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(52, 154, 255, 0.2);
        }

        .btn-signup:hover {
            background-color: #2185d0;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 154, 255, 0.3);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid px-5">
        
        <a class="navbar-brand" href="index.php">
            <img src="assets/img/Logo Artavista.png" alt="Logo Artavista"> 
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="courses.php">Course</a></li>
                <li class="nav-item"><a class="nav-link nav-link-custom" href="faq.php">FAQ</a></li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="btn-signup">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Login</a>
                    <a href="register.php" class="btn-signup">Sign up</a>
                <?php endif; ?>
            </div>
        </div>

    </div>
</nav>