<?php
// ==========================================
// register.php - Form pendaftaran user baru
// ==========================================

include 'config.php';

$error = "";     // buat nampung pesan error kalau ada masalah
$success = "";   // buat nampung pesan sukses

// Cek: apakah form ini baru di-submit? (method POST = form dikirim)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil data dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. Cek dulu apakah email udah dipakai orang lain
    //    Pakai prepared statement (?) biar aman dari SQL Injection
    $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        // Kalau email udah ada di database
        $error = "Email sudah terdaftar, coba pakai email lain.";
    } else {
        // 2. Hash password sebelum disimpan (JANGAN PERNAH simpan password polos!)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 3. Insert data user baru ke database
        //    role dikosongin di sini, otomatis kepake default 'user' dari skema tabel
        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);

        if (mysqli_stmt_execute($stmt)) {
            $success = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Bookstore</title>
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
            <a href="#">Forum</a>
        </nav>
    </header>

    <!-- ========== FORM REGISTER ========== -->
    <section class="auth-section">
        <div class="auth-box">
            <h2>Daftar Akun Baru</h2>

            <?php if ($error) { ?>
                <p class="auth-error"><?php echo $error; ?></p>
            <?php } ?>

            <?php if ($success) { ?>
                <p class="auth-success"><?php echo $success; ?> <a href="login.php">Login di sini</a></p>
            <?php } ?>

            <!-- action="" artinya form dikirim ke file ini sendiri (register.php) -->
            <form method="POST" action="">
                <label>Nama</label>
                <input type="text" name="nama" required>

                <label>Email</label>
                <input type="email" name="email" required>

                <label>Password</label>
                <input type="password" name="password" required minlength="6">

                <button type="submit">Daftar</button>
            </form>

            <p class="auth-switch">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer>
        <p>Pusat buku lengkap dan terpercaya, siap menemani perjalanan dan ilmu pengetahuan baru.</p>
    </footer>

</body>
</html>
<?php
mysqli_close($conn);
?>
