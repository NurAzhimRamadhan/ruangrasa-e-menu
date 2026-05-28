<?php
require_once '../config/koneksi.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token()) {
        $error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok!';
    } elseif (strlen($password) < 8) {
        $error = 'Password minimal 8 karakter!';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password harus mengandung minimal 1 huruf besar!';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password harus mengandung minimal 1 huruf kecil!';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password harus mengandung minimal 1 angka!';
    } else {
        $stmt = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $foto = 'default.png';
            
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_file_size = 2 * 1024 * 1024; // 2MB
                
                $filename = $_FILES['foto']['name'];
                $file_size = $_FILES['foto']['size'];
                $file_tmp = $_FILES['foto']['tmp_name'];
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
                    $foto = uniqid() . '.' . $ext;
                    move_uploaded_file($file_tmp, '../img/' . $foto);
                }
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $koneksi->prepare("INSERT INTO users (nama, email, password, foto, role) VALUES (?, ?, ?, ?, 'user')");
            $stmt->bind_param("ssss", $nama, $email, $hashed_password, $foto);
            
            if ($stmt->execute()) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                error_log("Registration failed: " . $stmt->error);
                $error = 'Terjadi kesalahan saat registrasi!';
            }
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
    <title>Registrasi - Ruang Rasa</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="brand-title">Ruang Rasa</h1>
                <p class="auth-subtitle">Buat Akun Baru</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert-error">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm1 15H9v-2h2v2zm0-4H9V5h2v6z" fill="currentColor"/>
                </svg>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert-success">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 0C4.48 0 0 4.48 0 10s4.48 10 10 10 10-4.48 10-10S15.52 0 10 0zm-2 15l-5-5 1.41-1.41L8 12.17l7.59-7.59L17 6l-9 9z" fill="currentColor"/>
                </svg>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" enctype="multipart/form-data" class="auth-form">
                <?php csrf_field(); ?>
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" required placeholder="Nama lengkap Anda" value="<?php echo htmlspecialchars($_POST['nama'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="nama@email.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Min 8 karakter, huruf besar, kecil, angka">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password Anda">
                </div>
                
                <div class="form-group">
                    <label for="foto">Foto Profil (Opsional)</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                </div>
                
                <button type="submit" class="btn-primary">Daftar</button>
            </form>
            
            <div class="auth-footer">
                <p>Sudah punya akun? <a href="login.php" class="link-primary">Login di sini</a></p>
            </div>
        </div>
    </div>
</body>
</html>
