<?php
// ==========================================
// admin/transaksi_detail.php - Detail Pesanan & Proses
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil id ORDER dari URL (bukan id buku lagi kayak versi lama)
$id = $_GET['id'];

// ---------- PROSES TOMBOL "PROSES PESANAN" ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'proses_pesanan') {
    $update = mysqli_prepare($conn, "UPDATE orders SET status = 'Selesai' WHERE id = ?");
    mysqli_stmt_bind_param($update, "i", $id);
    mysqli_stmt_execute($update);

    header("Location: transaksi_detail.php?id=" . $id);
    exit;
}

// Ambil data order + nama usernya
$stmt = mysqli_prepare($conn, "
    SELECT orders.*, users.nama AS nama_user, users.email AS email_user
    FROM orders
    JOIN users ON orders.user_id = users.id
    WHERE orders.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

// Ambil detail item-item di pesanan ini
$stmt_items = mysqli_prepare($conn, "SELECT * FROM order_items WHERE order_id = ?");
mysqli_stmt_bind_param($stmt_items, "i", $id);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);

// Simpen ke array, biar bisa dipakai 2x (buat tabel DAN buat label pengiriman)
// soalnya hasil query cuma bisa di-loop sekali kalau langsung dari $result_items
$items = [];
while ($row = mysqli_fetch_assoc($result_items)) {
    $items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Pesanan #<?php echo $order['id']; ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- ========== NAVBAR ADMIN ========== -->
    <header class="navbar">
        <div class="logo">📚 BOOKSTORE ADMIN</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="kategori.php">Kategori</a>
        </nav>
        <div class="nav-icons">
            <span class="user-info">Halo, <?php echo $_SESSION['nama']; ?></span>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
    </header>

    <section class="admin-section">
        <a href="transaksi.php" class="back-link print-hide">&larr; Kembali ke daftar pesanan</a>

        <div class="transaksi-detail-box" style="flex-direction:column;">

            <div class="print-hide" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:15px;">
                <div>
                    <h2>Pesanan #<?php echo $order['id']; ?></h2>
                    <p style="color:#666; font-size:14px;">
                        Dipesan oleh <strong><?php echo $order['nama_user']; ?></strong> (<?php echo $order['email_user']; ?>)
                        <br>
                        <?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?>
                    </p>
                </div>

                <div>
                    <?php if ($order['status'] == 'Menunggu Diproses') { ?>
                        <span class="status-pending">⏳ Menunggu Diproses</span>
                    <?php } else { ?>
                        <span class="status-selesai">✅ Selesai</span>
                    <?php } ?>
                </div>
            </div>

            <table class="admin-table print-hide" style="margin-bottom:15px;">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item) { ?>
                        <tr>
                            <td><?php echo $item['judul']; ?></td>
                            <td><?php echo $item['jumlah']; ?></td>
                            <td>Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td>Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <p class="print-hide" style="text-align:right; font-size:16px; margin-bottom:5px;">
                <strong>Total: Rp<?php echo number_format($order['total_harga'], 0, ',', '.'); ?></strong>
            </p>
            <p class="print-hide" style="text-align:right; font-size:13px; color:#666; margin-bottom:20px;">
                Metode Pembayaran: <?php echo $order['metode_pembayaran']; ?>
            </p>

            <!-- ===== LABEL PENGIRIMAN (khusus admin, nggak muncul di struk user) ===== -->
            <!-- Bagian ini SENGAJA tidak dikasih class print-hide, biar ini yang kecetak -->
            <div class="shipping-label">
                <p class="shipping-label-title">📦 LABEL PENGIRIMAN</p>
                <div class="shipping-label-box">
                    <p><strong>Penerima:</strong> <?php echo $order['nama_user']; ?></p>
                    <p><strong>Alamat:</strong></p>
                    <p style="white-space:pre-line;"><?php echo htmlspecialchars($order['alamat_pengiriman']); ?></p>

                    <p style="margin-top:10px;"><strong>Isi Paket:</strong></p>
                    <ul style="margin:4px 0 0 18px; padding:0;">
                        <?php foreach ($items as $item) { ?>
                            <li><?php echo $item['judul']; ?> (x<?php echo $item['jumlah']; ?>)</li>
                        <?php } ?>
                    </ul>

                    <p style="margin-top:8px;"><strong>No. Order:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                </div>
                <button type="button" onclick="window.print()" class="btn-print print-hide">🖨️ Cetak Label</button>
            </div>

            <?php if ($order['status'] == 'Menunggu Diproses') { ?>
                <form method="POST" action="" class="print-hide" style="text-align:right;">
                    <input type="hidden" name="action" value="proses_pesanan">
                    <button type="submit" class="btn-checkout" style="border:none; cursor:pointer;">
                        ✅ Proses Pesanan Ini
                    </button>
                </form>
            <?php } else { ?>
                <p class="print-hide" style="text-align:right; color:#27ae60; font-weight:bold;">
                    Pesanan ini sudah selesai diproses.
                </p>
            <?php } ?>

        </div>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>