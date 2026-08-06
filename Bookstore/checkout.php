<?php
// ==========================================
// checkout.php - Halaman Checkout & Pilih Metode Pembayaran
// ==========================================

session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = "";

// ---------- AMBIL ISI CART (sama kayak di cart.php) ----------
$stmt = mysqli_prepare($conn, "
    SELECT cart.id AS cart_id, cart.jumlah, books.*
    FROM cart
    JOIN books ON cart.book_id = books.id
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_cart = mysqli_stmt_get_result($stmt);

$items = [];
$total_harga = 0;
while ($item = mysqli_fetch_assoc($result_cart)) {
    $subtotal = $item['harga'] * $item['jumlah'];
    $item['subtotal'] = $subtotal;
    $total_harga += $subtotal;
    $items[] = $item;
}

// Hitung PPN 12% dan total akhir (yang beneran dibayar/disimpan sebagai total_harga)
$subtotal = $total_harga;
$pajak = round($subtotal * 0.12);
$grand_total = $subtotal + $pajak;

// Kalau cart kosong, nggak ada yang bisa di-checkout -> balik ke cart
if (count($items) == 0) {
    header("Location: cart.php");
    exit;
}

// ---------- PROSES CHECKOUT (waktu tombol "Bayar Sekarang" diklik) ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $metode = $_POST['metode_pembayaran'];
    $alamat = trim($_POST['alamat_pengiriman']);

    if (!$metode) {
        $error = "Pilih metode pembayaran dulu.";
    } elseif ($alamat == '') {
        $error = "Alamat pengiriman wajib diisi.";
    } else {
        // 1. Bikin 1 baris baru di tabel orders (data induk pesanan)
        // total_harga yang disimpan = grand_total (SUDAH termasuk PPN 12%)
        // Status defaultnya "Menunggu Diproses" -> nanti admin yang ubah jadi "Selesai"
        $status_awal = "Menunggu Diproses";
        $stmt_order = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_harga, metode_pembayaran, alamat_pengiriman, status) VALUES (?, ?, ?, ?, ?)");

        // Cek dulu: apakah query-nya berhasil di-"prepare"? Kalau nggak, biasanya
        // artinya ada masalah struktur tabel (misal kolom belum ada)
        if (!$stmt_order) {
            die("Gagal menyiapkan query orders: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt_order, "idsss", $user_id, $grand_total, $metode, $alamat, $status_awal);

        if (!mysqli_stmt_execute($stmt_order)) {
            die("Gagal menyimpan order: " . mysqli_stmt_error($stmt_order));
        }

        // Ambil id order yang baru aja dibuat, buat dipakai di order_items
        $order_id = mysqli_insert_id($conn);

        // 2. Looping tiap item di cart, masukin ke order_items (detail per buku)
        // Catatan: subtotal per item TETAP harga asli (belum kena pajak),
        // pajak dihitung total-nya aja di level order, bukan per-item
        foreach ($items as $item) {
            $stmt_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, book_id, judul, harga, jumlah, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_item, "iisdid", $order_id, $item['id'], $item['judul'], $item['harga'], $item['jumlah'], $item['subtotal']);
            mysqli_stmt_execute($stmt_item);

            // 3. Kurangin stok buku sesuai jumlah yang dibeli
            $stmt_stok = mysqli_prepare($conn, "UPDATE books SET stok = stok - ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt_stok, "ii", $item['jumlah'], $item['id']);
            mysqli_stmt_execute($stmt_stok);
        }

        // 4. Kosongin cart user ini (barang udah "dibeli", pindah dari cart ke orders)
        $stmt_kosongin = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt_kosongin, "i", $user_id);
        mysqli_stmt_execute($stmt_kosongin);

        // 5. Arahkan ke halaman struk/invoice
        header("Location: invoice.php?order_id=" . $order_id);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Bookstore</title>
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

    <section class="checkout-section">
        <h2>Checkout</h2>

        <?php if ($error) { ?>
            <p class="auth-error"><?php echo $error; ?></p>
        <?php } ?>

        <div class="checkout-layout">

            <!-- ===== KIRI: Ringkasan pesanan ===== -->
            <div class="checkout-summary">
                <h3>Ringkasan Pesanan</h3>
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
                    <strong>Rp<?php echo number_format($grand_total, 0, ',', '.'); ?></strong>
                </div>
            </div>

            <!-- ===== KANAN: Alamat + Pilih metode pembayaran ===== -->
            <div class="checkout-payment">
                <h3>Alamat Pengiriman</h3>
                <form method="POST" action="">
                    <textarea name="alamat_pengiriman" rows="3" placeholder="Nama penerima, alamat lengkap, no HP..." required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-family:Arial, sans-serif; margin-bottom:20px;"><?php echo isset($_POST['alamat_pengiriman']) ? htmlspecialchars($_POST['alamat_pengiriman']) : ''; ?></textarea>

                <h3>Metode Pembayaran</h3>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="QRIS" required>
                        <span>QRIS</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="GoPay">
                        <span>GoPay</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="DANA">
                        <span>DANA</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="OVO">
                        <span>OVO</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="Transfer BNI">
                        <span>Transfer Bank BNI</span>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="metode_pembayaran" value="Transfer BCA">
                        <span>Transfer Bank BCA</span>
                    </label>

                    <p style="font-size:12px; color:#888; margin:12px 0;">
                        *Ini simulasi latihan, belum ada proses pembayaran beneran.
                    </p>

                    <button type="submit" class="btn-checkout">Bayar Sekarang</button>
                </form>
            </div>

        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer>
        <div class="footer-content">
            <div class="footer-about">
                <p class="footer-title">📚 BOOKSTORE</p>
                <p class="footer-desc">Pusat buku lengkap dan terpercaya, siap menemani perjalanan dan ilmu pengetahuan baru.</p>
                <div class="footer-socials">
                    <a href="#" title="Facebook"><img src="assets/icons/FC.png" alt="Facebook"></a>
                    <a href="#" title="Instagram"><img src="assets/icons/IG.png" alt="Instagram"></a>
                    <a href="#" title="Twitter"><img src="assets/icons/TW.png" alt="Twitter"></a>
                    <a href="#" title="Youtube"><img src="assets/icons/YT.png" alt="Youtube"></a>
                </div>
            </div>

            <div class="footer-contact">
                <p>Jl. Pelajar Indonesia No. 45, Kebayoran Baru, Jakarta Selatan, 12190</p>
                <p>WhatsApp / HP: +62 812-3456-7890</p>
                <p>Email: support@bookstore-example.com / halo@bookstore.id</p>
                <p>Jam Operasional: Senin - Sabtu, 08.00 - 20.00 WIB</p>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-links">
                <a href="privacy-policy.php">Privacy Policy</a> | <a href="terms-of-service.php">Terms of Service</a> | <a href="cookie-preferences.php">Cookie Preferences</a>
            </div>
            <p class="footer-copyright">&copy; <?php echo date('Y'); ?> Bookstore. All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>
<?php
mysqli_close($conn);
?>