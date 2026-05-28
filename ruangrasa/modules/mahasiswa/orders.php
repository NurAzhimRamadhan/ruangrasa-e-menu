<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$current_page = basename($_SERVER['PHP_SELF']);
$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];

$query = "
            SELECT 
                p.*,
                u.nama AS nama_pemesan
            FROM pesanan p
            JOIN users u
                ON p.user_id = u.id
            WHERE p.user_id = ?
            ORDER BY p.id DESC
        ";
$stmt = $koneksi->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Ruang Rasa</title>
    <link rel="stylesheet" href="../../style.css">
</head>
<body>

<?php

$total_cart = 0;

if (isset($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $qty) {

        $total_cart += $qty;
    }
}

?>

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

                <?php if ($role === 'admin'): ?>

                    <a href="admin.php"
                    class="nav-link <?= $current_page == 'admin.php' ? 'active' : '' ?>">
                        Admin Panel
                    </a>

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
        <h1 class="page-title">Pesanan Saya</h1>
        
        <?php if ($result->num_rows > 0): ?>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Nomor Meja</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                        <th>Waktu Pesan</th>
                    </tr>
                </thead>
                <tbody>

<?php while ($order = $result->fetch_assoc()): ?>

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
        $status_text = 'Sedang Dimasak';
        break;

    case 'selesai':
        $status_class = 'status-selesai';
        $status_text = 'Selesai';
        break;
}

$pesanan_id = $order['id'];

$detail_query = mysqli_query(
    $koneksi,
    "
    SELECT 
        d.*,
        m.nama_menu
    FROM detail_pesanan d
    JOIN menu m
        ON d.menu_id = m.id
    WHERE d.pesanan_id = $pesanan_id
    "
);

?>

<tr>

    <td>
        #<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($order['nomor_meja']); ?>
    </td>

    <td>
        Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?>
    </td>

    <td>
        <span class="status-badge <?php echo $status_class; ?>">
            <?php echo $status_text; ?>
        </span>
    </td>

    <td>
        <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
    </td>

</tr>

<tr>

    <td colspan="5">

        <div
            style="
                padding:15px;
                background:#fafafa;
                border-radius:10px;
            "
        >

            <strong style="color:#C41230;">
                Detail Menu:
            </strong>

            <ul
                style="
                    margin-top:10px;
                    padding-left:20px;
                    line-height:1.8;
                "
            >

                <?php while($detail = mysqli_fetch_assoc($detail_query)): ?>

                    <li>

                        <?php echo htmlspecialchars($detail['nama_menu']); ?>

                        x <?php echo $detail['qty']; ?>

                    </li>

                <?php endwhile; ?>

            </ul>

        </div>

    </td>

</tr>

<?php endwhile; ?>

            </ul>

        </div>

    </td>

</tr>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>Anda belum memiliki pesanan.</p>
            <a href="index.php" class="btn-primary">Mulai Memesan</a>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik.- Nur Azhim Ramadhan -</p>
    </footer>
</body>
</html>
