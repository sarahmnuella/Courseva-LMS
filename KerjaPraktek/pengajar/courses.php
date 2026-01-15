<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePengajar();

$pageTitle = "My Courses - Courseva";
$userId = $_SESSION['user_id'];

$conn = getDBConnection();

// Ambil semua course pengajar
$query = "SELECT c.*, 
          COUNT(DISTINCT e.id) as total_enrollment
          FROM courses c
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'verified'
          WHERE c.pengajar_id = ?
          GROUP BY c.id
          ORDER BY c.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$courses = $stmt->get_result();

// Delete course
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_course'])) {
    $courseId = $_POST['course_id'];
    
    // Cek apakah course milik pengajar ini
    $query = "SELECT id FROM courses WHERE id = ? AND pengajar_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $courseId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Delete course (cascade akan menghapus modul, exam, dll)
        $query = "DELETE FROM courses WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $stmt->close();
        
        redirectWithMessage('/pengajar/courses.php', 'Course berhasil dihapus.', 'success');
    }
    $stmt->close();
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Courses</h2>
        <a href="/pengajar/create_course.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Course Baru
        </a>
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
                                <th>Status</th>
                                <th>Peserta</th>
                                <th>Harga</th>
                                <th>Tanggal Dibuat</th>
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
                                    <td>
                                        <span class="badge bg-<?php echo $course['status'] == 'published' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $course['total_enrollment']; ?></td>
                                    <td><?php echo formatRupiah($course['harga']); ?></td>
                                    <td><?php echo formatTanggal($course['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/pengajar/edit_course.php?id=<?php echo $course['id']; ?>" 
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
                <p class="text-muted">Anda belum membuat course. <a href="/pengajar/create_course.php">Buat course pertama Anda</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

