<?php
// ==========================================
// SECURITY GUARD (Penjaga Keamanan)
// ==========================================
// Cek apakah user belum login (tidak punya gelang)


// Jika kode sampai di sini, artinya user sudah sah login
// Kita bisa menampilkan nama user yang sedang login di Navbar
$currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">Kedai Mie Jebew</a>

<div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="menu.php">Katalog Menu</a></li>
          <li class="nav-item"><a class="nav-link" href="reservasi.php">Reservasi</a></li>
          <li class="nav-item"><a class="nav-link" href="keranjang.php">Keranjang</a></li>
          <li class="nav-item"><a class="nav-link" href="riwayat.php">Riwayat Pesanan</a></li>
          <li class="nav-item"><a class="nav-link" href="pesanan_diproses.php">Pesanan Diproses</a></li>
        </ul>

        <div class="d-flex">
          <span class="navbar-text text-light me-3 "><?php echo htmlspecialchars($_SESSION['email']); ?></span>
          <a href="logout.php">Logout</a>
        </div>
      </div>
    </div>
  </nav>