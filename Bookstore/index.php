<?php
session_start();
// ==========================================
// index.php - Halaman Home Bookstore
// ==========================================

// 1. Konek ke database (file config.php yang udah kita buat sebelumnya)
include 'config.php';

// 2. Ambil data buku buat tiap section, pakai query SQL

//    "What's new" sekarang OTOMATIS: buku yang ditambahin dalam 7 hari terakhir
//    NOW() = waktu sekarang, INTERVAL 7 DAY = mundur 7 hari dari sekarang
$query_new = "SELECT * FROM books WHERE created_at >= NOW() - INTERVAL 7 DAY ORDER BY created_at DESC";
$result_new = mysqli_query($conn, $query_new);

//    is_bestseller = 1 -> masuk section "Best seller"
$query_bestseller = "SELECT * FROM books WHERE is_bestseller = 1";
$result_bestseller = mysqli_query($conn, $query_bestseller);

//    is_trending = 1 -> masuk section "Trending now"
$query_trending = "SELECT * FROM books WHERE is_trending = 1";
$result_trending = mysqli_query($conn, $query_trending);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bookstore</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    
    <!-- ========== NAVBAR ========== -->
<header class="navbar">

    <div class="logo">
        📚 BOOKSTORE
    </div>

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

    <!-- ========== SECTION: WHAT'S NEW ========== -->
    <section class="book-section">
        <h2>What's new?</h2>
        <div class="book-wrapper">
            <div class="book-list">
            <?php
            // Looping: selama masih ada data buku di $result_new, tampilkan satu per satu
            while ($book = mysqli_fetch_assoc($result_new)) {
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
            ?>
            </div>
        </div>
    </section>

    <!-- ========== SECTION: BEST SELLER ========== -->
    <section class="book-section">
        <h2>Best seller</h2>
        <div class="book-wrapper">
            <div class="book-list">
            <?php
            while ($book = mysqli_fetch_assoc($result_bestseller)) {
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
            ?>
            </div>
        </div>
    </section>

    <!-- ========== SECTION: TRENDING NOW ========== -->
    <section class="book-section">
        <h2>Trending now</h2>
        <div class="book-wrapper">
            <div class="book-list">
            <?php
            while ($book = mysqli_fetch_assoc($result_trending)) {
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
// 3. Tutup koneksi database di akhir file (kebiasaan yang baik)
mysqli_close($conn);
?>