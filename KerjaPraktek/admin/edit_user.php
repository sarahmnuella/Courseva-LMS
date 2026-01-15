<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session_check.php';

requireAdmin();

$pageTitle = "Edit User - Courseva";
$userId = $_GET['id'] ?? null;

if (!$userId) {
    $basePath = getBasePath();
    redirectWithMessage("{$basePath}/admin/users.php", 'User tidak ditemukan.', 'error');
}

$conn = getDBConnection();

// Ambil detail user
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $basePath = getBasePath();
    redirectWithMessage("{$basePath}/admin/users.php", 'User tidak ditemukan.', 'error');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $nama_lengkap = sanitize($_POST['nama_lengkap'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $username = sanitize($_POST['username'] ?? '');
        $role = $_POST['role'] ?? 'peserta';
        $status = $_POST['status'] ?? 'active';
        $password = $_POST['password'] ?? '';
        
        if (empty($nama_lengkap)) {
            $errors[] = "Nama lengkap harus diisi.";
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email tidak valid.";
        }
        
        if (empty($username)) {
            $errors[] = "Username harus diisi.";
        }
        
        // Cek email unique (kecuali user ini)
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email sudah terdaftar.";
        }
        $stmt->close();
        
        // Cek username unique (kecuali user ini)
        $query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Username sudah terdaftar.";
        }
        $stmt->close();
        
        if (empty($errors)) {
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $errors[] = "Password minimal 8 karakter.";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET nama_lengkap = ?, email = ?, username = ?, password = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssssssi", $nama_lengkap, $email, $username, $hashedPassword, $role, $status, $userId);
                }
            } else {
                $query = "UPDATE users SET nama_lengkap = ?, email = ?, username = ?, role = ?, status = ?, updated_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("sssssi", $nama_lengkap, $email, $username, $role, $status, $userId);
            }
            
            if (empty($errors)) {
                if ($stmt->execute()) {
                    $stmt->close();
                    $basePath = getBasePath();
                    redirectWithMessage("{$basePath}/admin/users.php", 'User berhasil diupdate!', 'success');
                } else {
                    $errors[] = "Terjadi kesalahan saat menyimpan user.";
                }
                $stmt->close();
            }
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-4">
    <h2 class="mb-4">Edit User</h2>
    
    <div class="card">
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                           value="<?php echo htmlspecialchars($user['nama_lengkap']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="username" name="username" 
                           value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" class="form-control" id="password" name="password">
                    <small class="text-muted">Minimal 8 karakter</small>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="peserta" <?php echo $user['role'] == 'peserta' ? 'selected' : ''; ?>>Peserta</option>
                                <option value="pengajar" <?php echo $user['role'] == 'pengajar' ? 'selected' : ''; ?>>Pengajar</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

