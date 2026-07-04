<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    echo "Login berhasil";
    header('Location: login.php');
    exit;
}

require_once '../config/koneksi.php';
// gather stats via DB; if DB empty/fails, fallback to JSON
$receipts = $reservations = [];
$customers = [];
$revenue = 0.0;

// Build a date window for the chart: last N days (including today)
$daysWindow = 7;
$revenueByDate = [];
for ($i = $daysWindow - 1; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $revenueByDate[$d] = 0.0;
}

// Total orders and receipts (from `pesanan` table)
$totalOrders = 0;
$sqlOrders = "SELECT * FROM pesanan ORDER BY id_pesanan DESC";
if ($resOrders = mysqli_query($koneksi, $sqlOrders)) {
  while ($row = mysqli_fetch_assoc($resOrders)) {
    $receipts[] = $row;
    $totalOrders++;
    // Build a stable unique key for customers: prefer id_user, then nama_pemesan, then email
    $custKey = null;
    if (!empty($row['id_user']) && (int)$row['id_user'] > 0) {
      $custKey = 'u:' . (int)$row['id_user'];
    } elseif (!empty($row['nama_pemesan'])) {
      $custKey = 'n:' . strtolower(trim($row['nama_pemesan']));
    } elseif (!empty($row['name'])) {
      $custKey = 'n:' . strtolower(trim($row['name']));
    } elseif (!empty($row['nama'])) {
      $custKey = 'n:' . strtolower(trim($row['nama']));
    } elseif (!empty($row['email'])) {
      $custKey = 'e:' . strtolower(trim($row['email']));
    }
    if ($custKey !== null) $customers[$custKey] = true;

    // Prefer numeric total_harga, fallback to total or 0
    $t = 0.0;
    if (!empty($row['total_harga'])) $t = (float)$row['total_harga'];
    elseif (!empty($row['total'])) $t = (float)$row['total'];
    $revenue += $t;

    // Determine a date for this order using common column names
    $dRaw = $row['date'] ?? ($row['tanggal'] ?? ($row['created'] ?? ($row['created_at'] ?? null)));
    // if still empty, try other fallbacks
    if (empty($dRaw) && !empty($row['waktu'])) $dRaw = $row['waktu'];

    if (!empty($dRaw)) {
      $parsed = @strtotime((string)$dRaw);
      if ($parsed !== false) {
        $d = date('Y-m-d', $parsed);
        if (isset($revenueByDate[$d])) {
          $revenueByDate[$d] += $t;
        }
      }
    }
  }
}

// Recent reservations (from `proses_reservasi`)
$sqlRes = "SELECT * FROM proses_reservasi ORDER BY id_reservasi DESC LIMIT 8";
if ($resQ = mysqli_query($koneksi, $sqlRes)) {
  while ($r = mysqli_fetch_assoc($resQ)) {
    $reservations[] = $r;
  }
}

// Include reservation submitters into the unique customer set as well
foreach ($reservations as $rv) {
  $custKey = null;
  if (!empty($rv['id_user']) && (int)$rv['id_user'] > 0) {
    $custKey = 'u:' . (int)$rv['id_user'];
  } elseif (!empty($rv['email'])) {
    $custKey = 'e:' . strtolower(trim($rv['email']));
  } elseif (!empty($rv['nama'])) {
    $custKey = 'n:' . strtolower(trim($rv['nama']));
  }
  if ($custKey !== null) $customers[$custKey] = true;
}

$customerCount = count($customers);
$chartLabels = array_keys($revenueByDate);

$chartData = array_values($revenueByDate);
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php require_once 'components/header.php'; ?>

