<?php
// ==========================================
// admin/edit_buku.php - Form edit buku
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil id buku dari URL, contoh: edit_buku.php?id=3
$id = $_GET['id'];

$error = "";

$kategori_result = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NOT NULL");

// Kalau form disubmit (proses UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $judul = $_POST['judul'];
    $penulis = $_POST['penulis'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $category_id = $_POST['category_id'];
    $deskripsi = $_POST['deskripsi'];
    // is_new udah nggak dipakai lagi dari form (otomatis dari tanggal)
    $is_new = 0;
    $is_bestseller = isset($_POST['is_bestseller']) ? 1 : 0;
    $is_trending = isset($_POST['is_trending']) ? 1 : 0;

    // ---------- PROSES UPLOAD GAMBAR (kalau ada yang baru diupload) ----------
    // Ambil dulu nama gambar LAMA dari form (dikirim lewat hidden input di bawah)
    $cover_image = $_POST['cover_lama'];

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        // Ada gambar baru diupload -> pakai yang baru, timpa yang lama
        $nama_file = time() . '_' . basename($_FILES['cover']['name']);
        $lokasi_tujuan = '../assets/images/books/' . $nama_file;

        if (move_uploaded_file($_FILES['cover']['tmp_name'], $lokasi_tujuan)) {
            $cover_image = $nama_file;
        }
    }
    // Kalau nggak ada upload baru, $cover_image tetap pakai nilai lama dari cover_lama

    $stmt = mysqli_prepare($conn, "UPDATE books SET judul=?, penulis=?, harga=?, stok=?, category_id=?, deskripsi=?, cover_image=?, is_new=?, is_bestseller=?, is_trending=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "ssdiissiiii", $judul, $penulis, $harga, $stok, $category_id, $deskripsi, $cover_image, $is_new, $is_bestseller, $is_trending, $id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Gagal menyimpan perubahan.";
    }
}

// Ambil data buku yang mau diedit, buat ngisi form (biar nggak kosong)
$stmt = mysqli_prepare($conn, "SELECT * FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$book = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Buku - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <header class="navbar">
        <div class="logo">📚 BOOKSTORE ADMIN</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
        </nav>
    </header>

    <section class="admin-section">
        <h2>Edit Buku: <?php echo $book['judul']; ?></h2>

        <?php if ($error) { ?>
            <p class="auth-error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
            <label>Judul</label>
            <input type="text" name="judul" value="<?php echo $book['judul']; ?>" required>

            <label>Penulis</label>
            <input type="text" name="penulis" value="<?php echo $book['penulis']; ?>" required>

            <label>Harga (Rp)</label>
            <input type="number" name="harga" value="<?php echo $book['harga']; ?>" required>

            <label>Stok</label>
            <input type="number" name="stok" value="<?php echo $book['stok']; ?>" required>

            <label>Kategori</label>
            <select name="category_id" required>
                <?php
                while ($kat = mysqli_fetch_assoc($kategori_result)) {
                    // selected kalau id kategori ini sama dengan category_id buku
                    $selected = ($kat['id'] == $book['category_id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $kat['id']; ?>" <?php echo $selected; ?>>
                        <?php echo $kat['nama_kategori']; ?>
                    </option>
                <?php
                }
                ?>
            </select>

            <label>Deskripsi</label>
            <textarea name="deskripsi" rows="4"><?php echo $book['deskripsi']; ?></textarea>

            <label>Cover Buku Saat Ini</label>
            <?php if ($book['cover_image']) { ?>
                <img src="../assets/images/books/<?php echo $book['cover_image']; ?>" alt="Cover saat ini" class="current-cover">
            <?php } else { ?>
                <p style="font-size:13px; color:#888;">Belum ada cover</p>
            <?php } ?>

            <!-- Hidden input: nyimpen nama file lama, biar kalau admin nggak upload
                 gambar baru, data ini yang dipakai (gambar lama nggak hilang) -->
            <input type="hidden" name="cover_lama" value="<?php echo $book['cover_image']; ?>">

            <label>Ganti Cover (kosongkan kalau tidak ingin ganti)</label>
            <input type="file" name="cover" accept="image/*">

            <p style="font-size:12px; color:#888; margin-top:10px;">
                "What's new" ditentukan otomatis dari tanggal buku ditambahkan (7 hari terakhir).
            </p>
            <label class="checkbox-label">
                <input type="checkbox" name="is_bestseller" <?php echo $book['is_bestseller'] ? 'checked' : ''; ?>> Tampilkan di "Best seller"
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="is_trending" <?php echo $book['is_trending'] ? 'checked' : ''; ?>> Tampilkan di "Trending now"
            </label>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>