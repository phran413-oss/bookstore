<?php
session_start();
// ==========================================
// books.php - Halaman daftar kategori/genre buku
// ==========================================

include 'config.php';

// 1. Ambil kategori UTAMA dulu (Fiksi, Non-Fiksi)
//    Ciri kategori utama: kolom parent_id-nya NULL (nggak punya induk)
$query_main = "SELECT * FROM categories WHERE parent_id IS NULL";
$result_main = mysqli_query($conn, $query_main);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Books - Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ========== NAVBAR (sama persis kayak di index.php) ========== -->
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

    <!-- ========== DAFTAR KATEGORI ========== -->
    <section class="category-section">
        <div class="category-columns">
            <?php
            // 2. Looping tiap kategori utama (Fiksi, Non-Fiksi)
            while ($main = mysqli_fetch_assoc($result_main)) {

                // Ambil id kategori utama ini, buat dipakai cari sub-genre-nya
                $main_id = $main['id'];

                // 3. Query kedua: ambil semua sub-genre yang parent_id-nya = kategori utama ini
                $query_sub = "SELECT * FROM categories WHERE parent_id = $main_id";
                $result_sub = mysqli_query($conn, $query_sub);
            ?>
                <div class="category-column">
                    <!-- Judul kolom, contoh: "#Fiksi" atau "#NonFiksi" -->
                    <h3>#<?php echo str_replace('-', '', $main['nama_kategori']); ?></h3>

                    <ul class="genre-list">
                        <?php
                        // 4. Looping sub-genre di dalam kategori utama ini
                        while ($sub = mysqli_fetch_assoc($result_sub)) {
                        ?>
                            <li>
                                <!-- Link ke books.php dengan parameter category id di URL,
                                     nanti dipakai buat filter daftar buku di genre itu -->
                                <a href="books_list.php?category=<?php echo $sub['id']; ?>">
                                    <?php echo $sub['nama_kategori']; ?>
                                </a>
                            </li>
                        <?php
                        }
                        ?>
                    </ul>
                </div>
            <?php
            }
            ?>
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