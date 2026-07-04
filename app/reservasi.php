<?php
session_start();
if (!isset($_SESSION['email'])) {
  header('Location: login.php');
  exit;
}

// Ambil menu dari database (kelola_produk)
require_once __DIR__ . '/../config/koneksi.php';
$menu = [];
$sql = "SELECT id_produk AS id, nama_produk AS name, harga AS price, gambar AS img FROM kelola_produk WHERE stok > 0 ORDER BY id_produk DESC";
$res = mysqli_query($koneksi, $sql);
if ($res) {
  while ($r = mysqli_fetch_assoc($res)) {
    $menu[] = $r;
  }
}

// price normalizer: convert formatted or string prices to numeric
if (!function_exists('_normalize_price')) {
  function _normalize_price($raw) {
    if (is_numeric($raw)) return (float)$raw;
    $digits = preg_replace('/[^0-9]/', '', (string)$raw);
    return $digits === '' ? 0.0 : (float)$digits;
  }
}

// No server-side save here — form will POST to admin handler (reseravsi saved into DB there)
$success = '';
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reservasi - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    .menu-card{cursor:pointer;border-radius:10px;overflow:hidden;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02)}
    .menu-card:hover{box-shadow:0 8px 30px rgba(0,0,0,0.4)}
    .qty-input{width:72px}
    .success{padding:12px;background:#e9f7ef;border-radius:8px;color:#1f7a2e}
    .error{padding:12px;background:#fdecea;border-radius:8px;color:#a33}
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>Reservasi</h2>
      <a href="dashboard.php" class="btn btn-light">Kembali</a>
    </div>

    <?php if (isset($_GET['ok'])): ?>
      <div class="success mb-3">Reservasi berhasil disimpan. Cek di Riwayat jika ingin melihat detail.</div>
    <?php endif; ?>
    <?php if ($success && strpos($success,'harus')!==false): ?>
      <div class="error mb-3"><?php echo htmlspecialchars($success); ?></div>
    <?php elseif ($success && isset($_GET['ok'])): ?>
      <!-- ok message already printed above -->
    <?php endif; ?>


  <!-- kirim ke handler yang menyimpan reservasi ke database -->
    <form method="POST" action="../admin/reservasi-proses.php" id="reserveForm">
      <!-- Hidden fields to match admin handler names -->
      <input type="hidden" name="nama" id="hf_nama" value="">
      <input type="hidden" name="email" id="hf_email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>">
      <input type="hidden" name="wa" id="hf_wa" value="">
      <input type="hidden" name="tgl" id="hf_tgl" value="">
      <input type="hidden" name="item" id="hf_item" value="">
      <input type="hidden" name="total" id="hf_total" value="0">
      <input type="hidden" name="catatan" id="hf_catatan" value="">
      <input type="hidden" name="status" id="hf_status" value="Menunggu">
      <div class="row">
        <div class="col-lg-6">
          <div class="card p-3 mb-3">
            <h6>Form Reservasi</h6>
            <div class="mb-2">
              <label class="form-label">Nama Lengkap</label>
              <input name="name" class="form-control" required>
            </div>
            <div class="mb-2">
              <label class="form-label">Nomor WhatsApp</label>
              <input name="phone" class="form-control">
            </div>
            <div class="row">
              <div class="col-6 mb-2"><label class="form-label">Tanggal</label><input type="date" name="date" class="form-control"></div>
              <div class="col-6 mb-2"><label class="form-label">Jam</label><input type="time" name="time" class="form-control"></div>
            </div>
            <div class="row">
              <div class="col-6 mb-2"><label class="form-label">Jumlah Orang</label><input name="people" class="form-control" placeholder="1"></div>
              <div class="col-6 mb-2"><label class="form-label">Pilih Tempat</label>
                <select name="place" class="form-select"><option>Indoor</option><option>Outdoor</option></select>
              </div>
            </div>
            <div class="mb-2"><label class="form-label">Catatan</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
          </div>

            <div class="card p-3 mb-3">
            <h6>Pilih Menu (klik untuk tambah)</h6>
            <div class="row g-3">
              <?php if (empty($menu)): ?>
                <div class="col-12"><div class="alert alert-secondary">Tidak ada menu tersedia untuk reservasi saat ini.</div></div>
              <?php else: ?>
                <?php foreach($menu as $m): ?>
                  <?php
                    // normalize image path: ensure it points to assets or uploads correctly
                    $imgPath = $m['img'] ?? '';
                    if ($imgPath !== '' && strpos($imgPath, 'http') !== 0 && strpos($imgPath, '../') !== 0) {
                        $imgPath = '../' . ltrim($imgPath, '/');
                    }
                  ?>
                  <div class="col-12 col-md-6">
                    <div class="menu-card p-2 d-flex gap-2 align-items-center" data-id="<?php echo $m['id']; ?>" onclick="incQty(<?php echo $m['id']; ?>)">
                      <img src="<?php echo htmlspecialchars($imgPath); ?>" style="width:84px;height:64px;object-fit:cover;border-radius:8px">
                      <div style="flex:1">
                        <div style="font-weight:700"><?php echo htmlspecialchars($m['name']); ?></div>
                        <?php $priceVal = _normalize_price($m['price'] ?? 0); ?>
                        <div class="text-muted">Rp <?php echo number_format($priceVal,0,',','.'); ?></div>
                      </div>
                      <div>
                        <input type="number" min="0" name="qty[<?php echo $m['id']; ?>]" id="qty_<?php echo $m['id']; ?>" value="0" class="form-control qty-input" data-price="<?php echo htmlspecialchars($priceVal); ?>">
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <div class="col-lg-6">
          <div class="card p-3 mb-3">
            <h6>Ringkasan</h6>
            <div class="mb-2">Total Item: <strong id="summaryItems">0</strong></div>
            <div class="mb-2">Total Harga: <strong id="summaryTotal">Rp 0</strong></div>
            <div class="mb-3">
              <label class="form-label">Metode Pembayaran</label>
              <select id="payMethod" class="form-select">
                <option value="cod">Bayar di Tempat</option>
                <option value="qris">QRIS</option>
              </select>

              <div id="qrisPreview" style="display:none" class="mt-2">
                <div class="card p-3 text-center">
                  <p>Scan QRIS untuk membayar</p>
                  <img src="../assets/img/image_qris.png" style="max-width:240px;display:block;margin:0 auto" alt="QRIS">
                  <div class="small text-muted mt-2">Setelah bayar, tunggu konfirmasi admin.</div>
                </div>
              </div>

            <div class="d-flex gap-2 mt-3">
              <button type="submit" name="reserve" class="btn btn-primary">Submit Reservasi</button>
              <a href="dashboard.php" class="btn btn-secondary">Batal</a>
            </div>
          </div>

          <div class="card p-3">
            <h6>Instruksi</h6>
            <p class="text-muted">Klik kartu menu untuk menambah jumlah. Anda bisa mengubah jumlah secara manual. Setelah submit, reservasi akan disimpan ke sesi.</p>
          </div>
        </div>
      </div>
    </form>
  </div>

  <script>
    function incQty(id){
      var el = document.getElementById('qty_'+id);
      el.value = parseInt(el.value || 0) + 1;
      updateSummary();
    }
    function updateSummary(){
      var inputs = document.querySelectorAll('input[name^="qty["]');
      var total = 0; var items = 0;
      inputs.forEach(function(i){
        var q = parseInt(i.value) || 0;
        var price = parseInt(i.dataset.price) || 0;
        total += q * price; items += q;
      });
      document.getElementById('summaryItems').innerText = items;
      document.getElementById('summaryTotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
    document.querySelectorAll('input[name^="qty["]').forEach(function(inp){ inp.addEventListener('change', updateSummary); });
    document.getElementById('payMethod').addEventListener('change', function(){ document.getElementById('qrisPreview').style.display = this.value==='qris'?'block':'none'; });
    // init
    updateSummary();
    // Populate hidden fields before submit
    document.getElementById('reserveForm').addEventListener('submit', function(e){
      // fill hidden fields
      document.getElementById('hf_nama').value = document.querySelector('input[name="name"]').value;
      document.getElementById('hf_wa').value = document.querySelector('input[name="phone"]').value;
      document.getElementById('hf_tgl').value = document.querySelector('input[name="date"]').value + ' ' + document.querySelector('input[name="time"]').value;
      document.getElementById('hf_catatan').value = document.querySelector('textarea[name="notes"]').value;

      // build items array
      var items = [];
      var inputs = document.querySelectorAll('input[name^="qty["]');
      inputs.forEach(function(i){
        var q = parseInt(i.value) || 0;
        if (q > 0) {
          var id = i.name.match(/qty\[(\d+)\]/)[1];
          var price = parseInt(i.dataset.price) || 0;
          var name = '';
          var card = i.closest('.menu-card');
          if (card) name = card.querySelector('div').innerText.trim();
          items.push({id: id, name: name, qty: q, price: price});
        }
      });

      document.getElementById('hf_item').value = JSON.stringify(items);
      document.getElementById('hf_total').value = items.reduce(function(acc, it){ return acc + (it.price * it.qty); }, 0);

      // allow submit to continue
    });
  </script>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>