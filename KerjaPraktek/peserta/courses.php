<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Browse Courses - Courseva";
$userId = $_SESSION['user_id'];

$conn = getDBConnection();

// Filter dan search
$search = $_GET['search'] ?? '';
$kategori = $_GET['kategori'] ?? '';
$pengajar = $_GET['pengajar'] ?? '';

// Query courses
$query = "SELECT c.*, u.nama_lengkap as pengajar_nama,
          COUNT(DISTINCT e.id) as total_enrollment
          FROM courses c
          LEFT JOIN users u ON c.pengajar_id = u.id
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'verified'
          WHERE c.status = 'published'";

$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (c.judul LIKE ? OR c.deskripsi LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($kategori)) {
    $query .= " AND c.kategori = ?";
    $params[] = $kategori;
    $types .= "s";
}

if (!empty($pengajar)) {
    $query .= " AND c.pengajar_id = ?";
    $params[] = $pengajar;
    $types .= "i";
}

$query .= " GROUP BY c.id ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$courses = $stmt->get_result();

// Ambil semua kategori untuk filter
$kategoriQuery = "SELECT DISTINCT kategori FROM courses WHERE status = 'published' AND kategori IS NOT NULL ORDER BY kategori";
$kategoris = $conn->query($kategoriQuery);

// Ambil semua pengajar untuk filter
$pengajarQuery = "SELECT DISTINCT u.id, u.nama_lengkap 
                  FROM users u
                  INNER JOIN courses c ON u.id = c.pengajar_id
                  WHERE c.status = 'published'
                  ORDER BY u.nama_lengkap";
$pengajars = $conn->query($pengajarQuery);

// View detail course
$viewCourseId = $_GET['view'] ?? null;
$courseDetail = null;
if ($viewCourseId) {
    $query = "SELECT c.*, u.nama_lengkap as pengajar_nama, u.email as pengajar_email
              FROM courses c
              LEFT JOIN users u ON c.pengajar_id = u.id
              WHERE c.id = ? AND c.status = 'published'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $viewCourseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courseDetail = $result->fetch_assoc();
    $stmt->close();
    
    // Cek apakah sudah enroll
    $isEnrolled = isEnrolled($userId, $viewCourseId);
    
    // Cek prasyarat
    $prasyarat = null;
    if ($courseDetail && $courseDetail['prasyarat_course_id']) {
        $query = "SELECT * FROM courses WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseDetail['prasyarat_course_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $prasyarat = $result->fetch_assoc();
        $stmt->close();
        
        // Cek apakah sudah selesai prasyarat
        if ($prasyarat) {
            $prasyaratCompleted = isAllModulesCompleted($userId, $prasyarat['id']);
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <?php if ($viewCourseId && $courseDetail): ?>
        <!-- Detail Course -->
        <div class="row">
            <div class="col-md-8">
                <h2><?php echo htmlspecialchars($courseDetail['judul']); ?></h2>
                <p class="text-muted">
                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($courseDetail['pengajar_nama']); ?>
                    <span class="ms-3"><i class="bi bi-clock"></i> <?php echo $courseDetail['durasi']; ?> jam</span>
                </p>
                
                <?php if ($courseDetail['thumbnail']): ?>
                    <img src="<?php echo uploadUrl('uploads/course_thumbnails/' . htmlspecialchars($courseDetail['thumbnail'])); ?>" 
                         class="img-fluid rounded mb-3" alt="<?php echo htmlspecialchars($courseDetail['judul']); ?>">
                <?php endif; ?>
                
                <h4>Deskripsi</h4>
                <p><?php echo nl2br(htmlspecialchars($courseDetail['deskripsi'])); ?></p>
                
                <?php if ($prasyarat): ?>
                    <div class="alert alert-info">
                        <h5>Prasyarat</h5>
                        <p>Anda harus menyelesaikan course: <strong><?php echo htmlspecialchars($prasyarat['judul']); ?></strong></p>
                        <?php if (isset($prasyaratCompleted) && $prasyaratCompleted): ?>
                            <span class="badge bg-success">Prasyarat sudah dipenuhi</span>
                        <?php elseif (isEnrolled($userId, $prasyarat['id'])): ?>
                            <a href="<?php echo url('peserta/learn.php?course_id=' . $prasyarat['id']); ?>" class="btn btn-sm btn-primary">Selesaikan Prasyarat</a>
                        <?php else: ?>
                            <a href="<?php echo url('peserta/courses.php?view=' . $prasyarat['id']); ?>" class="btn btn-sm btn-primary">Daftar Prasyarat</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="text-primary"><?php echo formatRupiah($courseDetail['harga']); ?></h4>
                        
                        <?php if ($isEnrolled): ?>
                            <a href="<?php echo url('peserta/learn.php?course_id=' . $courseDetail['id']); ?>" class="btn btn-success w-100 mb-2">
                                Lanjutkan Belajar
                            </a>
                        <?php else: ?>
                            <?php if ($prasyarat && (!isset($prasyaratCompleted) || !$prasyaratCompleted)): ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    Selesaikan Prasyarat Terlebih Dahulu
                                </button>
                            <?php else: ?>
                                <a href="<?php echo url('peserta/enroll.php?course_id=' . $courseDetail['id']); ?>" class="btn btn-primary w-100 mb-2">
                                    Daftar Course
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <hr>
                        <h6>Informasi Course</h6>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-clock"></i> Durasi: <?php echo $courseDetail['durasi']; ?> jam</li>
                            <li><i class="bi bi-person"></i> Pengajar: <?php echo htmlspecialchars($courseDetail['pengajar_nama']); ?></li>
                            <li><i class="bi bi-calendar"></i> Dibuat: <?php echo formatTanggal($courseDetail['created_at']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <a href="<?php echo url('peserta/courses.php'); ?>" class="btn btn-outline-primary">Kembali ke Daftar Course</a>
        </div>
    <?php else: ?>
        <!-- List Courses -->
        <h2 class="mb-4">Browse Courses</h2>
        
        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari course..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="kategori">
                                <option value="">Semua Kategori</option>
                                <?php while ($kat = $kategoris->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($kat['kategori']); ?>" 
                                            <?php echo $kategori == $kat['kategori'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($kat['kategori']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="pengajar">
                                <option value="">Semua Pengajar</option>
                                <?php while ($p = $pengajars->fetch_assoc()): ?>
                                    <option value="<?php echo $p['id']; ?>" 
                                            <?php echo $pengajar == $p['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- List Courses -->
        <div class="row g-4">
            <?php if ($courses && $courses->num_rows > 0): ?>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm">
                            <?php if ($course['thumbnail']): ?>
                                <img src="<?php echo uploadUrl('uploads/course_thumbnails/' . htmlspecialchars($course['thumbnail'])); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($course['judul']); ?>" 
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="bi bi-image text-white" style="font-size: 48px;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($course['judul']); ?></h5>
                                <p class="card-text text-muted small">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($course['pengajar_nama']); ?>
                                    <span class="ms-2"><i class="bi bi-clock"></i> <?php echo $course['durasi']; ?> jam</span>
                                </p>
                                <p class="card-text"><?php echo htmlspecialchars(substr($course['deskripsi'], 0, 100)) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-primary fw-bold"><?php echo formatRupiah($course['harga']); ?></span>
                                    <a href="<?php echo url('peserta/courses.php?view=' . $course['id']); ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">Tidak ada course yang ditemukan.</div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

