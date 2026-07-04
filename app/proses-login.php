<?php
session_start();
require_once '../config/koneksi.php';

$email = $_POST['email'];
$password = $_POST['password'];


$query = mysqli_query(
    $koneksi,
    "SELECT * FROM pengguna WHERE email='$email'"
);

if(mysqli_num_rows($query) == 1){

    $user = mysqli_fetch_assoc($query);

    if(password_verify($password, $user['password'])){

        $_SESSION['is_logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];

        header("Location: dashboard.php");
        exit;
    }
}

$_SESSION['login_error'] = "Email atau Password salah";
header("Location: login.php");
exit;