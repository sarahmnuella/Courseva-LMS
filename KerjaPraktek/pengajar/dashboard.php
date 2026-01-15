<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "Dashboard Pengajar - Courseva";
$userId = $_SESSION['user_id'];

$conn = getDBConnection();

// Statistik
// Total course
$query = "SELECT COUNT(*) as total FROM courses WHERE pengajar_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalCourses = $result->fetch_assoc()['total'];
$stmt->close();

// Total peserta
$query = "SELECT COUNT(DISTINCT e.user_id) as total 
          FROM enrollments e
          INNER JOIN courses c ON e.course_id = c.id
          WHERE c.pengajar_id = ? AND e.status = 'verified'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$totalPeserta = $result->fetch_assoc()['total'];
$stmt->close();

// Course dengan rating tertinggi
$query = "SELECT c.*, AVG(cr.rating) as avg_rating
          FROM courses c
          LEFT JOIN course_ratings cr ON c.id = cr.course_id
          WHERE c.pengajar_id = ?
          GROUP BY c.id
          ORDER BY avg_rating DESC
          LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$topCourse = $result->fetch_assoc();
$stmt->close();

// List course
$query = "SELECT c.*, 
          COUNT(DISTINCT e.id) as total_enrollment
          FROM courses c
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'verified'
          WHERE c.pengajar_id = ?
          GROUP BY c.id
          ORDER BY c.created_at DESC
          LIMIT 6";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$myCourses = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Dashboard Pengajar</h2>
    
    <!-- Statistik -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Course</h5>
                    <h2 class="mb-0"><?php echo $totalCourses; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Peserta</h5>
                    <h2 class="mb-0"><?php echo $totalPeserta; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Course Terpopuler</h5>
                    <p class="mb-0">
                        <?php if ($topCourse): ?>
                            <?php echo htmlspecialchars($topCourse['judul']); ?>
                            <br>
                            <small>Rating: <?php echo number_format($topCourse['avg_rating'] ?? 0, 1); ?>/5</small>
                        <?php else: ?>
                            Belum ada
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- My Courses -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Course Saya</h5>
            <a href="/pengajar/create_course.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Buat Course Baru
            </a>
        </div>
        <div class="card-body">
            <?php if ($myCourses && $myCourses->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $myCourses->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php if ($course['thumbnail']): ?>
                                            <img src="/uploads/course_thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" 
                                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <i class="bi bi-image" style="font-size: 30px;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($course['judul']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $course['status'] == 'published' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $course['total_enrollment']; ?></td>
                                    <td>
                                        <a href="/pengajar/edit_course.php?id=<?php echo $course['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="/pengajar/courses.php" class="btn btn-outline-primary">Lihat Semua Course</a>
                </div>
            <?php else: ?>
                <p class="text-muted">Anda belum membuat course. <a href="/pengajar/create_course.php">Buat course pertama Anda</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

