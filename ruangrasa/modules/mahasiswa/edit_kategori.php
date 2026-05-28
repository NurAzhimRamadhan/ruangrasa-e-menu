<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$kategori_id = intval($_GET['id'] ?? 0);

if ($kategori_id === 0) {
    header('Location: admin.php');
    exit();
}

$stmt = $koneksi->prepare("SELECT * FROM kategori WHERE id = ?");
$stmt->bind_param("i", $kategori_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: admin.php');
    exit();
}

$kategori = $result->fetch_assoc();
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');
        
        if (empty($nama_kategori)) {
            $error = 'Nama kategori harus diisi!';
        } else {
            $stmt = $koneksi->prepare("UPDATE kategori SET nama_kategori = ? WHERE id = ?");
            $stmt->bind_param("si", $nama_kategori, $kategori_id);
            
            if ($stmt->execute()) {
                $success = 'Kategori berhasil diperbarui!';
                $kategori['nama_kategori'] = $nama_kategori;
            } else {
                error_log("Update kategori failed: " . $stmt->error);
                $error = 'Terjadi kesalahan saat memperbarui kategori!';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Ruang Rasa</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">Ruang Rasa</div>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Menu</a>
                <a href="orders.php" class="nav-link">Pesanan Saya</a>
                <a href="about.php" class="nav-link">Tentang Kami</a>
                <a href="admin.php" class="nav-link">Admin Panel</a>
            </div>
            <div class="nav-profile">
                <img src="<?php echo htmlspecialchars(profile_img($foto)); ?>" alt="Profile" class="profile-img">
                <span class="profile-text">
                    Selamat datang, <?php echo htmlspecialchars($nama); ?>! (<?php echo ucfirst($role); ?>)
                </span>
                <a href="../../auth/logout.php" class="btn-logout">Logout</a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="breadcrumb">
            <a href="admin.php">Admin Panel</a> / <span>Edit Kategori</span>
        </div>

        <div class="form-container">
            <h1 class="form-title">Edit Kategori</h1>
            
            <?php if ($error): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="admin-form">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori</label>
                    <input type="text" id="nama_kategori" name="nama_kategori" required value="<?php echo htmlspecialchars($kategori['nama_kategori']); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Kategori</button>
                    <a href="admin.php" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik.</p>
    </footer>
</body>
</html>
