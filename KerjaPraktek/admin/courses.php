<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Manage Courses - Courseva";

$conn = getDBConnection();

// Filter
$status = $_GET['status'] ?? '';
$pengajar = $_GET['pengajar'] ?? '';

// Query courses
$query = "SELECT c.*, u.nama_lengkap as pengajar_nama
          FROM courses c
          LEFT JOIN users u ON c.pengajar_id = u.id
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($status)) {
    $query .= " AND c.status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($pengajar)) {
    $query .= " AND c.pengajar_id = ?";
    $params[] = $pengajar;
    $types .= "i";
}

$query .= " ORDER BY c.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$courses = $stmt->get_result();

// Ambil semua pengajar untuk filter
$pengajars = $conn->query("SELECT id, nama_lengkap FROM users WHERE role = 'pengajar' ORDER BY nama_lengkap");

// Delete course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_course'])) {
    $courseId = $_POST['course_id'];
    $query = "DELETE FROM courses WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $courseId);
    $stmt->execute();
    $stmt->close();
    
    $basePath = getBasePath();
    redirectWithMessage("{$basePath}/admin/courses.php", 'Course berhasil dihapus.', 'success');
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Courses</h2>
        <a href="<?php echo url('admin/create_course.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Course
        </a>
    </div>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="draft" <?php echo $status == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo $status == 'published' ? 'selected' : ''; ?>>Published</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="pengajar">
                            <option value="">Semua Pengajar</option>
                            <?php while ($p = $pengajars->fetch_assoc()): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo $pengajar == $p['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if ($courses && $courses->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Thumbnail</th>
                                <th>Judul</th>
                                <th>Pengajar</th>
                                <th>Status</th>
                                <th>Harga</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($course = $courses->fetch_assoc()): ?>
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
                                    <td><?php echo htmlspecialchars($course['pengajar_nama'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $course['status'] == 'published' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatRupiah($course['harga']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo url('admin/edit_course.php?id=' . $course['id']); ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline" 
                                                  onsubmit="return confirm('Yakin ingin menghapus course ini?');">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" name="delete_course" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada course ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

