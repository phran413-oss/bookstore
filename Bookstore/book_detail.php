<?php
// ==========================================
// book_detail.php - Halaman Detail Buku (sisi User)
// ==========================================

session_start();
include 'config.php';

// Ambil id buku dari URL, contoh: book_detail.php?id=3
$id = $_GET['id'];

// JOIN ke tabel categories, biar bisa ambil nama genre-nya sekalian
// (categories.nama_kategori dikasih nama alias "genre" biar gampang dipanggil)
$stmt = mysqli_prepare($conn, "
    SELECT books.*, categories.nama_kategori AS genre
    FROM books
    JOIN categories ON books.category_id = categories.id
    WHERE books.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);

// ---------- PROSES TOMBOL WISHLIST ----------
$pesan_wishlist = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_wishlist') {

    // Harus login dulu buat bisa wishlist
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Cek dulu, buku ini udah ada di wishlist user ini apa belum
    $cek = mysqli_prepare($conn, "SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    mysqli_stmt_bind_param($cek, "ii", $user_id, $id);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        // Udah ada -> berarti ini aksi HAPUS dari wishlist
        $hapus = mysqli_prepare($conn, "DELETE FROM wishlist WHERE user_id = ? AND book_id = ?");
        mysqli_stmt_bind_param($hapus, "ii", $user_id, $id);
        mysqli_stmt_execute($hapus);
        $pesan_wishlist = "Dihapus dari wishlist.";
    } else {
        // Belum ada -> berarti ini aksi TAMBAH ke wishlist
        $tambah = mysqli_prepare($conn, "INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($tambah, "ii", $user_id, $id);
        mysqli_stmt_execute($tambah);
        $pesan_wishlist = "Ditambahkan ke wishlist!";
    }
}

// ---------- PROSES TOMBOL TAMBAH KE KERANJANG ----------
$pesan_cart = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];

    // Cek dulu, buku ini udah ada di cart user ini apa belum
    $cek_cart = mysqli_prepare($conn, "SELECT id, jumlah FROM cart WHERE user_id = ? AND book_id = ?");
    mysqli_stmt_bind_param($cek_cart, "ii", $user_id, $id);
    mysqli_stmt_execute($cek_cart);
    $hasil_cek = mysqli_stmt_get_result($cek_cart);

    if ($row_cart = mysqli_fetch_assoc($hasil_cek)) {
        // Udah ada di cart -> tambahin jumlahnya +1 (bukan bikin baris baru)
        $jumlah_baru = $row_cart['jumlah'] + 1;
        $update_cart = mysqli_prepare($conn, "UPDATE cart SET jumlah = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_cart, "ii", $jumlah_baru, $row_cart['id']);
        mysqli_stmt_execute($update_cart);
    } else {
        // Belum ada -> insert baris baru dengan jumlah 1
        $insert_cart = mysqli_prepare($conn, "INSERT INTO cart (user_id, book_id, jumlah) VALUES (?, ?, 1)");
        mysqli_stmt_bind_param($insert_cart, "ii", $user_id, $id);
        mysqli_stmt_execute($insert_cart);
    }

    $pesan_cart = "Ditambahkan ke keranjang!";
}

// Cek lagi status wishlist SEKARANG (buat nentuin teks tombol: "Tambah" atau "Hapus")
$is_wishlisted = false;
if (isset($_SESSION['user_id'])) {
    $cek2 = mysqli_prepare($conn, "SELECT id FROM wishlist WHERE user_id = ? AND book_id = ?");
    mysqli_stmt_bind_param($cek2, "ii", $_SESSION['user_id'], $id);
    mysqli_stmt_execute($cek2);
    mysqli_stmt_store_result($cek2);
    $is_wishlisted = mysqli_stmt_num_rows($cek2) > 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo $book['judul']; ?> - Bookstore</title>
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

    <section class="detail-section">
        <a href="index.php" class="back-link">&larr; Kembali</a>

        <div class="detail-box">

            <!-- ===== KIRI: Gambar buku ===== -->
            <div class="detail-left">
                <?php if ($book['cover_image']) { ?>
                    <img src="assets/images/books/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['judul']; ?>">
                <?php } else { ?>
                    <img src="https://placehold.co/250x350?text=<?php echo urlencode($book['judul']); ?>" alt="<?php echo $book['judul']; ?>">
                <?php } ?>
            </div>

            <!-- ===== KANAN: Info buku ===== -->
            <div class="detail-right">
                <h1><?php echo $book['judul']; ?></h1>
                <p class="detail-penulis">oleh <?php echo $book['penulis']; ?></p>

                <span class="detail-genre"><?php echo $book['genre']; ?></span>

                <p class="detail-price">Rp<?php echo number_format($book['harga'], 0, ',', '.'); ?></p>

                <p class="detail-deskripsi">
                    <?php echo $book['deskripsi'] ? $book['deskripsi'] : '(Belum ada deskripsi untuk buku ini)'; ?>
                </p>

                <?php if ($pesan_wishlist) { ?>
                    <p class="auth-success" style="max-width:300px;"><?php echo $pesan_wishlist; ?></p>
                <?php } ?>
                <?php if ($pesan_cart) { ?>
                    <p class="auth-success" style="max-width:300px;"><?php echo $pesan_cart; ?></p>
                <?php } ?>

                <!-- Tombol Wishlist dan Cart sekarang keduanya fungsional -->
                <div class="detail-actions">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="toggle_wishlist">
                        <button type="submit" class="btn-wishlist">
                            <?php echo $is_wishlisted ? '✅ Sudah di Wishlist (klik hapus)' : '🔖 Tambah ke Wishlist'; ?>
                        </button>
                    </form>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_to_cart">
                        <button type="submit" class="btn-cart">🛒 Tambah ke Keranjang</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========== SECTION KOMENTAR (base, belum fungsional) ========== -->
        <div class="comment-section">
            <h3>Komentar</h3>

            <!-- Form komentar, belum nyambung ke database -->
            <form class="comment-form">
                <textarea placeholder="Tulis komentar kamu di sini..." rows="3"></textarea>
                <button type="button">Kirim Komentar</button>
            </form>

            <!-- Placeholder daftar komentar -->
            <div class="comment-list">
                <p class="comment-empty">Belum ada komentar untuk buku ini.</p>

                <!--
                Nanti kalau fitur komentar udah aktif, bagian di atas diganti looping kayak gini:

                <div class="comment-item">
                    <p class="comment-author">Nama User</p>
                    <p class="comment-text">Isi komentarnya...</p>
                </div>
                -->
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