<?php
session_start();
require_once '../config/koneksi.php';
$id_user = $_SESSION['id_user'];

$query = mysqli_query($koneksi,"
SELECT *
FROM pesanan
WHERE id_user='$id_user'
AND status != 'Selesai'
AND (metode_bayar IS NULL OR metode_bayar != 'Reservasi')
ORDER BY id_pesanan DESC
");
$active = [];

while($row = mysqli_fetch_assoc($query)){
    $active[] = $row;
}
if (!isset($_SESSION['email'])) { header('Location: login.php'); exit; }

$id_user = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;

// Ambil pesanan aktif (belum selesai)


// Ambil pesanan selesai (sebagian riwayat singkat)
$sqlCompleted = "SELECT * FROM pesanan WHERE id_user = $id_user AND status = 'Selesai' ORDER BY id_pesanan DESC LIMIT 10";
$resCompleted = mysqli_query($koneksi, $sqlCompleted);
$completed = [];
if ($resCompleted) {
  while ($r = mysqli_fetch_assoc($resCompleted)) $completed[] = $r;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="refresh" content="5">
  <title>Pesanan Diproses - Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .stepper{display:flex;gap:12px;align-items:center}
    .step{width:12px;height:12px;border-radius:50%;background:#444}
    .step.active{background:#ff4b2e}
    .receipt-box{background:#fff;padding:12px;border-radius:8px;color:#111}
    .muted-small{color:#666}
    .order-row{display:flex;gap:16px}
    @media(max-width:768px){.order-row{flex-direction:column}}
  </style>
</head>
<body>
  <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
      <h3>Pesanan Sedang Diproses</h3>
      <a href="dashboard.php" class="btn btn-light">Dashboard</a>
    </div>

    <?php if (isset($_GET['action']) && isset($_GET['id'])): ?>
      <div class="alert alert-warning">Operasi dari admin diarahkan melalui <strong>admin/manage_processing.php</strong>.</div>
    <?php endif; ?>

    <?php if (empty($active)): ?>
      <div class="alert alert-secondary">Saat ini tidak ada pesanan yang sedang diproses.</div>
    <?php else: ?>
      <?php foreach ($active as $ordIndex => $o):
        // Prepare display values for this order
        $orderId = $o['id_pesanan'];
        $status = $o['status'] ?? '';
        $method = $o['metode_bayar'] ?? $o['metode'] ?? '';
        $created = $o['tanggal_pesanan'] ?? $o['tanggal'] ?? $o['date'] ?? $o['created_at'] ?? '';
        $total = isset($o['total_harga']) ? $o['total_harga'] : null;

        // determine progress (0..3)
        switch ($status) {
            case 'Diterima': $progress = 0; break;
            case 'Dimasak': $progress = 1; break;
            case 'Ditunggu': $progress = 2; break;
            case 'Selesai': $progress = 3; break;
            default: $progress = 0; break;
        }

        // Load items for this order
        $items = [];
        $sqlItems = "SELECT dp.qty, dp.harga, dp.catatan, kp.nama_produk
                     FROM detail_pesanan dp
                     LEFT JOIN kelola_produk kp ON dp.id_produk = kp.id_produk
                     WHERE dp.id_pesanan = $orderId";
        $resItems = mysqli_query($koneksi, $sqlItems);
        if ($resItems) {
            while ($it = mysqli_fetch_assoc($resItems)) {
                $items[] = [
                    'name' => $it['nama_produk'] ?? 'Item',
                    'qty' => (int) $it['qty'],
                    'price' => (float) $it['harga'],
                    'note' => $it['catatan'] ?? ''
                ];
            }
        }

        // If total not present in main table, compute from items
        if ($total === null) {
            $total = array_sum(array_map(function($i){return $i['price'] * $i['qty'];}, $items));
        }
      ?>
        <div class="card mb-3 p-3">
          <div class="order-row">
            <div style="flex:1">
              <div class="d-flex justify-content-between">
                <div>
                  <div style="font-weight:700">Pesanan #<?php echo htmlspecialchars($orderId); ?></div>
                  <div class="muted-small"><?php echo htmlspecialchars($created); ?></div>
                </div>
                <div class="text-end muted-small">
                  <?php echo htmlspecialchars(ucfirst($method)); ?>
                </div>
              </div>

              <div class="mt-3">
                <div class="stepper">
                  <div class="step <?php echo $progress>=0 ? 'active' : ''; ?>" title="Diterima"></div>
                  <div style="flex:1;height:4px;background:rgba(0,0,0,0.06)"></div>
                  <div class="step <?php echo $progress>=1 ? 'active' : ''; ?>" title="Dimasak"></div>
                  <div style="flex:1;height:4px;background:rgba(0,0,0,0.06)"></div>
                  <div class="step <?php echo $progress>=2 ? 'active' : ''; ?>" title="Ditunggu"></div>
                  <div style="flex:1;height:4px;background:rgba(0,0,0,0.06)"></div>
                  <div class="step <?php echo $progress>=3 ? 'active' : ''; ?>" title="Selesai"></div>
                  <div style="flex:1;height:4px;background:rgba(0,0,0,0.06)"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 muted-small">
                  <div>Diterima</div>
                  <div>Dimasak</div>
                  <div>Ditunggu</div>
                  <div>Selesai</div>
                </div>
                <?php
                $statusText = [
                    0 => 'Pesanan Diterima',
                    1 => 'Sedang Dimasak',
                    2 => 'Menunggu Dipanggil',
                    3 => 'Pesanan Selesai'
                ];
                ?>

                <div class="mt-2 fw-bold text-warning">
                    <?php echo htmlspecialchars($statusText[$progress] ?? ''); ?>
                </div>
              </div>
            </div>

            <div style="width:320px">
              <div class="receipt-box">
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <div style="font-weight:700">Struk Pesanan</div>
                  <div class="muted-small"><?php echo htmlspecialchars($created); ?></div>
                </div>
                <hr>
                <?php if (!empty($items)): ?>
                  <div style="max-height:180px;overflow:auto">
                  <?php foreach ($items as $it):
                    $name = $it['name'] ?? 'Item';
                    $qty = $it['qty'] ?? 1;
                    $price = $it['price'] ?? 0;
                  ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                      <div>
                        <div style="font-weight:600"><?php echo htmlspecialchars($name); ?></div>
                        <?php if (!empty($it['note'])): ?><div class="muted-small">Catatan: <?php echo htmlspecialchars($it['note']); ?></div><?php endif; ?>
                      </div>
                      <div class="muted-small"><?php echo $qty; ?> x Rp <?php echo number_format($price,0,',','.'); ?></div>
                    </div>
                  <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div class="muted-small">Tidak ada item pada struk.</div>
                <?php endif; ?>
                <hr>
                <div style="display:flex;justify-content:space-between;align-items:center">
                  <div style="font-weight:700">Total</div>
                  <div style="font-weight:800">Rp <?php echo number_format($total,0,',','.'); ?></div>
                </div>
                <div class="mt-2 text-end">
                  <a href="struk.php" class="btn btn-sm btn-outline-primary">Lihat Lengkap</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($completed)): ?>
      <h5 class="mt-4">Pesanan Selesai</h5>
      <?php foreach ($completed as $c): ?>
        <div class="card p-3 mb-2">
          <div class="d-flex justify-content-between">
            <div><?php echo htmlspecialchars($c['name'] ?? '-'); ?></div>
            <div class="muted-small">Rp <?php echo number_format($c['total'] ?? 0,0,',','.'); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</body>
</html>
