<?php
session_start();
require_once '../config/koneksi.php';

// Ambil id pesanan terakhir yang disimpan di sesi saat checkout
$receipt = null;
$id_pesanan = isset($_SESSION['id_pesanan']) ? (int) $_SESSION['id_pesanan'] : null;

if ($id_pesanan) {
  // Ambil data pesanan utama
  $sql = "SELECT * FROM pesanan WHERE id_pesanan = $id_pesanan LIMIT 1";
  $res = mysqli_query($koneksi, $sql);

  if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);

    // Ambil detail pesanan dan nama produk
    $items = [];
    $sqlItems = "SELECT dp.qty, dp.harga, dp.catatan, kp.nama_produk
           FROM detail_pesanan dp
           LEFT JOIN kelola_produk kp ON dp.id_produk = kp.id_produk
           WHERE dp.id_pesanan = $id_pesanan";
    $resItems = mysqli_query($koneksi, $sqlItems);
    if ($resItems) {
      while ($it = mysqli_fetch_assoc($resItems)) {
        $items[] = [
          'name' => $it['nama_produk'] ?? 'Produk',
          'qty' => (int) $it['qty'],
          'price' => (float) $it['harga'],
          'note' => $it['catatan'] ?? ''
        ];
      }
    }

    // Tanggal: coba beberapa nama kolom yang mungkin ada
    $date = $row['tanggal'] ?? $row['date'] ?? $row['created_at'] ?? $row['waktu'] ?? null;

    $receipt = [
      'name' => $row['nama_pemesan'] ?? $row['name'] ?? '',
      'date' => $date,
      'method' => $row['metode_bayar'] ?? $row['metode'] ?? '',
      'items' => $items,
      'total' => isset($row['total_harga']) ? (float) $row['total_harga'] : array_sum(array_map(function($i){return $i['price']*$i['qty'];}, $items))
    ];
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk Pembayaran - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container py-4">
    <h1 class="h3">Struk Pembayaran</h1>
    <?php if (!$receipt): ?>
      <div class="alert alert-info">Tidak ada struk terakhir. <a href="menu.php">Kembali ke menu</a></div>
    <?php else: ?>
      <div class="card p-3">
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($receipt['name']); ?></p>
        <p><strong>Tanggal:</strong> <?php echo htmlspecialchars($receipt['date']); ?></p>
        <p><strong>Metode:</strong> <?php echo strtoupper(htmlspecialchars($receipt['method'])); ?></p>
        <ul class="list-group mb-2">
          <?php foreach ($receipt['items'] as $it): ?>
            <li class="list-group-item">
              <div class="d-flex justify-content-between align-items-center">
                <div><?php echo htmlspecialchars($it['name']); ?> x <?php echo $it['qty']; ?></div>
                <div>Rp <?php echo number_format($it['price'] * $it['qty'],0,',','.'); ?></div>
              </div>
              <?php if (!empty($it['note'])): ?>
                <div class="small text-muted mt-1">Catatan: <?php echo htmlspecialchars($it['note']); ?></div>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="mb-1"><strong>Total:</strong> Rp <?php echo number_format($receipt['total'],0,',','.'); ?></p>
        <p class="text-success mb-0"><strong>Pembayaran berhasil</strong></p>
        <p class="text-muted small">Tunggu Pesanan Sedang Di Proses!</p>

        <div class="mt-3">
          <a href="pesanan_diproses.php" class="btn btn-primary">Lihat Pesanan Diproses</a>
          <a href="menu.php" class="btn btn-light ms-2">Kembali ke Menu</a>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>
