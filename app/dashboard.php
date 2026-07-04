<?php
session_start();

require_once '../config/koneksi.php';
// simple auth guard
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// tampilkan pesanan diproses terbaru seperti di app/pesanan_diproses.php
$active = [];
// coba ambil data proses dari tabel pesanan (status bukan Selesai)
if (isset($_SESSION['id_user'])) {
  $id_user = (int) $_SESSION['id_user'];
  $sql = "SELECT * FROM pesanan WHERE id_user = $id_user AND status != 'Selesai' ORDER BY id_pesanan DESC";
  $q = mysqli_query($koneksi, $sql);
  if ($q) {
    while ($r = mysqli_fetch_assoc($q)) {
      $active[] = $r;
    }
  }
}

// fallback ke session jika DB kosong
if (empty($active)) {
  $active = $_SESSION['processing_orders'] ?? $_SESSION['active'] ?? [];
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php require_once 'components/header.php'; ?>

  <div class="container py-4">
    <h1 class="h3 mb-3">Dashboard</h1>

    <div class="row">
      <div class="col-md-12">
        <div class="card mb-4 p-4">
          <h6>Ringkasan</h6>
          <p>
            Selamat datang, <?php echo htmlspecialchars($_SESSION['email']); ?>.
            Gunakan menu di atas untuk mengakses fitur.
          </p>
          <div class="d-flex gap-1">
            <a href="menu.php" class="btn btn-primary">Buka Katalog</a>
            <a href="keranjang.php" class="btn btn-success">Lihat Keranjang</a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>

