<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Dashboard Admin - Courseva";

$conn = getDBConnection();

// Statistik
// Total peserta
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'peserta' AND status = 'active'";
$result = $conn->query($query);
$totalPeserta = $result->fetch_assoc()['total'];

// Total pengajar
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'pengajar' AND status = 'active'";
$result = $conn->query($query);
$totalPengajar = $result->fetch_assoc()['total'];

// Total courses
$query = "SELECT COUNT(*) as total FROM courses";
$result = $conn->query($query);
$totalCourses = $result->fetch_assoc()['total'];

// Total enrollment
$query = "SELECT COUNT(*) as total FROM enrollments WHERE status = 'verified'";
$result = $conn->query($query);
$totalEnrollment = $result->fetch_assoc()['total'];

// Pending payments
$query = "SELECT COUNT(*) as total FROM payments WHERE status = 'pending'";
$result = $conn->query($query);
$pendingPayments = $result->fetch_assoc()['total'];
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Dashboard Admin</h2>
    
    <!-- Statistik -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Peserta</h5>
                    <h2 class="mb-0"><?php echo $totalPeserta; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Pengajar</h5>
                    <h2 class="mb-0"><?php echo $totalPengajar; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Courses</h5>
                    <h2 class="mb-0"><?php echo $totalCourses; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Enrollment</h5>
                    <h2 class="mb-0"><?php echo $totalEnrollment; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Pending Payments</h5>
                </div>
                <div class="card-body">
                    <h2><?php echo $pendingPayments; ?></h2>
                    <p class="text-muted">Pembayaran yang menunggu verifikasi</p>
                    <a href="<?php echo url('admin/payments.php'); ?>" class="btn btn-danger">Verifikasi Pembayaran</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Quick Links</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?php echo url('admin/users.php'); ?>" class="btn btn-primary">Manage Users</a>
                        <a href="<?php echo url('admin/courses.php'); ?>" class="btn btn-primary">Manage Courses</a>
                        <a href="<?php echo url('admin/reports.php'); ?>" class="btn btn-primary">View Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

