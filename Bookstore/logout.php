<?php
// ==========================================
// logout.php - Proses keluar/logout
// ==========================================

session_start();

// session_destroy() ngapus semua data session user ini
// (nama, user_id, role yang tadi kita simpan pas login)
session_destroy();

// Setelah logout, arahkan balik ke halaman Home
header("Location: index.php");
exit;
?>
