<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Verifikasi Pembayaran - Courseva";

$conn = getDBConnection();

// Filter
$status = $_GET['status'] ?? 'pending';

// Query payments
$query = "SELECT p.*, e.user_id, e.course_id, u.nama_lengkap, u.email, c.judul as course_judul
          FROM payments p
          INNER JOIN enrollments e ON p.enrollment_id = e.id
          INNER JOIN users u ON e.user_id = u.id
          INNER JOIN courses c ON e.course_id = c.id
          WHERE p.status = ?
          ORDER BY p.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $status);
$stmt->execute();
$payments = $stmt->get_result();

// Verify/Reject payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentId = $_POST['payment_id'];
    $action = $_POST['action'];
    $notes = sanitize($_POST['notes'] ?? '');
    
    if ($action == 'verify') {
        // Update payment status
        $query = "UPDATE payments SET status = 'verified', verified_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $stmt->close();
        
        // Get enrollment_id
        $query = "SELECT enrollment_id FROM payments WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $payment = $result->fetch_assoc();
        $stmt->close();
        
        // Update enrollment status
        $query = "UPDATE enrollments SET status = 'verified' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $payment['enrollment_id']);
        $stmt->execute();
        $stmt->close();
        
        $basePath = getBasePath();
        redirectWithMessage("{$basePath}/admin/payments.php", 'Pembayaran berhasil diverifikasi!', 'success');
    } elseif ($action == 'reject') {
        // Update payment status
        $query = "UPDATE payments SET status = 'rejected', notes = ?, verified_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $notes, $paymentId);
        $stmt->execute();
        $stmt->close();
        
        $basePath = getBasePath();
        redirectWithMessage("{$basePath}/admin/payments.php", 'Pembayaran ditolak.', 'success');
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Verifikasi Pembayaran</h2>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="<?php echo url('admin/payments.php?status=pending'); ?>" 
                   class="btn btn-<?php echo $status == 'pending' ? 'primary' : 'outline-primary'; ?>">
                    Pending
                </a>
                <a href="<?php echo url('admin/payments.php?status=verified'); ?>" 
                   class="btn btn-<?php echo $status == 'verified' ? 'success' : 'outline-success'; ?>">
                    Verified
                </a>
                <a href="<?php echo url('admin/payments.php?status=rejected'); ?>" 
                   class="btn btn-<?php echo $status == 'rejected' ? 'danger' : 'outline-danger'; ?>">
                    Rejected
                </a>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <?php if ($payments && $payments->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Peserta</th>
                                <th>Course</th>
                                <th>Jumlah</th>
                                <th>Bukti Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($payment = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['nama_lengkap']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($payment['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($payment['course_judul']); ?></td>
                                    <td><?php echo formatRupiah($payment['amount']); ?></td>
                                    <td>
                                        <a href="<?php echo uploadUrl('uploads/bukti_pembayaran/' . htmlspecialchars($payment['bukti_pembayaran'])); ?>" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                    <td><?php echo formatTanggal($payment['created_at'], true); ?></td>
                                    <td>
                                        <?php if ($payment['status'] == 'pending'): ?>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#verifyModal<?php echo $payment['id']; ?>">
                                                <i class="bi bi-check-circle"></i> Verify
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#rejectModal<?php echo $payment['id']; ?>">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                            
                                            <!-- Verify Modal -->
                                            <div class="modal fade" id="verifyModal<?php echo $payment['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Verifikasi Pembayaran</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <div class="modal-body">
                                                                <p>Yakin ingin memverifikasi pembayaran ini?</p>
                                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                                <input type="hidden" name="action" value="verify">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">Verifikasi</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Reject Modal -->
                                            <div class="modal fade" id="rejectModal<?php echo $payment['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Tolak Pembayaran</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="">
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="notes" class="form-label">Alasan (Opsional)</label>
                                                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                                                </div>
                                                                <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                                                <input type="hidden" name="action" value="reject">
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-<?php echo $payment['status'] == 'verified' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($payment['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada pembayaran dengan status ini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

