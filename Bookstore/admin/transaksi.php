<?php
// ==========================================
// admin/transaksi.php - Daftar Pesanan Masuk
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Ambil semua order, JOIN ke users biar dapet nama pemesannya
// Pesanan yang "Menunggu Diproses" ditaruh paling atas (belum diurus duluan)
$result = mysqli_query($conn, "
    SELECT orders.*, users.nama AS nama_user
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY
        CASE WHEN orders.status = 'Menunggu Diproses' THEN 0 ELSE 1 END,
        orders.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi - Admin Bookstore</title>
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
        <h2>Transaksi</h2>
        <p style="color:#888; font-size:13px; margin-bottom:20px;">
            Daftar pesanan yang masuk dari checkout user. Klik salah satu buat lihat detail dan proses pesanannya.
        </p>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Metode Bayar</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) == 0) {
                ?>
                    <tr><td colspan="7" style="text-align:center; color:#999;">Belum ada pesanan masuk.</td></tr>
                <?php
                } else {
                    while ($order = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo $order['nama_user']; ?></td>
                        <td>Rp<?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                        <td><?php echo $order['metode_pembayaran']; ?></td>
                        <td><?php echo date('d M Y, H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <?php if ($order['status'] == 'Menunggu Diproses') { ?>
                                <span class="status-pending">⏳ Menunggu</span>
                            <?php } else { ?>
                                <span class="status-selesai">✅ Selesai</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="transaksi_detail.php?id=<?php echo $order['id']; ?>" class="btn-edit">Detail</a>
                        </td>
                    </tr>
                <?php
                    }
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