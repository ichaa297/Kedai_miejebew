<?php
// ==========================================
// SECURITY GUARD (Penjaga Keamanan)
// ==========================================
// Cek apakah user belum login (tidak punya gelang)
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    // Jika belum login, tendang paksa ke halaman login
    header("Location: login.php");
    exit(); // Hentikan eksekusi kode dibawahnya
}

// Jika kode sampai di sini, artinya user sudah sah login
// Kita bisa menampilkan nama user yang sedang login di Navbar
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark shadow-sm">
<div class="container-fluid">
    <span class="navbar-brand mb-0 h1">Kedai Mie Jebew</span>
    <!-- Menampilkan nama user yang login di pojok kanan navbar -->
    <span class="navbar-text text-white">
        <i class="bi bi-person-circle"></i> 
        Login sebagai: <strong><?php echo $currentUser; ?></strong>
    </span>
</div>
</nav>