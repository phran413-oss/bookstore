<?php
// File koneksi ke database MySQL/MariaDB

$host = "localhost";
$user = "root";
$pass = "";          // default XAMPP kosong
$dbname = "bookstore";

// Bikin koneksi pakai mysqli
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Cek kalau koneksi gagal
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>