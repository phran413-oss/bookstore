<?php
// ==========================================
// books_list.php - Daftar buku berdasarkan genre yang dipilih
// ==========================================

session_start();
include 'config.php';

// Ambil id kategori dari URL, contoh: books_list.php?category=5
$category_id = $_GET['category'];

// Ambil dulu nama kategori-nya, buat ditampilin sebagai judul halaman
$stmt_kat = mysqli_prepare($conn, "SELECT nama_kategori FROM categories WHERE id = ?");
mysqli_stmt_bind_param($stmt_kat, "i", $category_id);
mysqli_stmt_execute($stmt_kat);
$hasil_kat = mysqli_stmt_get_result($stmt_kat);
$kategori = mysqli_fetch_assoc($hasil_kat);

// Ambil semua buku yang category_id-nya sesuai
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE category_id = ?");
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $kategori ? $kategori['nama_kategori'] : 'Genre'; ?> - Bookstore</title>
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

    <section class="book-section">
        <a href="books.php" class="back-link">&larr; Kembali ke daftar genre</a>

        <h2><?php echo $kategori ? $kategori['nama_kategori'] : 'Genre tidak ditemukan'; ?></h2>

        <div class="book-wrapper">
            <div class="book-list">
                <?php
                if (mysqli_num_rows($result) == 0) {
                ?>
                    <p>Belum ada buku di genre ini.</p>
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