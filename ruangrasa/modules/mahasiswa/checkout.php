<?php
session_start();

require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id'])) {

    header('Location: ../../auth/login.php');
    exit();
}

if (empty($_SESSION['cart'])) {

    header('Location: cart.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$cart = $_SESSION['cart'];

$total = 0;

$cart_items = [];

$ids = implode(',', array_keys($cart));

$query = "SELECT * FROM menu WHERE id IN ($ids)";

$result = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($result)) {

    $qty = $cart[$row['id']];

    $subtotal = $row['harga'] * $qty;

    $total += $subtotal;

    $cart_items[] = [

        'id' => $row['id'],
        'nama_menu' => $row['nama_menu'],
        'harga' => $row['harga'],
        'qty' => $qty,
        'subtotal' => $subtotal
    ];
}

/*
|--------------------------------------------------------------------------
| PROSES CHECKOUT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nomor_meja = intval($_POST['nomor_meja']);

    /*
    |--------------------------------------------------------------------------
    | INSERT PESANAN
    |--------------------------------------------------------------------------
    */

    $stmt = $koneksi->prepare("
        INSERT INTO pesanan
        (user_id, nomor_meja, total_harga, status)
        VALUES (?, ?, ?, 'pending')
    ");

    $stmt->bind_param(
        "iii",
        $user_id,
        $nomor_meja,
        $total
    );

    $stmt->execute();

    $pesanan_id = $stmt->insert_id;

    /*
    |--------------------------------------------------------------------------
    | INSERT DETAIL PESANAN
    |--------------------------------------------------------------------------
    */

    foreach ($cart_items as $item) {

        $stmt_detail = $koneksi->prepare("
            INSERT INTO detail_pesanan
            (pesanan_id, menu_id, qty, subtotal)
            VALUES (?, ?, ?, ?)
        ");

        $stmt_detail->bind_param(
            "iiii",
            $pesanan_id,
            $item['id'],
            $item['qty'],
            $item['subtotal']
        );

        $stmt_detail->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | KOSONGKAN CART
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['cart']);

    /*
    |--------------------------------------------------------------------------
    | REDIRECT
    |--------------------------------------------------------------------------
    */

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout - Ruang Rasa</title>

    <link rel="stylesheet" href="../../style.css">

</head>

<body>

<div class="main-container">

    <div class="order-section">

        <h1 class="section-title">
            Checkout Pesanan
        </h1>

        <div class="table-container">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Subtotal</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($cart_items as $item): ?>

                    <tr>

                        <td>
                            <?php echo htmlspecialchars($item['nama_menu']); ?>
                        </td>

                        <td>
                            <?php echo $item['qty']; ?>
                        </td>

                        <td>
                            Rp <?php echo number_format($item['subtotal'],0,',','.'); ?>
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

                Total Bayar:

                <span style="color:#C41230;">

                    Rp <?php echo number_format($total,0,',','.'); ?>

                </span>

            </h2>

            <br>

            <form method="POST">

                <div class="form-group">

                    <label>
                        Nomor Meja
                    </label>

                    <input
                        type="number"
                        name="nomor_meja"
                        required
                        min="1"
                        class="search-input"
                        placeholder="Masukkan nomor meja"
                    >

                </div>

                <br>

                <button type="submit" class="btn-primary">

                    Konfirmasi Checkout

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>