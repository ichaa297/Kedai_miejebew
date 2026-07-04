<?php
// File koneksi universal untuk project ORMAWA
$server = "localhost";
$user = "root";
$pass = ""; // Default Laragon/XAMPP kosong
$db = "db_kedaimiejebew";

// Membuat koneksi
$koneksi = mysqli_connect($server, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
