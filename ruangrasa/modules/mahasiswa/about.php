<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../auth/login.php');
    exit();
}

$nama = $_SESSION['nama'];
$role = $_SESSION['role'];
$foto = $_SESSION['foto'];
$current_page = basename($_SERVER['PHP_SELF']);

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
    <title>Tentang Kami - Ruang Rasa</title>
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
        <div class="about-hero">
            <h1 class="about-title">Tentang Ruang Rasa</h1>
            <p class="about-tagline">Warisan Kuliner Nusantara dalam Setiap Sajian</p>
        </div>

        <div class="about-content">
            <section class="about-section">
                <h2 class="about-section-title">Filosofi Kami</h2>
                <p class="about-text">
                    Ruang Rasa hadir sebagai jembatan antara tradisi kuliner Nusantara yang kaya dengan dinamika kehidupan modern. Kami percaya bahwa setiap hidangan bukan sekadar makanan, melainkan narasi budaya yang terbentuk dari warisan nenek moyang, dedikasi para koki, dan kearifan lokal yang telah teruji ratusan tahun. Setiap bumbu yang kami pilih, setiap teknik memasak yang kami terapkan, adalah hasil riset mendalam dan komitmen untuk menjaga orisinalitas tanpa mengorbankan inovasi.
                </p>
                <p class="about-text">
                    Nama "Ruang Rasa" dipilih untuk merepresentasikan ruang eksplorasi tanpa batas—tempat di mana lidah Anda dapat melintasi berbagai provinsi di Indonesia hanya melalui satu menu. Dari pedas menyengat khas Sumatera Barat, gurih lembut dari Jawa Tengah, hingga kesegaran tropis dari kawasan timur Indonesia. Kami tidak hanya menyajikan makanan; kami menghadirkan pengalaman kuliner yang menyentuh emosi dan memori.
                </p>
            </section>

            <section class="about-section">
                <h2 class="about-section-title">Standar Kualitas Tertinggi</h2>
                <p class="about-text">
                    Setiap bahan baku yang masuk ke dapur kami melalui proses seleksi ketat. Daging sapi pilihan dipilih dari peternak lokal bersertifikat halal dan sehat. Rempah-rempah seperti kunyit, lengkuas, jahe, dan serai kami dapatkan langsung dari petani organik untuk memastikan kesegaran dan kemurnian aroma. Santan yang kami gunakan diperas langsung dari kelapa segar, bukan dari kemasan instan, karena kami memahami bahwa detail terkecil menciptakan perbedaan rasa yang signifikan.
                </p>
                <p class="about-text">
                    Tim koki kami terdiri dari para ahli yang telah mendalami kuliner tradisional selama puluhan tahun. Mereka adalah penjaga resep turun-temurun yang telah kami dokumentasikan dengan cermat, namun tetap terbuka terhadap penyesuaian berdasarkan masukan pelanggan dan tren kesehatan modern. Contohnya, kami mengurangi penggunaan MSG tanpa mengurangi kelezatan, serta menyediakan opsi rendah garam bagi pelanggan dengan kebutuhan khusus.
                </p>
            </section>

            <section class="about-section">
                <h2 class="about-section-title">Pengalaman Digital yang Modern</h2>
                <p class="about-text">
                    Di era digital ini, kami memahami bahwa kenyamanan adalah bagian dari pengalaman bersantap yang sempurna. Sistem E-Menu kami dirancang untuk memberikan kemudahan akses informasi lengkap tentang setiap hidangan—mulai dari deskripsi detail, harga transparan, hingga rekomendasi berdasarkan preferensi pelanggan. Anda dapat menelusuri menu kami kapan saja, dari mana saja, bahkan sebelum tiba di lokasi restoran.
                </p>
                <p class="about-text">
                    Teknologi digital yang kami implementasikan bukan untuk menggantikan kehangatan layanan manusia, melainkan memperkuatnya. Pelayan kami tetap siap membantu dengan senyuman ramah, sementara sistem pesanan digital memastikan tidak ada kesalahan komunikasi antara Anda, pelayan, dan dapur. Hasilnya adalah efisiensi maksimal tanpa mengorbankan sentuhan personal yang membuat pengalaman bersantap menjadi berkesan.
                </p>
            </section>

            <section class="about-section">
                <h2 class="about-section-title">Komitmen Keberlanjutan</h2>
                <p class="about-text">
                    Kami menyadari bahwa industri kuliner memiliki tanggung jawab besar terhadap kelestarian lingkungan. Oleh karena itu, Ruang Rasa mengadopsi praktik berkelanjutan dalam setiap aspek operasional. Limbah organik dari dapur kami dikelola menjadi kompos untuk pertanian mitra. Kemasan takeaway kami menggunakan material ramah lingkungan yang dapat terurai secara alami. Bahkan listrik yang kami gunakan sebagian besar berasal dari energi terbarukan.
                </p>
                <p class="about-text">
                    Selain itu, kami aktif berkolaborasi dengan komunitas lokal untuk memberdayakan petani kecil dan UMKM. Dengan membeli bahan baku langsung dari mereka, kami tidak hanya mendapatkan kualitas terbaik, tetapi juga turut meningkatkan kesejahteraan ekonomi masyarakat. Ruang Rasa bukan hanya bisnis; kami adalah bagian dari ekosistem sosial yang saling mendukung pertumbuhan bersama.
                </p>
            </section>

            <section class="about-section">
                <h2 class="about-section-title">Visi Masa Depan</h2>
                <p class="about-text">
                    Kedepannya, Ruang Rasa bertekad menjadi destinasi utama bagi siapa pun yang ingin merasakan kekayaan kuliner Indonesia dalam kemasan modern. Kami berencana membuka lebih banyak cabang di berbagai kota, menghadirkan program edukasi kuliner untuk generasi muda, dan bahkan ekspor produk olahan khas kami ke pasar internasional. Tujuan kami sederhana namun ambisius: membuat dunia jatuh cinta pada masakan Indonesia.
                </p>
                <p class="about-text">
                    Terima kasih telah mempercayai Ruang Rasa sebagai pilihan Anda untuk menikmati hidangan berkualitas. Setiap kunjungan Anda adalah dukungan terhadap misi kami untuk melestarikan dan memajukan warisan kuliner bangsa. Kami berkomitmen untuk terus berinovasi, meningkatkan kualitas, dan memberikan pengalaman yang tak terlupakan di setiap kesempatan.
                </p>
            </section>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ruang Rasa. Cita Rasa Nusantara yang Autentik. - Nur Azhim Ramadhan -</p>
    </footer>
</body>
</html>
