<?php
session_start();
// ==========================================
// wishlist.php - Halaman Wishlist
// ==========================================

include 'config.php';

// Halaman wishlist WAJIB login dulu, soalnya wishlist itu punya masing-masing user
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil user_id dari session (bukan hardcode lagi, sekarang sistem login udah jalan)
$user_id = $_SESSION['user_id'];

// Query JOIN: gabungin tabel wishlist dan books
// - wishlist cuma nyimpen user_id & book_id
// - books nyimpen detail buku (judul, harga, dll)
// JOIN dipakai buat "nyambungin" 2 tabel itu berdasarkan book_id yang sama
$query_wishlist = "
    SELECT books.*
    FROM wishlist
    JOIN books ON wishlist.book_id = books.id
    WHERE wishlist.user_id = $user_id
";
$result_wishlist = mysqli_query($conn, $query_wishlist);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Wishlist - Bookstore</title>
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
            <input type="text" name="q" placeholder="Search books...">
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

    <!-- ========== SECTION: WISHLIST ========== -->
    <section class="book-section">
        <h2>Wishlist</h2>

        <div class="book-wrapper">
            <div class="book-list">
                <?php
                // Cek dulu: ada isinya atau kosong?
                if (mysqli_num_rows($result_wishlist) == 0) {
                ?>
                    <p>Belum ada buku di wishlist kamu.</p>
                <?php
                } else {
                    // Looping tampilin tiap buku yang ada di wishlist
                    while ($book = mysqli_fetch_assoc($result_wishlist)) {
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