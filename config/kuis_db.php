<?php
$host = "127.0.0.1";
$user = "root";
$pass = "Kamarmandi23."; // Jika gagal koneksi/access denied, ganti jadi "" (kosong)
$db   = "db_akademik";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>