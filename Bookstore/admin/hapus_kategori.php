<?php
// ==========================================
// admin/hapus_kategori.php - Proses hapus kategori
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

// Catatan: di skema database, kolom parent_id punya "ON DELETE SET NULL"
// Jadi kalau hapus kategori UTAMA, sub-genre di dalamnya BUKAN ikut kehapus,
// tapi parent_id-nya otomatis jadi NULL (jadi kategori utama "yatim piatu").
// Buat latihan ini udah cukup aman, nggak bikin data planet lain rusak.

$stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: kategori.php");
exit;
?>
