<?php
session_start();

require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];
$user_id = $_SESSION['user_id'];
$pesan_sukses = false;
$pesan_error = '';

/*
|--------------------------------------------------------------------------
| SESSION CART
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/*
|--------------------------------------------------------------------------
| TAMBAH MENU KE CART
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['menu_id'])) {

        $menu_id = intval($_POST['menu_id']);

        if (isset($_SESSION['cart'][$menu_id])) {

            $_SESSION['cart'][$menu_id]++;

        } else {

            $_SESSION['cart'][$menu_id] = 1;
        }

        header("Location: index.php?tambah=success");
        exit();
    }
}

/*
|--------------------------------------------------------------------------
| HAPUS ITEM
|--------------------------------------------------------------------------
*/

if (isset($_GET['hapus'])) {

    $hapus_id = intval($_GET['hapus']);

    unset($_SESSION['cart'][$hapus_id]);

    header("Location: cart.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| UPDATE JUMLAH
|--------------------------------------------------------------------------
*/

if (isset($_GET['tambah'])) {

    $id = intval($_GET['tambah']);

    if (isset($_SESSION['cart'][$id])) {

        $_SESSION['cart'][$id]++;
    }

    header("Location: cart.php");
    exit();
}

if (isset($_GET['kurang'])) {

    $id = intval($_GET['kurang']);

    if (isset($_SESSION['cart'][$id])) {

        $_SESSION['cart'][$id]--;

        if ($_SESSION['cart'][$id] <= 0) {

            unset($_SESSION['cart'][$id]);
        }
    }

    header("Location: cart.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA CART
|--------------------------------------------------------------------------
*/

$cart_items = [];

$total = 0;

if (!empty($_SESSION['cart'])) {

    $ids = implode(',', array_keys($_SESSION['cart']));

    $query = "SELECT * FROM menu WHERE id IN ($ids)";

    $result = mysqli_query($koneksi, $query);

    while ($row = mysqli_fetch_assoc($result)) {

        $qty = $_SESSION['cart'][$row['id']];

        $subtotal = $row['harga'] * $qty;

        $total += $subtotal;

        $cart_items[] = [

            'id' => $row['id'],
            'nama_menu' => $row['nama_menu'],
            'harga' => $row['harga'],
            'gambar' => $row['gambar'],
            'qty' => $qty,
            'subtotal' => $subtotal
        ];
    }
}

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

    <!-- NAVBAR -->
    <nav class="navbar">

        <div class="nav-container">

            <div class="nav-brand">
                Ruang Rasa
            </div>

            <div class="nav-links">

            <a href="index.php" class="nav-link">
                Menu
            </a>

<?php

$total_cart = 0;

if (isset($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $qty) {

        $total_cart += $qty;
    }
}

?>

        <a href="cart.php" class="nav-link active" style="position:relative;">

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

                <a href="orders.php" class="nav-link">
                    Pesanan Saya
                </a>

                <a href="about.php" class="nav-link">
                    Tentang Kami
                </a>

                <?php if ($role === 'admin'): ?>

                    <a href="admin.php" class="nav-link">
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
    
    <!-- CART PREVIEW -->
<div class="order-section">

    <h2 class="section-title">
        Keranjang Saya
    </h2>

    <?php if (empty($cart_items)): ?>

        <div class="empty-state">

            <p>
                Keranjang masih kosong.
            </p>

            <a href="index.php" class="btn-primary">
                Belanja Sekarang
            </a>

        </div>

    <?php else: ?>

        <div class="table-container">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>Menu</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($cart_items as $item): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($item['nama_menu']); ?>
                        </td>

                        <td>
                            Rp <?php echo number_format($item['harga'],0,',','.'); ?>
                        </td>

                        <td>

                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    gap:12px;
                                "
                            >

                                <a
                                    href="cart.php?kurang=<?php echo $item['id']; ?>"
                                    class="btn-action btn-delete"
                                >
                                    -
                                </a>

                                <strong>
                                    <?php echo $item['qty']; ?>
                                </strong>

                                <a
                                    href="cart.php?tambah=<?php echo $item['id']; ?>"
                                    class="btn-action btn-success"
                                >
                                    +
                                </a>

                            </div>

                        </td>

                        <td>
                            Rp <?php echo number_format($item['subtotal'],0,',','.'); ?>
                        </td>

                        <td>

                            <a
                                href="cart.php?hapus=<?php echo $item['id']; ?>"
                                class="btn-delete"
                            >
                                Hapus
                            </a>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <div
            style="
                margin-top:30px;
                padding:30px;
                background:white;
                border-radius:16px;
                box-shadow:0 4px 20px rgba(0,0,0,0.08);
            "
        >

            <h2>

                Total:

                <span style="color:#C41230;">

                    Rp <?php echo number_format($total,0,',','.'); ?>

                </span>

            </h2>

            <br>

            <a href="checkout.php" class="btn-primary">

                Checkout Sekarang

            </a>

        </div>

    <?php endif; ?>

</div>


        <!-- ORDER SECTION -->
        <div class="order-section">

            <h2 class="section-title">
                Pesanan Saya
            </h2>

            <p
                style="
                    margin-top:-10px;
                    margin-bottom:25px;
                    color:#666;
                    font-size:15px;
                    line-height:1.7;
                "
            >
                Silakan lakukan checkout untuk melanjutkan proses pemesanan makanan Anda.
                Setelah checkout berhasil, status pesanan dapat dipantau secara realtime
                melalui halaman Pesanan Saya.
            </p>

            <?php if (isset($pesan_sukses)): ?>

                <div class="alert-success">
                    Pesanan untuk meja Anda telah dicatat!
                    Cek status di halaman Pesanan Saya.
                </div>

            <?php endif; ?>

            <?php if (!empty($pesan_error)): ?>

                <div class="alert-error">
                    <?php echo htmlspecialchars($pesan_error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="" class="order-form">

                <?php csrf_field(); ?>

                <div class="form-group">

        </div>

    </div>

<script>

document.querySelectorAll('.qty-btn').forEach(button => {

    button.addEventListener('click', function(e) {

        e.preventDefault();

        let id = this.dataset.id;
        let action = this.dataset.action;

        fetch(`cart.php?${action}=${id}`)
        .then(response => response.text())
        .then(() => {

            location.reload();

        });

    });

});

</script>

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