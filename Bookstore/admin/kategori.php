<?php
// ==========================================
// admin/kategori.php - Kelola Kategori & Sub-Genre
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

// ---------- PROSES TAMBAH KATEGORI/SUB-GENRE BARU ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama_kategori = trim($_POST['nama_kategori']);
    // Kalau pilih "Kategori Utama" di form, parent_id-nya NULL
    // Kalau pilih salah satu kategori utama, parent_id-nya diisi id itu
    $parent_id = $_POST['parent_id'] == '' ? NULL : $_POST['parent_id'];

    if ($nama_kategori == '') {
        $error = "Nama kategori tidak boleh kosong.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (nama_kategori, parent_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $nama_kategori, $parent_id);
        mysqli_stmt_execute($stmt);

        header("Location: kategori.php");
        exit;
    }
}

// Ambil kategori UTAMA (buat pilihan dropdown "Induk" di form, dan buat looping tampilan)
$kategori_utama = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY nama_kategori");

// Ambil lagi buat dropdown (query terpisah, biar nggak konflik sama looping tampilan di bawah)
$kategori_utama_dropdown = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kategori - Admin Bookstore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- ========== NAVBAR ADMIN ========== -->
    <header class="navbar">
        <div class="logo">📚 BOOKSTORE ADMIN</div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="transaksi.php">Transaksi</a>
            <a href="kategori.php">Kategori</a>
        </nav>
        <div class="nav-icons">
            <span class="user-info">Halo, <?php echo $_SESSION['nama']; ?></span>
            <a href="../logout.php" class="logout-link">Logout</a>
        </div>
    </header>

    <section class="admin-section">
        <h2>Kelola Kategori & Sub-Genre</h2>

        <!-- ========== FORM TAMBAH ========== -->
        <div class="kategori-form-box">
            <h3>Tambah Baru</h3>

            <?php if ($error) { ?>
                <p class="auth-error"><?php echo $error; ?></p>
            <?php } ?>

            <form method="POST" action="" class="admin-form kategori-inline-form">
                <input type="text" name="nama_kategori" placeholder="Nama kategori/sub-genre" required>

                <select name="parent_id">
                    <option value="">-- Jadikan Kategori Utama --</option>
                    <?php while ($k = mysqli_fetch_assoc($kategori_utama_dropdown)) { ?>
                        <option value="<?php echo $k['id']; ?>">Sub-genre dari: <?php echo $k['nama_kategori']; ?></option>
                    <?php } ?>
                </select>

                <button type="submit">+ Tambah</button>
            </form>
            <p style="font-size:12px; color:#888; margin-top:8px;">
                Kosongkan pilihan "induk" kalau mau bikin kategori utama baru (setara Fiksi/Non-Fiksi).
                Pilih salah satu kalau mau bikin sub-genre di dalamnya.
            </p>
        </div>

        <!-- ========== LIST KATEGORI + SUB-GENRE ========== -->
        <?php while ($main = mysqli_fetch_assoc($kategori_utama)) {
            $main_id = $main['id'];
            $sub_result = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id = $main_id ORDER BY nama_kategori");
        ?>
            <div class="kategori-group">
                <div class="kategori-group-header">
                    <h3>#<?php echo $main['nama_kategori']; ?></h3>
                    <div class="kategori-actions">
                        <a href="edit_kategori.php?id=<?php echo $main['id']; ?>" class="btn-edit">Edit</a>
                        <a href="hapus_kategori.php?id=<?php echo $main['id']; ?>"
                           class="btn-delete"
                           onclick="return confirm('Hapus kategori ini? Semua sub-genre di dalamnya juga akan terhapus!');">Hapus</a>
                    </div>
                </div>

                <ul class="kategori-sub-list">
                    <?php
                    if (mysqli_num_rows($sub_result) == 0) {
                    ?>
                        <li class="kategori-empty">Belum ada sub-genre.</li>
                    <?php
                    } else {
                        while ($sub = mysqli_fetch_assoc($sub_result)) {
                    ?>
                        <li>
                            <span><?php echo $sub['nama_kategori']; ?></span>
                            <div class="kategori-actions">
                                <a href="edit_kategori.php?id=<?php echo $sub['id']; ?>" class="btn-edit">Edit</a>
                                <a href="hapus_kategori.php?id=<?php echo $sub['id']; ?>"
                                   class="btn-delete"
                                   onclick="return confirm('Hapus sub-genre ini?');">Hapus</a>
                            </div>
                        </li>
                    <?php
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php } ?>

    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>
