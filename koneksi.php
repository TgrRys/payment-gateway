<?php
$host = "localhost"; // Host MySQL Anda
$username = "root"; // Nama pengguna MySQL Anda
$password = ""; // Kata sandi MySQL Anda
$database = "transaksimeta"; // Nama database Anda

// Membuat koneksi ke database
$conn = mysqli_connect($host, $username, $password, $database);

// Memeriksa apakah koneksi berhasil
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set karakter set utf8 untuk koneksi
mysqli_set_charset($conn, "utf8");
?>
