<?php
// ==========================================
// admin/tambah_buku.php - Form tambah buku baru
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

// Ambil daftar kategori, buat pilihan dropdown di form
$kategori_result = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NOT NULL");

// Kalau form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $category_id = $_POST['category_id'];
    $deskripsi = $_POST['deskripsi'];

    // Checkbox: kalau dicentang nilainya 1, kalau nggak dicentang nggak kekirim sama sekali
    // makanya dicek pakai isset()
    // is_new udah nggak dipakai lagi dari form (otomatis dari tanggal), tapi kolomnya
    // masih ada di database, jadi kita isi 0 aja biar nggak error
    $is_new = 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;

    // ---------- PROSES UPLOAD GAMBAR ----------
    $cover_image = ""; // default kosong kalau nggak upload apa-apa

    // Cek dulu apakah ada file yang diupload
    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {

        // Bikin nama file unik, biar nggak bentrok kalau ada nama sama
        // contoh hasil: 1690000000_atomichabits.jpg
        $nama_file = time() . '_' . basename($_FILES['cover']['name']);
        $lokasi_tujuan = '../assets/images/books/' . $nama_file;

        // Pindahin file dari lokasi sementara ke folder tujuan
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $lokasi_tujuan)) {
            $cover_image = $nama_file; // yang disimpan ke database cuma NAMA FILE-nya aja
        }
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO books (judul, penulis, harga, stok, category_id, deskripsi, cover_image, is_new, is_bestseller, is_trending) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssdiissiii", $judul, $penulis, $harga, $stok, $category_id, $deskripsi, $cover_image, $is_new, $is_bestseller, $is_trending);

    if (mysqli_stmt_execute($stmt)) {
        // Berhasil -> balik ke dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Gagal menambahkan buku.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="logo">📚 BOOKSTORE ADMIN</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="kategori.php">Kategori</a>
        </nav>
    </header>

    <section class="admin-section">
        <h2>Tambah Buku Baru</h2>

        <?php if ($error) { ?>
            <p class="auth-error"><?php echo $error; ?></p>
        <?php } ?>

        <!-- enctype="multipart/form-data" WAJIB ada kalau form-nya ada upload file,
             kalau lupa, file nggak bakal kekirim ke server -->
        <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
            <label>Judul</label>
            <input type="text" name="judul" required>

            <label>Penulis</label>
            <input type="text" name="penulis" required>

            <label>Harga (Rp)</label>
            <input type="number" name="harga" required>

            <label>Stok</label>
            <input type="number" name="stok" required>

            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php
                while ($kat = mysqli_fetch_assoc($kategori_result)) {
                ?>
                    <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                <?php
                }
                ?>
            </select>

            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="4"></textarea>

            <label>Cover Buku</label>
            <input type="file" name="cover" accept="image/*">

            <!-- Checkbox buat nentuin masuk section mana di Home
                 (What's new sekarang OTOMATIS berdasarkan tanggal ditambahkan, jadi nggak perlu dicentang manual) -->
            <label class="checkbox-label">
                <input type="checkbox" name="is_bestseller"> Tampilkan di "Best seller"
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="is_trending"> Tampilkan di "Trending now"
            </label>

            <button type="submit">Simpan Buku</button>
        </form>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>