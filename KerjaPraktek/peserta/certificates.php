<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Sertifikat Saya - Courseva";
$userId = $_SESSION['user_id'];

$conn = getDBConnection();

// Ambil semua sertifikat
$query = "SELECT c.*, co.judul as course_judul, co.thumbnail
          FROM certificates c
          INNER JOIN courses co ON c.course_id = co.id
          WHERE c.user_id = ?
          ORDER BY c.issued_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$certificates = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Sertifikat Saya</h2>
    
    <?php if ($certificates && $certificates->num_rows > 0): ?>
        <div class="row g-4">
            <?php while ($cert = $certificates->fetch_assoc()): ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <?php if ($cert['thumbnail']): ?>
                                    <img src="/uploads/course_thumbnails/<?php echo htmlspecialchars($cert['thumbnail']); ?>" 
                                         class="me-3" style="width: 80px; height: 80px; object-fit: cover; border-radius: 4px;">
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($cert['course_judul']); ?></h5>
                                    <p class="text-muted small mb-0">
                                        Certificate Number: <?php echo htmlspecialchars($cert['certificate_number']); ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        Issued: <?php echo formatTanggal($cert['issued_at']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="/uploads/certificates/<?php echo basename($cert['file_path']); ?>" 
                                   target="_blank" class="btn btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                <a href="/uploads/certificates/<?php echo basename($cert['file_path']); ?>" 
                                   download class="btn btn-success">
                                    <i class="bi bi-download"></i> Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <h5>Belum ada sertifikat</h5>
            <p>Anda akan mendapatkan sertifikat setelah menyelesaikan course dan lulus exam.</p>
            <a href="/peserta/courses.php" class="btn btn-primary">Browse Courses</a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

