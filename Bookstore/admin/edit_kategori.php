<?php
// ==========================================
// admin/edit_kategori.php - Edit kategori/sub-genre
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];
$error = "";

// Kalau form disubmit (proses UPDATE)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_kategori = trim($_POST['nama_kategori']);
    $parent_id = $_POST['parent_id'] == '' ? NULL : $_POST['parent_id'];

    if ($nama_kategori == '') {
        $error = "Nama kategori tidak boleh kosong.";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE categories SET nama_kategori = ?, parent_id = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "sii", $nama_kategori, $parent_id, $id);
        mysqli_stmt_execute($stmt);

        header("Location: kategori.php");
        exit;
    }
}

// Ambil data kategori yang mau diedit
$stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$kategori = mysqli_fetch_assoc($result);

// Ambil daftar kategori utama buat dropdown "induk"
// (kecuali dirinya sendiri, biar kategori nggak bisa jadi induk buat dirinya sendiri)
$kategori_utama = mysqli_query($conn, "SELECT * FROM categories WHERE parent_id IS NULL AND id != $id ORDER BY nama_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Kategori - Admin</title>
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
        <h2>Edit Kategori: <?php echo $kategori['nama_kategori']; ?></h2>

        <?php if ($error) { ?>
            <p class="auth-error"><?php echo $error; ?></p>
        <?php } ?>

        <form method="POST" action="" class="admin-form">
            <label>Nama Kategori</label>
            <input type="text" name="nama_kategori" value="<?php echo $kategori['nama_kategori']; ?>" required>

            <label>Induk Kategori</label>
            <select name="parent_id">
                <option value="">-- Jadikan Kategori Utama --</option>
                <?php while ($k = mysqli_fetch_assoc($kategori_utama)) {
                    $selected = ($k['id'] == $kategori['parent_id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $k['id']; ?>" <?php echo $selected; ?>>
                        Sub-genre dari: <?php echo $k['nama_kategori']; ?>
                    </option>
                <?php } ?>
            </select>

            <button type="submit">Simpan Perubahan</button>
        </form>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>
