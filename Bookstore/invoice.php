<?php
// ==========================================
// invoice.php - Halaman Struk/Bukti Pembelian (sisi User)
// ==========================================

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Ambil data order, PASTIKAN juga order ini punya user yang lagi login
$stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Struk tidak ditemukan.");
}

// Ambil detail item-item di order ini
$stmt_items = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);

// Tampung ke array sambil itung ulang subtotal (buat breakdown PPN di struk)
$items = [];
$subtotal = 0;
while ($item = mysqli_fetch_assoc($result_items)) {
    $items[] = $item;
    $subtotal += $item['subtotal'];
}
$pajak = round($subtotal * 0.12);
// total_harga di database udah termasuk pajak (dihitung pas checkout)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Pembelian - Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ========== NAVBAR ========== -->
    <header class="navbar">
        <div class="logo">📚 BOOKSTORE</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="books.php">Books</a>
            <a href="wishlist.php">Wishlist</a>
            <a href="riwayat_pesanan.php">Pesanan Saya</a>
            <a href="#">Forum</a>
        </nav>
        <div class="nav-icons">
            <a href="cart.php"><img src="assets/icons/trolley.png" alt="Cart" class="icon-cart"></a>
            <span class="user-info">
                <img src="assets/icons/icon.png" alt="User" class="icon-user">
                <?php echo $_SESSION['nama']; ?>
            </span>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </header>

    <section class="invoice-section">

        <?php if ($order['status'] == 'Menunggu Diproses') { ?>

            <!-- ===== TAMPILAN SEBELUM DIPROSES: cuma rangkuman biasa ===== -->
            <div class="summary-box">
                <p class="summary-pending-badge">⏳ Menunggu Diproses Admin</p>
                <h2 style="margin-bottom:4px;">Pesanan Diterima</h2>
                <p class="invoice-meta">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?> — <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></p>

                <p style="font-size:13px; color:#666; margin:15px 0;">
                    Struk resmi akan muncul otomatis di halaman ini begitu admin memproses pesanan kamu.
                    Cek lagi nanti lewat menu "Pesanan Saya".
                </p>

                <div class="summary-items">
                    <?php foreach ($items as $item) { ?>
                        <div class="checkout-item-row">
                            <span><?php echo $item['judul']; ?> x<?php echo $item['jumlah']; ?></span>
                            <span>Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                    <?php } ?>
                    <div class="checkout-item-row" style="border-top:1px dashed #ccc; margin-top:8px; padding-top:10px;">
                        <span>Subtotal</span>
                        <span>Rp<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                    </div>
                    <div class="checkout-item-row">
                        <span>PPN (12%)</span>
                        <span>Rp<?php echo number_format($pajak, 0, ',', '.'); ?></span>
                    </div>
                    <div class="checkout-item-row checkout-total-row">
                        <strong>Total</strong>
                        <strong>Rp<?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
                    </div>
                </div>

                <p style="font-size:13px; color:#666; margin-top:15px;">
                    Metode Pembayaran: <strong><?php echo $order['metode_pembayaran']; ?></strong>
                </p>
            </div>

        <?php } else { ?>

            <!-- ===== TAMPILAN SETELAH DIPROSES: struk resmi gaya thermal printer ===== -->
            <div class="receipt">
                <div class="receipt-header">
                    <p class="receipt-store">📚 BOOKSTORE</p>
                    <p class="receipt-address">
                        Jl. Pelajar Indonesia No. 45, Kebayoran Baru<br>
                        Jakarta Selatan, 12190
                    </p>
                </div>

                <div class="receipt-dashed"></div>

                <div class="receipt-meta">
                    <div class="receipt-meta-row"><span>Tanggal</span><span><?php echo date('d-m-Y H:i', strtotime($order['created_at'])); ?></span></div>
                    <div class="receipt-meta-row"><span>No. Order</span><span>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span></div>
                    <div class="receipt-meta-row"><span>Pembeli</span><span><?php echo $_SESSION['nama']; ?></span></div>
                    <div class="receipt-meta-row"><span>Channel</span><span><?php echo $order['metode_pembayaran']; ?></span></div>
                </div>

                <div class="receipt-dashed"></div>

                <div class="receipt-items">
                    <?php foreach ($items as $item) { ?>
                        <div class="receipt-item-name"><?php echo $item['judul']; ?></div>
                        <div class="receipt-item-detail">
                            <span><?php echo $item['jumlah']; ?> x <?php echo number_format($item['harga'], 0, ',', '.'); ?></span>
                            <span><?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                    <?php } ?>
                </div>

                <div class="receipt-dashed"></div>

                <div class="receipt-meta">
                    <div class="receipt-meta-row"><span>Subtotal</span><span><?php echo number_format($subtotal, 0, ',', '.'); ?></span></div>
                    <div class="receipt-meta-row"><span>PPN 12%</span><span><?php echo number_format($pajak, 0, ',', '.'); ?></span></div>
                    <div class="receipt-meta-row receipt-total"><span>TOTAL</span><span><?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span></div>
                </div>

                <div class="receipt-dashed"></div>

                <p class="receipt-status">
                    Status: <strong><?php echo $order['status']; ?></strong>
                </p>

                <p class="receipt-footer">** Terima kasih sudah belanja di Bookstore **</p>
            </div>

            <!-- Tombol ini sengaja ditaruh DI LUAR div.receipt, biar nggak ikut ke-print -->
            <div class="invoice-actions print-hide">
                <button type="button" onclick="window.print()" class="btn-checkout" style="border:none; cursor:pointer;">
                    🖨️ Cetak / Download Struk
                </button>
            </div>

        <?php } ?>

        <div class="invoice-actions print-hide">
            <a href="index.php" class="back-link">&larr; Kembali ke Home</a>
        </div>

    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>