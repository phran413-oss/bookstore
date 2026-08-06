<?php
// ==========================================
// search.php - Halaman hasil pencarian buku
// ==========================================

session_start();
include 'config.php';

// Ambil keyword dari URL, contoh: search.php?q=sapiens
// trim() buat ngilangin spasi kosong di awal/akhir
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';

$result = null;

if ($keyword !== '') {
    // LIKE dipakai buat cari yang "mengandung" kata tertentu, bukan harus sama persis
    // tanda % di kiri-kanan artinya "boleh ada apa aja sebelum/sesudah keyword ini"
    // contoh: kalau keyword "sapi", ini bisa nemuin "Sapiens"
    $stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE judul LIKE ? OR penulis LIKE ?");
    $like_keyword = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "ss", $like_keyword, $like_keyword);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Pencarian - Bookstore</title>
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

        <form method="GET" action="search.php" class="search-box">
            <input type="text" name="q" placeholder="Search books..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit">🔍</button>
        </form>

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

    <section class="book-section">
        <h2>
            <?php if ($keyword !== '') { ?>
                Hasil pencarian untuk "<?php echo htmlspecialchars($keyword); ?>"
            <?php } else { ?>
                Cari buku
            <?php } ?>
        </h2>

        <div class="book-wrapper">
            <div class="book-list">
                <?php
                if ($keyword === '') {
                ?>
                    <p>Ketik judul buku atau nama penulis di kotak pencarian di atas.</p>
                <?php
                } elseif (mysqli_num_rows($result) == 0) {
                ?>
                    <p>Tidak ada buku yang cocok dengan "<?php echo htmlspecialchars($keyword); ?>".</p>
                <?php
                } else {
                    while ($book = mysqli_fetch_assoc($result)) {
                ?>
                    <a href="book_detail.php?id=<?php echo $book['id']; ?>" class="book-card">
                        <?php if ($book['cover_image']) { ?>
                            <img src="assets/images/books/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['judul']; ?>">
                        <?php } else { ?>
                            <img src="https://placehold.co/150x220?text=<?php echo urlencode($book['judul']); ?>" alt="<?php echo $book['judul']; ?>">
                        <?php } ?>
                        <p class="book-title"><?php echo $book['judul']; ?></p>
                        <p class="book-price">Rp<?php echo number_format($book['harga'], 0, ',', '.'); ?></p>
                    </a>
                <?php
                    }
                }
                ?>
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