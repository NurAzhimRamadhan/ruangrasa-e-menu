<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

if (empty($type) || $id <= 0) {
    header('Location: admin.php');
    exit();
}

// Get item info untuk konfirmasi
$item_name = '';
$type_label = '';

if ($type === 'user') {
    if ($id == $_SESSION['user_id']) {
        header('Location: admin.php');
        exit();
    }
    $stmt = $koneksi->prepare("SELECT nama FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $item_name = $row['nama'];
        $type_label = 'pengguna';
    }
    $stmt->close();
} elseif ($type === 'kategori') {
    $stmt = $koneksi->prepare("SELECT nama_kategori FROM kategori WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $item_name = $row['nama_kategori'];
        $type_label = 'kategori';
    }
    $stmt->close();
} elseif ($type === 'menu') {
    $stmt = $koneksi->prepare("SELECT nama_menu FROM menu WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $item_name = $row['nama_menu'];
        $type_label = 'menu';
    }
    $stmt->close();
}

if (empty($item_name)) {
    header('Location: admin.php');
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Hapus - Ruang Rasa</title>
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
                <a href="admin.php" class="nav-link active">Admin Panel</a>
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
            <a href="admin.php">Admin Panel</a> / <span>Konfirmasi Hapus</span>
        </div>

        <div class="confirm-container">
            <div class="confirm-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <h1 class="confirm-title">Konfirmasi Penghapusan</h1>
            <p class="confirm-message">
                Apakah Anda yakin ingin menghapus <?php echo htmlspecialchars($type_label); ?> berikut?
            </p>
            <div class="confirm-item">
                <strong><?php echo htmlspecialchars($item_name); ?></strong>
            </div>
            <p class="confirm-warning">
                Tindakan ini tidak dapat dibatalkan dan data akan dihapus secara permanen.
            </p>
            <div class="confirm-actions">
                <a href="hapus.php?type=<?php echo urlencode($type); ?>&id=<?php echo $id; ?>&confirmed=1" class="btn-danger">Ya, Hapus</a>
                <a href="admin.php" class="btn-secondary">Batal</a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik.</p>
    </footer>
</body>
</html>
