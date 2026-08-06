<?php
// ==========================================
// cart.php - Halaman Keranjang Belanja
// ==========================================

session_start();
include 'config.php';

// Wajib login buat akses cart
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ---------- PROSES HAPUS ITEM DARI CART ----------
if (isset($_GET['hapus'])) {
    $cart_id = $_GET['hapus'];
    $hapus = mysqli_prepare($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?");
    // AND user_id = ? penting! biar user A nggak bisa hapus cart punya user B
    // cuma dengan tebak-tebakan angka id di URL
    mysqli_stmt_bind_param($hapus, "ii", $cart_id, $user_id);
    mysqli_stmt_execute($hapus);
    header("Location: cart.php");
    exit;
}

// ---------- PROSES UBAH JUMLAH ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_qty') {
    $cart_id = $_POST['cart_id'];
    $jumlah = $_POST['jumlah'];

    // Minimal jumlah 1, nggak boleh 0 atau minus
    if ($jumlah < 1) { $jumlah = 1; }

    $update = mysqli_prepare($conn, "UPDATE cart SET jumlah = ? WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($update, "iii", $jumlah, $cart_id, $user_id);
    mysqli_stmt_execute($update);
    header("Location: cart.php");
    exit;
}

// ---------- AMBIL ISI CART ----------
// JOIN cart + books, biar dapet detail buku (judul, harga, dll)
$stmt = mysqli_prepare($conn, "
    SELECT cart.id AS cart_id, cart.jumlah, books.*
    FROM cart
    JOIN books ON cart.book_id = books.id
    WHERE cart.user_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result_cart = mysqli_stmt_get_result($stmt);

// Sekalian itung TOTAL harga semua item, sambil nampung datanya ke array
// (biar nggak query 2x buat nampilin + ngitung total)
$items = [];
$total_harga = 0;
while ($item = mysqli_fetch_assoc($result_cart)) {
    $subtotal = $item['harga'] * $item['jumlah'];
    $item['subtotal'] = $subtotal;
    $total_harga += $subtotal;
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang - Bookstore</title>
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
            <?php if (isset($_SESSION['user_id'])) { ?>
                <span class="user-info">
                    <img src="assets/icons/icon.png" alt="User" class="icon-user">
                    <?php echo $_SESSION['nama']; ?>
                </span>
                <a href="logout.php" class="logout-link">Logout</a>
            <?php } else { ?>
                <a href="login.php"><img src="assets/icons/icon.png" alt="User" class="icon-user"> Log in</a>
            <?php } ?>
        </div>
    </header>

    <section class="cart-section">
        <h2>Keranjang Belanja</h2>

        <?php if (count($items) == 0) { ?>
            <p>Keranjang kamu masih kosong.</p>
        <?php } else { ?>

            <div class="cart-table">
                <?php foreach ($items as $item) { ?>
                    <div class="cart-row">
                        <?php if ($item['cover_image']) { ?>
                            <img src="assets/images/books/<?php echo $item['cover_image']; ?>" alt="<?php echo $item['judul']; ?>" class="cart-img">
                        <?php } else { ?>
                            <img src="https://placehold.co/80x110?text=<?php echo urlencode($item['judul']); ?>" alt="<?php echo $item['judul']; ?>" class="cart-img">
                        <?php } ?>

                        <div class="cart-info">
                            <p class="cart-title"><?php echo $item['judul']; ?></p>
                            <p class="cart-price">Rp<?php echo number_format($item['harga'], 0, ',', '.'); ?></p>
                        </div>

                        <!-- Form kecil buat ubah jumlah -->
                        <form method="POST" action="" class="cart-qty-form">
                            <input type="hidden" name="action" value="update_qty">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="jumlah" value="<?php echo $item['jumlah']; ?>" min="1" class="cart-qty-input">
                            <button type="submit" class="btn-update-qty">Update</button>
                        </form>

                        <p class="cart-subtotal">Rp<?php echo number_format($item['subtotal'], 0, ',', '.'); ?></p>

                        <a href="cart.php?hapus=<?php echo $item['cart_id']; ?>"
                           class="btn-delete"
                           onclick="return confirm('Hapus buku ini dari keranjang?');">Hapus</a>
                    </div>
                <?php } ?>
            </div>

            <!-- ========== TOTAL & CHECKOUT ========== -->
            <div class="cart-total-box">
                <p class="cart-total-text">Total: <strong>Rp<?php echo number_format($total_harga, 0, ',', '.'); ?></strong></p>

                <!-- Tombol checkout sekarang beneran ngarah ke halaman checkout -->
                <a href="checkout.php" class="btn-checkout" style="text-decoration:none; display:inline-block;">
                    Checkout
                </a>
            </div>

        <?php } ?>
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