<?php
session_start();
if (!isset($_SESSION['email'])) { header('Location: login.php'); exit; }

require_once __DIR__ . '/../config/koneksi.php';
/** @var mysqli $koneksi */

$receipts = [];
$reservations = [];
$userEmail = $_SESSION['email'];

// Ambil semua pesanan untuk user (selesai dan yang masih diproses)
$id_user = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;

$sqlR = "SELECT * FROM pesanan WHERE id_user=$id_user AND (metode_bayar IS NULL OR metode_bayar != 'Reservasi') ORDER BY id_pesanan DESC";
$resR = mysqli_query($koneksi, $sqlR);
if ($resR) {
  while ($row = mysqli_fetch_assoc($resR)) {
    // Ambil item untuk pesanan ini dari detail_pesanan
    $row['item'] = [];
    $idPes = (int) ($row['id_pesanan'] ?? $row['id_order'] ?? 0);
    if ($idPes) {
      $qIt = mysqli_query($koneksi, "SELECT dp.qty, dp.harga, dp.catatan, kp.nama_produk FROM detail_pesanan dp LEFT JOIN kelola_produk kp ON dp.id_produk = kp.id_produk WHERE dp.id_pesanan = $idPes");
      if ($qIt) {
        while ($it = mysqli_fetch_assoc($qIt)) {
          $row['item'][] = [
            'name' => $it['nama_produk'] ?? 'Item',
            'qty' => (int) $it['qty'],
            'price' => (float) $it['harga'],
            'note' => $it['catatan'] ?? ''
          ];
        }
      }
    }

    // fallback total: prefer total_harga if present
    if (empty($row['total']) && !empty($row['total_harga'])) {
      $row['total'] = $row['total_harga'];
    }

    $receipts[] = $row;
  }
}

// Ambil reservasi untuk user dari tabel proses_reservasi
// Ambil reservasi (utama) untuk user
// Query reservasi untuk user. Support either id_user (if logged-in) or fallback to email for guest entries.
$userEmailEsc = mysqli_real_escape_string($koneksi, $userEmail);
$sqlRes = "SELECT * FROM proses_reservasi WHERE (id_user = $id_user) OR (email = '$userEmailEsc') ORDER BY id_reservasi DESC";
$resQ = mysqli_query($koneksi, $sqlRes);
if ($resQ) {
  while ($r = mysqli_fetch_assoc($resQ)) {
    // Ambil item dari tabel detail_reservasi (relasional)
    $r['item'] = [];
    $idRes = (int) $r['id_reservasi'];
    $qItems = mysqli_query($koneksi, "SELECT dr.qty, dr.harga, dr.catatan, kp.nama_produk FROM detail_reservasi dr LEFT JOIN kelola_produk kp ON dr.id_produk = kp.id_produk WHERE dr.id_reservasi = $idRes");
    if ($qItems) {
      while ($it = mysqli_fetch_assoc($qItems)) {
        $r['item'][] = [
          'name' => $it['nama_produk'] ?? 'Item',
          'qty' => (int) $it['qty'],
          'price' => (float) $it['harga'],
          'note' => $it['catatan'] ?? ''
        ];
      }
    }
    // Normalize column names for frontend templates
    // db has 'nama', 'tgl', 'catatan', 'total', 'status'
    if (isset($r['nama']) && !isset($r['name'])) $r['name'] = $r['nama'];
    if (isset($r['tgl']) && !isset($r['date'])) $r['date'] = $r['tgl'];
    if (isset($r['catatan']) && !isset($r['notes'])) $r['notes'] = $r['catatan'];
    if (isset($r['total']) && !isset($r['total'])) $r['total'] = $r['total'];
    $reservations[] = $r;
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Riwayat Pesanan - Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .card {border-radius:10px}
    .muted-small{color:#777}
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-item-center mb-3">
      <h3>Riwayat Pesanan</h3>
      <a href="dashboard.php" class="btn btn-light">Dashboard</a>
    </div>

    <h5>Pesanan Langsung</h5>
    <?php if (empty($receipts)): ?>
      <div class="alert alert-secondary">Belum ada pesanan langsung.</div>
    <?php else: ?>
      <?php foreach ($receipts as $idx => $r): ?>
        <div class="card mb-3 p-3">
          <div class="d-flex justify-content-between">
            <div>
              <div style="font-weight:700"><?php echo htmlspecialchars($r['nama_pemesan'] ?? ' - '); ?></div>
              <div class="muted-small"><?php echo htmlspecialchars($r['date'] ?? ''); ?> • <?php echo htmlspecialchars($r['method'] ?? ''); ?></div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:800">Rp <?php echo number_format($r['total'] ?? 0,0,',','.'); ?></div>
              <a href="struk.php" class="btn btn-sm btn-outline-primary mt-2">Lihat Struk</a>
            </div>
          </div>
          <div class="mt-2">
            <?php if (!empty($r['item'])): ?>
              <ul class="list-unstyled mb-0">
                <?php foreach ($r['item'] as $it): ?>
                  <li><?php echo htmlspecialchars($it['name']); ?> x <?php echo $it['qty']; ?> — Rp <?php echo number_format($it['price']*$it['qty'],0,',','.'); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <h5 class="mt-4">Reservasi & Pre-Order</h5>
    <?php if (empty($reservations)): ?>
      <div class="alert alert-secondary">Belum ada reservasi.</div>
    <?php else: ?>
      <?php foreach (array_reverse($reservations) as $res): ?>
        <div class="card mb-3 p-3">
          <div class="d-flex justify-content-between">
            <div>
              <?php
                // Provide safe defaults and ensure strings for htmlspecialchars
                $res_name = htmlspecialchars((string) ($res['name'] ?? $res['nama'] ?? '-'));
                $res_date = htmlspecialchars((string) ($res['date'] ?? $res['tgl'] ?? ''));
                $res_time = htmlspecialchars((string) ($res['time'] ?? ''));
                $res_place = htmlspecialchars((string) ($res['place'] ?? ''));
              ?>
              <div style="font-weight:700"><?php echo $res_name; ?></div>
              <div class="muted-small"><?php echo trim($res_date . ' ' . $res_time); ?> • <?php echo $res_place; ?></div>
            </div>
            <div style="text-align:right">
              <div style="font-weight:800">Rp <?php echo number_format($res['total'],0,',','.'); ?></div>
              <div class="mt-1">
                  <?php if (($res['status'] ?? '') === 'Dikonfirmasi'): ?>
                      <span class="badge bg-success">Dikonfirmasi Admin</span>
                  <?php else: ?>
                      <span class="badge bg-warning text-dark">Menunggu Konfirmasi</span>
                  <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="mt-2">
            <div class="muted-small">Item:</div>
            <ul>
            <?php foreach ($res['item'] as $it): ?>
              <li><?php echo htmlspecialchars($it['name']); ?> x <?php echo $it['qty']; ?> — Rp <?php echo number_format($it['price']*$it['qty'],0,',','.'); ?></li>
            <?php endforeach; ?>
            </ul>
            <?php if (!empty($res['notes'])): ?><div class="muted-small">Catatan: <?php echo htmlspecialchars($res['notes']); ?></div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</body>
</html>
