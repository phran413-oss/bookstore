<?php
// ==========================================
// admin/index.php - Halaman Overview/Statistik Admin
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil beberapa angka ringkasan buat ditampilin sebagai statistik
// mysqli_fetch_assoc(...)['jumlah'] -> langsung ambil 1 nilai dari hasil COUNT()

$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM books"))['jumlah'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM users WHERE role = 'user'"))['jumlah'];
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM categories WHERE parent_id IS NOT NULL"))['jumlah'];

// Ambil SEMUA buku, diurutkan dari stok paling sedikit
$stok_menipis = mysqli_query($conn, "SELECT judul, stok FROM books ORDER BY stok ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Overview - Admin Bookstore</title>
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
        <h2>Overview</h2>

        <!-- ========== KARTU STATISTIK ========== -->
        <div class="stat-cards">
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_buku; ?></p>
                <p class="stat-label">Total Buku</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_user; ?></p>
                <p class="stat-label">Total User</p>
            </div>
            <div class="stat-card">
                <p class="stat-number"><?php echo $total_kategori; ?></p>
                <p class="stat-label">Total Sub-Genre</p>
            </div>
        </div>

        <!-- ========== STOK MENIPIS ========== -->
        <h3 style="margin-top:30px; margin-bottom:12px;">Stok Menipis</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Stok Tersisa</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($stok_menipis)) { ?>
                    <tr>
                        <td><?php echo $row['judul']; ?></td>
                        <td><?php echo $row['stok']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

</body>
</html>
<?php
mysqli_close($conn);
?>