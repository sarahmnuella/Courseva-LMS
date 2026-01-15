<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Reports - Courseva";

$conn = getDBConnection();

// Enrollment per course
$query = "SELECT c.judul, COUNT(e.id) as total_enrollment
          FROM courses c
          LEFT JOIN enrollments e ON c.id = e.course_id AND e.status = 'verified'
          GROUP BY c.id
          ORDER BY total_enrollment DESC";
$enrollmentReport = $conn->query($query);

// Pembayaran report
$query = "SELECT 
            COUNT(*) as total_payments,
            SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END) as total_verified,
            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as total_pending,
            SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END) as total_rejected
          FROM payments";
$paymentReport = $conn->query($query)->fetch_assoc();

// Completion rate
$query = "SELECT 
            COUNT(DISTINCT e.course_id) as total_courses,
            COUNT(DISTINCT CASE WHEN mp.status = 'completed' THEN e.course_id END) as completed_courses
          FROM enrollments e
          LEFT JOIN module_progress mp ON e.course_id = mp.course_id AND e.user_id = mp.user_id
          WHERE e.status = 'verified'";
$completionReport = $conn->query($query)->fetch_assoc();
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Reports</h2>
    
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Payments</h5>
                    <h2 class="mb-0"><?php echo $paymentReport['total_payments']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Verified Amount</h5>
                    <h2 class="mb-0"><?php echo formatRupiah($paymentReport['total_verified']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Amount</h5>
                    <h2 class="mb-0"><?php echo formatRupiah($paymentReport['total_pending']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Completion Rate</h5>
                    <h2 class="mb-0">
                        <?php 
                        $rate = $completionReport['total_courses'] > 0 
                            ? round(($completionReport['completed_courses'] / $completionReport['total_courses']) * 100) 
                            : 0;
                        echo $rate; 
                        ?>%
                    </h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollment per Course -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Enrollment per Course</h5>
        </div>
        <div class="card-body">
            <?php if ($enrollmentReport && $enrollmentReport->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course</th>
                                <th>Total Enrollment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $enrollmentReport->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td><?php echo $row['total_enrollment']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada data.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payment Report -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Payment Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-success">Verified</span></td>
                            <td><?php echo $paymentReport['total_payments']; ?></td>
                            <td><?php echo formatRupiah($paymentReport['total_verified']); ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>-</td>
                            <td><?php echo formatRupiah($paymentReport['total_pending']); ?></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">Rejected</span></td>
                            <td>-</td>
                            <td><?php echo formatRupiah($paymentReport['total_rejected']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

