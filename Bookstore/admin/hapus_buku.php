<?php
// ==========================================
// admin/hapus_buku.php - Proses hapus buku
// ==========================================

session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];

$stmt = mysqli_prepare($conn, "DELETE FROM books WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

// Habis dihapus, langsung balik ke dashboard (nggak ada halaman ditampilin)
header("Location: dashboard.php");
exit;
?>