<div class="container-fluid">
  <div class="row">

    <?php require_once 'components/sidebar.php'; ?>

    <!-- KONTEN UTAMA -->
    <div class="col-md-9 p-4" style="background: linear-gradient(135deg,#8b0000,#ff3c3c); min-height:100vh;">
        <div class="d-flex justify-content-between align-items-center text-color-light mb-4">
          <h5 class="mb-0">Admin Dashboard</h5>
        </div>

        <div class="row mb-2 g-3">
          <div class="col-md-4">
            <div class="card border-start border-2 border-primary shadow-sm" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
              <div class="card-body">
                <div class="muted-small text-dark">Total Order</div>
                <div class="text-dark" style="font-size:1.4rem;font-weight:800"><?php echo (int)$totalOrders; ?></div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-start border-2 border-success shadow-sm" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
              <div class="card-body">
                <div class="muted-small text-dark">Total Pelanggan</div>
                <div class="text-dark" style="font-size:1.4rem;font-weight:800"><?php echo (int)$customerCount; ?></div>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card border-start border-2 border-warning shadow-sm" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);">
              <div class="card-body">
                <div class="muted-small text-dark">Pendapatan</div>
                <div class="text-dark" style="font-size:1.4rem;font-weight:800">Rp <?php echo number_format($revenue, 0, ',', '.'); ?></div>
              </div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-lg-12">
            <div class="card p-4 mb-4 text-dark shadow-sm" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color:#000;">
              <h5 style="color:#000">Pendapatan</h5>
              <canvas id="revenueChart" height="120"></canvas>
            </div>

        <div class="row g-3">    
          <div class="col-lg-6">
            <div class="card p-3 mb-3 text-dark" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color:#000;">
              <h5 style="color:#000">Reservasi Terbaru</h5>
              <?php if (empty($reservations)): ?>
                <div class="muted-small">Belum ada reservasi.</div>
              <?php else: ?>
                <ul class="list-group list-group-flush">
                  <?php foreach (array_slice($reservations, 0, 6) as $res): ?> 
                    <?php
                      $r_name = htmlspecialchars((string) ($res['name'] ?? $res['nama'] ?? '-'));
                      $r_date = htmlspecialchars((string) ($res['date'] ?? $res['tgl'] ?? ''));
                      $r_time = htmlspecialchars((string) ($res['time'] ?? ''));
                      $r_place = htmlspecialchars((string) ($res['place'] ?? ''));
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-secondary" style="color:#000">
                      <div style="font-weight:700"><?php echo $r_name; ?></div>
                      <div class="muted-small">
                        <?php echo trim($r_date . ' ' . $r_time); ?> • <?php echo $r_place; ?>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>
            
          <div class="col-lg-6">
            <div class="card p-3 mb-3 text-dark" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color:#000;">
              <h5 style="color:#000">Order Terakhir</h5>
              <?php if (empty($receipts)): ?>
                <div class="muted-small">Belum ada pesanan.</div>
              <?php else: ?>
                <ul class="list-group">
                  <?php foreach (array_slice($receipts, 0, 8) as $r): ?>
                    <?php
                      // Prefer explicit customer name column if present
                      $rcp_name = htmlspecialchars((string) ($r['nama_pemesan'] ?? $r['name'] ?? $r['nama'] ?? $r['email'] ?? '-'));

                      $rcp_method = htmlspecialchars((string) ($r['method'] ?? $r['metode_bayar'] ?? 'Reservasi'));
                      $rcp_total = isset($r['total'] ) ? $r['total'] : ($r['total_harga'] ?? 0);
                      // normalize order date
                      $orderDateRaw = $r['date'] ?? $r['created'] ?? $r['created_at'] ?? null;
                      $orderDate = '';
                      if (!empty($orderDateRaw)) {
                        $ts2 = @strtotime((string)$orderDateRaw);
                        if ($ts2 !== false) $orderDate = date('Y-m-d', $ts2);
                      }
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent" style="color:#000">
                      <div>
                        <div style="font-weight:700"><?php echo $rcp_name; ?></div>
                        <div class="muted-small" style="color:#000">
                         <?php echo htmlspecialchars($orderDate ?? ''); ?> • <?php echo $rcp_method; ?>
                        </div>
                      </div>
                      <div style="font-weight:800">Rp <?php echo number_format($rcp_total ?? 0, 0, ',', '.'); ?></div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </div>
          </div>


          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = <?php echo json_encode($chartLabels); ?>;
    const data = <?php echo json_encode($chartData); ?>;
    const ctx = document.getElementById('revenueChart');

    if (ctx) {
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Pendapatan',
              data: data,
              backgroundColor: 'rgba(255,75,46,0.12)',
              borderColor: 'rgba(255,75,46,0.9)',
              fill: true
            }
          ]
        },
        options: {
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    }
  </script>

</body>
</html>

