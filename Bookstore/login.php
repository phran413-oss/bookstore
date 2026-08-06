<?php
// ==========================================
// login.php - Halaman Login & Register (1 file, 2 tab)
// ==========================================

// session_start() WAJIB dipanggil paling atas, sebelum ada output apapun.
// Ini yang bikin PHP bisa "inget" user ini siapa di halaman-halaman lain.
session_start();

include 'config.php';

$error_login = "";
$error_register = "";
$success_register = "";

// ---------- PROSES LOGIN ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'login') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cari user berdasarkan email
    $stmt = mysqli_prepare($conn, "SELECT id, nama, password, role FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // password_verify() bandingin password yang diketik user
        // dengan hash yang tersimpan di database (dari password_hash() waktu register)
        if (password_verify($password, $user['password'])) {

            // LOGIN BERHASIL -> simpan data user ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            // Arahkan sesuai role
            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: index.php");
            }
            exit; // wajib, biar script berhenti setelah redirect
        } else {
            $error_login = "Password salah.";
        }
    } else {
        $error_login = "Email belum terdaftar.";
    }
}

// ---------- PROSES REGISTER ----------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'register') {

    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($cek, "s", $email);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        $error_register = "Email sudah terdaftar.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);

        if (mysqli_stmt_execute($stmt)) {
            $success_register = "Pendaftaran berhasil! Silakan login.";
        } else {
            $error_register = "Terjadi kesalahan, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login / Register - Bookstore</title>
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

    <!-- ========== FORM LOGIN / REGISTER (dengan tab) ========== -->
    <section class="auth-section">
        <div class="auth-box">

            <!-- Tombol tab: Login vs Register -->
            <div class="auth-tabs">
                <button type="button" class="tab-btn active" onclick="showTab('login')" id="tab-login">Login</button>
                <button type="button" class="tab-btn" onclick="showTab('register')" id="tab-register">Register</button>
            </div>

            <!-- ===== TAB LOGIN ===== -->
            <div id="form-login" class="tab-content">
                <?php if ($error_login) { ?>
                    <p class="auth-error"><?php echo $error_login; ?></p>
                <?php } ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">

                    <label>Email</label>
                    <input type="email" name="email" required>

                    <label>Password</label>
                    <input type="password" name="password" required>

                    <button type="submit">Login</button>
                </form>

                <div class="divider">atau</div>

                <!-- Tombol Google: TAMPILAN SAJA, belum fungsional -->
                <button type="button" class="btn-google" onclick="alert('Login Google belum aktif, nanti dikembangkan di tahap berikutnya.')">
                    <img src="assets/icons/google.png" alt="Google" class="icon-google">
                    Login dengan Google
                </button>
            </div>

            <!-- ===== TAB REGISTER ===== -->
            <div id="form-register" class="tab-content" style="display:none;">
                <?php if ($error_register) { ?>
                    <p class="auth-error"><?php echo $error_register; ?></p>
                <?php } ?>
                <?php if ($success_register) { ?>
                    <p class="auth-success"><?php echo $success_register; ?></p>
                <?php } ?>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">

                    <label>Nama</label>
                    <input type="text" name="nama" required>

                    <label>Email</label>
                    <input type="email" name="email" required>

                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">

                    <button type="submit">Daftar</button>
                </form>
            </div>

        </div>
    </section>

    <!-- ========== FOOTER ========== -->
    <footer>
        <p>Pusat buku lengkap dan terpercaya, siap menemani perjalanan dan ilmu pengetahuan baru.</p>
    </footer>

    <!-- Script kecil buat pindah tab, nggak reload halaman -->
    <script>
        function showTab(tab) {
            // Sembunyikan semua isi tab dulu
            document.getElementById('form-login').style.display = 'none';
            document.getElementById('form-register').style.display = 'none';
            document.getElementById('tab-login').classList.remove('active');
            document.getElementById('tab-register').classList.remove('active');

            // Tampilkan yang dipilih aja
            document.getElementById('form-' + tab).style.display = 'block';
            document.getElementById('tab-' + tab).classList.add('active');
        }
    </script>

</body>
</html>
<?php
mysqli_close($conn);
?>