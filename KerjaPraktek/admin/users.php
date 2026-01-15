<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Manage Users - Courseva";

$conn = getDBConnection();

// Filter
$role = $_GET['role'] ?? '';
$status = $_GET['status'] ?? '';

// Query users
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!empty($role)) {
    $query .= " AND role = ?";
    $params[] = $role;
    $types .= "s";
}

if (!empty($status)) {
    $query .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();

// Delete user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];
    
    // Jangan hapus user sendiri
    if ($userId != $_SESSION['user_id']) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        $basePath = getBasePath();
        redirectWithMessage("{$basePath}/admin/users.php", 'User berhasil dihapus.', 'success');
    } else {
        $basePath = getBasePath();
        redirectWithMessage("{$basePath}/admin/users.php", 'Tidak dapat menghapus akun sendiri.', 'error');
    }
}

// Toggle status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['toggle_status'])) {
    $userId = $_POST['user_id'];
    $newStatus = $_POST['new_status'];
    
    // Jangan nonaktifkan diri sendiri
    if ($userId != $_SESSION['user_id'] || $newStatus == 'active') {
        $query = "UPDATE users SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $newStatus, $userId);
        $stmt->execute();
        $stmt->close();
        
        $basePath = getBasePath();
        redirectWithMessage("{$basePath}/admin/users.php", 'Status user berhasil diupdate.', 'success');
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Users</h2>
        <a href="<?php echo url('admin/create_user.php'); ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah User
        </a>
    </div>
    
    <!-- Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="role">
                            <option value="">Semua Role</option>
                            <option value="peserta" <?php echo $role == 'peserta' ? 'selected' : ''; ?>>Peserta</option>
                            <option value="pengajar" <?php echo $role == 'pengajar' ? 'selected' : ''; ?>>Pengajar</option>
                            <option value="admin" <?php echo $role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
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
            <?php if ($users && $users->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : ($user['role'] == 'pengajar' ? 'primary' : 'success'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatTanggal($user['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="<?php echo url('admin/edit_user.php?id=' . $user['id']); ?>" 
                                               class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_status" 
                                                       value="<?php echo $user['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-toggle-<?php echo $user['status'] == 'active' ? 'off' : 'on'; ?>"></i>
                                                </button>
                                            </form>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" action="" class="d-inline" 
                                                      onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada user ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

