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

$search = $_GET['search'] ?? '';

if ($search) {

    $query = "SELECT m.*, k.nama_kategori 
              FROM menu m
              JOIN kategori k ON m.id_kategori = k.id
              WHERE m.nama_menu LIKE ?
              OR k.nama_kategori LIKE ?
              ORDER BY k.id, m.nama_menu";

    $stmt = $koneksi->prepare($query);

    $search_param = "%$search%";

    $stmt->bind_param("ss", $search_param, $search_param);

    $stmt->execute();

    $search_result = $stmt->get_result();
}

$kategori_query = $koneksi->query("SELECT * FROM kategori ORDER BY id");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nomor_meja'])) {

    if (verify_csrf_token()) {

        $nomor_meja = intval($_POST['nomor_meja']);

        $total_harga = 0;

        $stmt = $koneksi->prepare("
            INSERT INTO pesanan 
            (user_id, nomor_meja, total_harga, status)
            VALUES (?, ?, ?, 'pending')
        ");

        $stmt->bind_param(
            "iii",
            $user_id,
            $nomor_meja,
            $total_harga
        );

        if ($stmt->execute()) {

            $pesan_sukses = true;

        } else {

            error_log("Insert pesanan failed: " . $stmt->error);
        }

        $stmt->close();

    } else {

        $pesan_error = 'Token keamanan tidak valid. Silakan refresh halaman.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruang Rasa - E-Menu Digital</title>

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

    <!-- NAVBAR -->
    <nav class="navbar">

        <div class="nav-container">

            <div class="nav-brand">
                Ruang Rasa
            </div>

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

                <img
                    src="<?php echo htmlspecialchars(profile_img($foto)); ?>"
                    alt="Profile"
                    class="profile-img"
                >

                <span class="profile-text">
                    Selamat datang,
                    <?php echo htmlspecialchars($nama); ?>!
                    (<?php echo ucfirst($role); ?>)
                </span>

                <a href="../../auth/logout.php" class="btn-logout">
                    Logout
                </a>

            </div>

        </div>

    </nav>

    <!-- MAIN -->
    <div class="main-container">

        <!-- HERO -->
                <?php if (isset($_GET['tambah']) && $_GET['tambah'] == 'success'): ?>

            <div
                style="
                    background:#d4edda;
                    color:#155724;
                    padding:16px 20px;
                    border-radius:12px;
                    margin-bottom:20px;
                    border-left:5px solid #28a745;
                    font-weight:600;
                "
            >
                ✅ Menu berhasil ditambahkan ke keranjang
            </div>

        <?php endif; ?>
        <div class="hero-section">

            <h1 class="hero-title">
                Jelajahi Cita Rasa Nusantara
            </h1>

            <p class="hero-subtitle">
                Hidangan otentik dengan kualitas premium dan rempah pilihan
            </p>

            <div class="search-container">

                <form method="GET" action="">

                    <input
                        type="text"
                        name="search"
                        placeholder="Cari menu favorit Anda..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="search-input"
                    >

                    <button type="submit" class="search-btn">
                        Cari
                    </button>

                </form>

            </div>

        </div>

        <!-- SEARCH RESULT -->
        <?php if ($search): ?>

            <div class="search-results">

                <h2 class="section-title">
                    Hasil Pencarian:
                    "<?php echo htmlspecialchars($search); ?>"
                </h2>

                <div class="menu-grid">

                    <?php while ($item = $search_result->fetch_assoc()): ?>

                        <div class="menu-card">

                            <div class="menu-img-wrapper">

                                <img
                                    src="<?php echo htmlspecialchars(menu_img($item['gambar'])); ?>"
                                    alt="<?php echo htmlspecialchars($item['nama_menu']); ?>"
                                    class="menu-img"
                                >

                                <span class="menu-badge">
                                    <?php echo htmlspecialchars($item['nama_kategori']); ?>
                                </span>

                            </div>

                            <div class="menu-content">

                                <h3 class="menu-title">
                                    <?php echo htmlspecialchars($item['nama_menu']); ?>
                                </h3>

                                <p class="menu-desc">
                                    <?php echo substr(htmlspecialchars($item['deskripsi']), 0, 100); ?>...
                                </p>

                                <div class="menu-footer">

                                    <span class="menu-price">
                                        Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                    </span>

                                    <div style="display:flex; gap:10px;">

                                        <a
                                            href="detail.php?id=<?php echo $item['id']; ?>"
                                            class="btn-detail"
                                        >
                                            Lihat Detail
                                        </a>

                                        <form method="POST" action="cart.php">

                                            <input
                                                type="hidden"
                                                name="menu_id"
                                                value="<?php echo $item['id']; ?>"
                                            >

                                            <button type="submit" class="btn-primary">
                                                + Keranjang
                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </div>

        <?php else: ?>

            <!-- CATEGORY -->
            <?php while ($kategori = $kategori_query->fetch_assoc()): ?>

                <?php

                $kategori_id = $kategori['id'];

                $stmt_menu = $koneksi->prepare("
                    SELECT * FROM menu
                    WHERE id_kategori = ?
                    ORDER BY nama_menu
                ");

                $stmt_menu->bind_param("i", $kategori_id);

                $stmt_menu->execute();

                $menu_query = $stmt_menu->get_result();

                ?>

                <?php if ($menu_query->num_rows > 0): ?>

                    <div class="category-section">

                        <h2 class="section-title">
                            <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                        </h2>

                        <div class="menu-grid">

                            <?php while ($item = $menu_query->fetch_assoc()): ?>

                                <div class="menu-card">

                                    <div class="menu-img-wrapper">

                                        <img
                                            src="<?php echo htmlspecialchars(menu_img($item['gambar'])); ?>"
                                            alt="<?php echo htmlspecialchars($item['nama_menu']); ?>"
                                            class="menu-img"
                                        >

                                        <span class="menu-badge">
                                            <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                        </span>

                                    </div>

                                    <div class="menu-content">

                                        <h3 class="menu-title">
                                            <?php echo htmlspecialchars($item['nama_menu']); ?>
                                        </h3>

                                        <p class="menu-desc">
                                            <?php echo substr(htmlspecialchars($item['deskripsi']), 0, 100); ?>...
                                        </p>

                                        <div class="menu-footer">

                                            <span class="menu-price">
                                                Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?>
                                            </span>

                                            <div style="display:flex; gap:10px;">

                                                <a
                                                    href="detail.php?id=<?php echo $item['id']; ?>"
                                                    class="btn-detail"
                                                >
                                                    Lihat Detail
                                                </a>

                                                <form method="POST" action="cart.php">

                                                    <input
                                                        type="hidden"
                                                        name="menu_id"
                                                        value="<?php echo $item['id']; ?>"
                                                    >

                                                    <button type="submit" class="btn-primary">
                                                        + Keranjang
                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    </div>

                <?php endif; ?>

            <?php endwhile; ?>

        <?php endif; ?>

        <!-- ORDER SECTION -->
        <div class="order-section">

            <h2 class="section-title">
                Simulasi Pemesanan Cepat
            </h2>

            <?php if (isset($pesan_sukses)): ?>

                <div class="alert-success">
                    Pesanan untuk meja Anda telah dicatat!
                    Cek status di halaman Pesanan Saya.
                </div>

            <?php endif; ?>

            <?php if (isset($pesan_error)): ?>

                <div class="alert-error">
                    <?php echo htmlspecialchars($pesan_error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="" class="order-form">

                <?php csrf_field(); ?>

                <div class="form-group">

                    <label for="nomor_meja">
                        Nomor Meja
                    </label>

                    <input
                        type="number"
                        id="nomor_meja"
                        name="nomor_meja"
                        required
                        min="1"
                        placeholder="Masukkan nomor meja Anda"
                    >

                </div>

                <button type="submit" class="btn-primary">
                    Buat Pesanan
                </button>

            </form>

        </div>

    </div>

    <!-- FOOTER -->
    <footer class="footer">

        <p>
            &copy; 2026 Ruang Rasa.
            Cita Rasa Nusantara yang Autentik.
            - Nur Azhim Ramadhan -
        </p>

    </footer>

</body>
</html>