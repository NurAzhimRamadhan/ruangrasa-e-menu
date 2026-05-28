<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$menu_id = intval($_GET['id'] ?? 0);

if ($menu_id === 0) {
    header('Location: index.php');
    exit();
}

$stmt = $koneksi->prepare("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id WHERE m.id = ?");
$stmt->bind_param("i", $menu_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$menu = $result->fetch_assoc();
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($menu['nama_menu']); ?> - Ruang Rasa</title>
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
                <?php if ($role === 'admin'): ?>
                <a href="admin.php" class="nav-link">Admin Panel</a>
                <?php endif; ?>
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
            <a href="index.php">Menu</a> / <span><?php echo htmlspecialchars($menu['nama_menu']); ?></span>
        </div>

        <div class="detail-container">
            <div class="detail-image">
                <img src="<?php echo htmlspecialchars(menu_img($menu['gambar'])); ?>" alt="<?php echo htmlspecialchars($menu['nama_menu']); ?>">
            </div>
            <div class="detail-content">
                <span class="detail-badge"><?php echo htmlspecialchars($menu['nama_kategori']); ?></span>
                <h1 class="detail-title"><?php echo htmlspecialchars($menu['nama_menu']); ?></h1>
                <div class="detail-price">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></div>
                <div class="detail-divider"></div>
                <h3 class="detail-section-title">Deskripsi Hidangan</h3>
                <p class="detail-desc"><?php echo nl2br(htmlspecialchars($menu['deskripsi'])); ?></p>
                <div class="detail-actions">
                    <a href="index.php" class="btn-secondary">Kembali ke Menu</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik.</p>
    </footer>
</body>
</html>
