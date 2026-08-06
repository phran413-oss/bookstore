<?php
// ==========================================
// admin/dashboard.php - Halaman utama Admin
// ==========================================

session_start();

// Path config.php pakai '../' karena file ini ada di dalam folder admin/
// jadi harus "naik 1 folder dulu" buat nemuin config.php di folder utama
include '../config.php';

// ---------- PROTEKSI HALAMAN ADMIN ----------
// Kalau belum login ATAU role-nya bukan admin -> tendang ke halaman login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua data buku, urut dari yang terbaru
$query = "SELECT * FROM books ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Bookstore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <!-- ========== NAVBAR ADMIN (lebih simpel dari navbar user) ========== -->
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
        <div class="admin-header">
            <h2>Kelola Buku</h2>
            <a href="tambah_buku.php" class="btn-add">+ Tambah Buku</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cover</th>
                    <th>Judul</th>
                    <th>Penulis</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Looping tiap buku, tampilin jadi 1 baris tabel
                while ($book = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?php echo $book['id']; ?></td>
                        <td>
                            <?php if ($book['cover_image']) { ?>
                                <img src="../assets/images/books/<?php echo $book['cover_image']; ?>" alt="<?php echo $book['judul']; ?>" class="table-cover">
                            <?php } else { ?>
                                <span style="font-size:12px; color:#999;">-</span>
                            <?php } ?>
                        </td>
                        <td><?php echo $book['judul']; ?></td>
                        <td><?php echo $book['penulis']; ?></td>
                        <td>Rp<?php echo number_format($book['harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $book['stok']; ?></td>
                        <td class="admin-actions">
                            <a href="edit_buku.php?id=<?php echo $book['id']; ?>" class="btn-edit">Edit</a>
                            <a href="hapus_buku.php?id=<?php echo $book['id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Yakin mau hapus buku ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>