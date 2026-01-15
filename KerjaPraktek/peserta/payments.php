<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requirePeserta();

$pageTitle = "Status Pembayaran - Courseva";
$userId = $_SESSION['user_id'];

$conn = getDBConnection();

// Ambil semua pembayaran user
$query = "SELECT p.*, e.course_id, c.judul as course_judul, c.thumbnail
          FROM payments p
          INNER JOIN enrollments e ON p.enrollment_id = e.id
          INNER JOIN courses c ON e.course_id = c.id
          WHERE e.user_id = ?
          ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$payments = $stmt->get_result();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Status Pembayaran</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if ($payments && $payments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Jumlah</th>
                                <th>Bukti Pembayaran</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($payment['thumbnail']): ?>
                                                <img src="/uploads/course_thumbnails/<?php echo htmlspecialchars($payment['thumbnail']); ?>" 
                                                     class="me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($payment['course_judul']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo formatRupiah($payment['amount']); ?></td>
                                    <td>
                                        <a href="/uploads/bukti_pembayaran/<?php echo htmlspecialchars($payment['bukti_pembayaran']); ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        switch ($payment['status']) {
                                            case 'verified':
                                                $statusClass = 'success';
                                                $statusText = 'Terverifikasi';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'danger';
                                                $statusText = 'Ditolak';
                                                break;
                                            default:
                                                $statusClass = 'warning';
                                                $statusText = 'Menunggu Verifikasi';
                                        }
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td><?php echo formatTanggal($payment['created_at'], true); ?></td>
                                    <td>
                                        <?php if ($payment['status'] == 'verified'): ?>
                                            <a href="/peserta/learn.php?course_id=<?php echo $payment['course_id']; ?>" 
                                               class="btn btn-sm btn-success">
                                                Akses Course
                                            </a>
                                        <?php elseif ($payment['status'] == 'rejected'): ?>
                                            <a href="/peserta/enroll.php?course_id=<?php echo $payment['course_id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                Upload Ulang
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Menunggu...</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada pembayaran.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

