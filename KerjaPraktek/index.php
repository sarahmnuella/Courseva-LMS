<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
session_start();

$pageTitle = "ByteForge Learning Platform";

// Ambil course populer dari database
$conn = getDBConnection();

// Query yang sudah diperbaiki untuk ByteForgeDB
$query = "SELECT c.*, 
          COUNT(DISTINCT ucp.progress_id) as total_enrollment
          FROM COURSES c
          LEFT JOIN USER_COURSE_PROGRESS ucp ON c.course_id = ucp.course_id 
              AND ucp.status IN ('in_progress', 'completed')
          WHERE c.is_published = 1
          GROUP BY c.course_id
          ORDER BY total_enrollment DESC, c.created_at DESC
          LIMIT 6";

$popularCourses = $conn->query($query);
?>

<?php include 'includes/header.php'; ?>

<style>
    :root {
        --primary-blue: #2196f3;
        --bg-light-blue: #a5c9ff;
        --card-blue: #c2dcff;
        --text-dark: #333333;
    }

    body {
        font-family: 'Inter', sans-serif;
    }

    /* Hero Section */
    .hero-wrapper {
        background-color: var(--bg-light-blue);
        padding: 80px 0;
    }

    .hero-card {
        background-color: var(--card-blue);
        border-radius: 40px;
        padding: 60px;
        border: none;
        position: relative;
        overflow: hidden;
    }

    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        color: #444;
        line-height: 1.1;
        margin-bottom: 25px;
    }

    .hero-subtitle {
        font-size: 1.2rem;
        color: #555;
        margin-bottom: 40px;
        max-width: 500px;
    }

    .btn-register-main {
        background-color: var(--primary-blue);
        color: white;
        padding: 12px 40px;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        transition: 0.3s;
    }

    .btn-register-main:hover {
        background-color: #1976d2;
        transform: translateY(-2px);
    }

    /* Course Cards */
    .course-card {
        border: none;
        border-radius: 20px;
        transition: 0.3s;
        overflow: hidden;
    }

    .course-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }

    /* Footer Styles */
    .footer-section {
        background-color: #ffffff;
        padding: 80px 0 40px 0;
        border-top: 1px solid #eee;
    }

    .footer-heading {
        font-weight: 700;
        margin-bottom: 25px;
        font-size: 1.1rem;
    }

    .footer-link {
        color: #6c757d;
        text-decoration: none;
        font-size: 0.95rem;
        display: block;
        margin-bottom: 12px;
    }

    .footer-link:hover {
        color: var(--primary-blue);
    }

    .newsletter-input {
        background-color: #e9ecef;
        border: none;
        padding: 12px 20px;
        border-radius: 8px 0 0 8px;
    }

    .newsletter-btn {
        background-color: #e9ecef;
        border: none;
        padding: 0 15px;
        border-radius: 0 8px 8px 0;
        color: #555;
    }

    .social-icon {
        width: 35px;
        height: 35px;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #333;
        margin-right: 10px;
        text-decoration: none;
    }
</style>

<section class="hero-wrapper">
    <div class="container">
        <div class="hero-card">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1 class="hero-title">Belajar, Berkembang,<br>Berkarya Bersama</h1>
                    <p class="hero-subtitle">
                        Platform pembelajaran ByteForge untuk pengembangan karyawan.
                    </p>
                    <a href="<?php echo url('register.php'); ?>" class="btn btn-register-main">Register</a>
                </div>
                <div class="col-lg-5 text-center d-none d-lg-block">
                    <img src="assets/img/ilustrasi.png" alt="Learning Illustration" class="img-fluid" style="max-height: 400px;">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: #a5c9ff;">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h2 class="fw-bold">Course Tersedia</h2>
                <p class="text-muted">Tingkatkan kompetensi Anda dengan materi pilihan.</p>
            </div>
            <a href="<?php echo url('peserta/courses.php'); ?>" class="btn btn-outline-primary rounded-pill px-4">Lihat Semua</a>
        </div>

        <div class="row g-4">
            <?php if ($popularCourses && $popularCourses->num_rows > 0): ?>
                <?php while ($course = $popularCourses->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100 course-card shadow-sm">
                            <?php if (!empty($course['thumbnail_url'])): ?>
                                <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                                     class="card-img-top" alt="Course Thumbnail" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="bi bi-journal-code text-secondary" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body p-4">
                                <h5 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($course['duration_hours']); ?> Jam
                                    <span class="ms-3">
                                        <i class="bi bi-bar-chart me-1"></i> 
                                        <?php 
                                            $levelMap = [
                                                'beginner' => 'Pemula',
                                                'intermediate' => 'Menengah',
                                                'advanced' => 'Lanjutan'
                                            ];
                                            echo $levelMap[$course['level']] ?? 'Pemula';
                                        ?>
                                    </span>
                                </p>
                                <p class="card-text text-muted small" style="height: 60px; overflow: hidden;">
                                    <?php echo htmlspecialchars(substr($course['course_description'], 0, 100)); ?>...
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <span class="text-primary fw-bold">
                                        <i class="bi bi-people-fill me-1"></i>
                                        <?php echo $course['total_enrollment']; ?> Peserta
                                    </span>
                                    <a href="<?php echo url('peserta/courses.php?view=' . $course['course_id']); ?>" 
                                       class="btn btn-sm btn-primary px-3 rounded-pill">Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <img src="assets/img/empty.svg" alt="Empty" style="width: 150px;" class="mb-3 opacity-50">
                    <p class="text-muted">Belum ada course yang dipublikasikan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>