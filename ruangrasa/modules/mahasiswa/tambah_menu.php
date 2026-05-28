<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];

$error = '';
$success = '';

$kategori_result = $koneksi->query("SELECT * FROM kategori ORDER BY nama_kategori");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $nama_menu = trim($_POST['nama_menu'] ?? '');
        $harga = intval($_POST['harga'] ?? 0);
        $id_kategori = intval($_POST['id_kategori'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
    
    if (empty($nama_menu) || $harga <= 0 || $id_kategori <= 0 || empty($deskripsi)) {
        $error = 'Semua field harus diisi dengan benar!';
    } else {
        $gambar = 'default_menu.png';
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 2 * 1024 * 1024; // 2MB
            
            $filename = $_FILES['gambar']['name'];
            $file_size = $_FILES['gambar']['size'];
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            // Validate file size
            if ($file_size > $max_file_size) {
                $error = 'Ukuran file maksimal 2MB!';
            }
            // Validate extension
            elseif (!in_array($ext, $allowed_extensions)) {
                $error = 'Format file tidak didukung! Hanya JPG, PNG, GIF.';
            }
            // Validate MIME type
            elseif (!in_array(mime_content_type($file_tmp), $allowed_mime_types)) {
                $error = 'File bukan gambar valid!';
            }
            // Validate if it's really an image
            elseif (!getimagesize($file_tmp)) {
                $error = 'File bukan gambar valid!';
            }
            else {
                $gambar = 'menu_' . uniqid() . '.' . $ext;
                move_uploaded_file($file_tmp, '../../img/' . $gambar);
            }
        }
        
        $stmt = $koneksi->prepare("INSERT INTO menu (nama_menu, harga, id_kategori, gambar, deskripsi) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $nama_menu, $harga, $id_kategori, $gambar, $deskripsi);
        
        if ($stmt->execute()) {
            $success = 'Menu berhasil ditambahkan!';
        } else {
            error_log("Insert menu failed: " . $stmt->error);
            $error = 'Terjadi kesalahan saat menambahkan menu!';
        }
        $stmt->close();
    }
    } // End CSRF else
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Menu - Ruang Rasa</title>
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
            <a href="admin.php">Admin Panel</a> / <span>Tambah Menu</span>
        </div>

        <div class="form-container">
            <h1 class="form-title">Tambah Menu Baru</h1>
            
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
            
            <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="nama_menu">Nama Menu</label>
                    <input type="text" id="nama_menu" name="nama_menu" required placeholder="Contoh: Rendang Daging Sapi">
                </div>
                
                <div class="form-group">
                    <label for="id_kategori">Kategori</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">Pilih Kategori</option>
                        <?php while ($kat = $kategori_result->fetch_assoc()): ?>
                        <option value="<?php echo $kat['id']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="harga">Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" required placeholder="35000" min="1">
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" required placeholder="Deskripsi detail tentang menu ini..." rows="5"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="gambar">Gambar Menu</label>
                    <input type="file" id="gambar" name="gambar" accept="image/*">
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Simpan Menu</button>
                    <a href="admin.php" class="btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik.- Nur Azhim Ramadhan -</p>
    </footer>
</body>
</html>
