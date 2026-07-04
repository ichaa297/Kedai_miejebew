<?php
session_start();
require_once '../config/koneksi.php';

$nama = mysqli_real_escape_string($koneksi,$_POST['nama']);
$email = mysqli_real_escape_string($koneksi,$_POST['email']);
$password = $_POST['password'];

$cek = mysqli_query(
    $koneksi,
    "SELECT * FROM pengguna WHERE email='$email'"
);

if(mysqli_num_rows($cek)>0){
    die("Email sudah terdaftar");
}

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

mysqli_query(
    $koneksi,
    "INSERT INTO pengguna(nama,email,password)
    VALUES('$nama','$email','$passwordHash')"
);

header("Location: login.php");
exit;