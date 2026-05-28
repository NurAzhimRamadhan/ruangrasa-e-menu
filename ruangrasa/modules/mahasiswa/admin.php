<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];
$current_page = basename($_SERVER['PHP_SELF']);

$total_sales = $koneksi->query("SELECT COALESCE(SUM(total_harga), 0) as total FROM pesanan")->fetch_assoc()['total'];
$total_orders = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status != 'selesai'")->fetch_assoc()['total'];
$total_users = $koneksi->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$users_result = $koneksi->query("SELECT * FROM users ORDER BY id DESC");
$kategori_result = $koneksi->query("SELECT * FROM kategori ORDER BY id");
$menu_result = $koneksi->query("SELECT m.*, k.nama_kategori FROM menu m JOIN kategori k ON m.id_kategori = k.id ORDER BY m.id DESC");
$orders_result = $koneksi->query("SELECT p.*, u.nama as nama_pemesan FROM pesanan p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
?>

<?php

$total_cart = 0;

if (isset($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $qty) {

        $total_cart += $qty;
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Ruang Rasa</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">Ruang Rasa</div>
<div class="nav-links">

    <a href="index.php"
       class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>">
        Menu
    </a>

    <a href="cart.php"
       class="nav-link <?= $current_page == 'cart.php' ? 'active' : '' ?>"
       style="position:relative;">

        🛒 Keranjang

        <?php if ($total_cart > 0): ?>

            <span
                style="
                    position:absolute;
                    top:-8px;
                    right:-18px;
                    background:#C41230;
                    color:white;
                    font-size:11px;
                    font-weight:bold;
                    min-width:20px;
                    height:20px;
                    border-radius:50px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    padding:0 6px;
                "
            >

                <?php echo $total_cart > 10 ? '10+' : $total_cart; ?>

            </span>

        <?php endif; ?>

    </a>

    <a href="orders.php"
       class="nav-link <?= $current_page == 'orders.php' ? 'active' : '' ?>">
        Pesanan Saya
    </a>

    <a href="about.php"
       class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>">
        Tentang Kami
    </a>

    <a href="admin.php"
       class="nav-link <?= $current_page == 'admin.php' ? 'active' : '' ?>">
        Admin Panel
    </a>

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
        <h1 class="page-title">Admin Control Panel</h1>
        
        <div class="analytics-grid">
            <div class="analytics-card">
                <div class="analytics-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <div class="analytics-content">
                    <p class="analytics-label">Total Penjualan</p>
                    <h3 class="analytics-value">Rp <?php echo number_format($total_sales, 0, ',', '.'); ?></h3>
                </div>
            </div>
            
            <div class="analytics-card">
                <div class="analytics-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </div>
                <div class="analytics-content">
                    <p class="analytics-label">Pesanan Aktif</p>
                    <h3 class="analytics-value"><?php echo $total_orders; ?></h3>
                </div>
            </div>
            
            <div class="analytics-card">
                <div class="analytics-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="analytics-content">
                    <p class="analytics-label">Total Pengguna</p>
                    <h3 class="analytics-value"><?php echo $total_users; ?></h3>
                </div>
            </div>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Manajemen Pengguna</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['nama']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="ubah.php?type=user_role&id=<?php echo $user['id']; ?>&role=<?php echo $user['role'] === 'admin' ? 'user' : 'admin'; ?>" class="btn-action btn-edit">Toggle Role</a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="hapus.php?type=user&id=<?php echo $user['id']; ?>" class="btn-action btn-delete">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Manajemen Kategori</h2>
                <a href="tambah_kategori.php" class="btn-primary">+ Tambah Kategori</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($kategori = $kategori_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $kategori['id']; ?></td>
                            <td><?php echo htmlspecialchars($kategori['nama_kategori']); ?></td>
                            <td class="action-buttons">
                                <a href="edit_kategori.php?id=<?php echo $kategori['id']; ?>" class="btn-action btn-edit">Edit</a>
                                <a href="hapus.php?type=kategori&id=<?php echo $kategori['id']; ?>" class="btn-action btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <div class="section-header">
                <h2 class="section-title">Manajemen Menu</h2>
                <a href="tambah_menu.php" class="btn-primary">+ Tambah Menu</a>
            </div>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($menu = $menu_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $menu['id']; ?></td>
                            <td><?php echo htmlspecialchars($menu['nama_menu']); ?></td>
                            <td><?php echo htmlspecialchars($menu['nama_kategori']); ?></td>
                            <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                            <td class="action-buttons">
                                <a href="edit_menu.php?id=<?php echo $menu['id']; ?>" class="btn-action btn-edit">Edit</a>
                                <a href="hapus.php?type=menu&id=<?php echo $menu['id']; ?>" class="btn-action btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-section">
            <h2 class="section-title">Monitor Pesanan Live</h2>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pemesan</th>
                            <th>Meja</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['nama_pemesan']); ?></td>
                            <td><?php echo $order['nomor_meja']; ?></td>
                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_text = '';
                                switch ($order['status']) {
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        $status_text = 'Menunggu';
                                        break;
                                    case 'dimasak':
                                        $status_class = 'status-dimasak';
                                        $status_text = 'Dimasak';
                                        break;
                                    case 'selesai':
                                        $status_class = 'status-selesai';
                                        $status_text = 'Selesai';
                                        break;
                                }
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                            <td class="action-buttons">
                                <?php if ($order['status'] === 'pending'): ?>
                                <a href="ubah.php?type=order&id=<?php echo $order['id']; ?>&status=dimasak" class="btn-action btn-edit">Masak</a>
                                <?php elseif ($order['status'] === 'dimasak'): ?>
                                <a href="ubah.php?type=order&id=<?php echo $order['id']; ?>&status=selesai" class="btn-action btn-success">Selesai</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik. - Nur Azhim Ramadhan -</p>
    </footer>
</body>
</html>
