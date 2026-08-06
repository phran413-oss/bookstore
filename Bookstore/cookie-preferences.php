<?php
// ==========================================
// privacy-policy.php - Halaman statis
// ==========================================
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cookie Preferences - Bookstore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

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

    <section class="static-page-section">
        <h1>Cookie Preferences</h1>
        <p class="static-updated">Terakhir diperbarui: <?php echo date('d F Y'); ?></p>

        <h3>1. Apa itu Cookie?</h3>
        <p>Cookie adalah file kecil yang disimpan di browser kamu untuk membantu website mengingat informasi, seperti status login (session), agar kamu tidak perlu login ulang setiap membuka halaman baru.</p>

        <h3>2. Cookie yang Kami Gunakan</h3>
        <p>Bookstore menggunakan session cookie untuk menjaga status login kamu selama sesi belanja berlangsung. Cookie ini otomatis terhapus saat kamu logout atau menutup browser.</p>

        <h3>3. Mengatur Cookie</h3>
        <p>Kamu dapat menghapus atau memblokir cookie melalui pengaturan browser kamu, namun beberapa fitur seperti login dan keranjang belanja mungkin tidak akan berfungsi dengan baik.</p>

        <p style="margin-top:20px; font-size:12px; color:#999;">
            Catatan: Halaman ini adalah contoh placeholder untuk keperluan latihan/pengembangan, bukan kebijakan privasi yang berlaku secara hukum.
        </p>
    </section>

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