<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
require_once '../config/koneksi.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

function subtotal() {
  $total = 0;
  foreach ($_SESSION['cart'] as $c) {
    $price = _normalize_price($c['price'] ?? 0);
    $qty = isset($c['qty']) ? (int)$c['qty'] : 0;
    $total += $price * $qty;
  }
  return $total;
}

$message = '';
$receipt = null;
// price normalizer (tolerate formatted strings)
if (!function_exists('_normalize_price')) {
  function _normalize_price($raw) {
    if (is_numeric($raw)) return (float)$raw;
    $digits = preg_replace('/[^0-9]/', '', (string)$raw);
    return $digits === '' ? 0.0 : (float)$digits;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
   (isset($_POST['pay']) || (isset($_POST['qris_confirm']) && $_POST['qris_confirm'] === '1'))) {

  $method = $_POST['method'] ?? 'Cash';
  $name = trim($_POST['name'] ?? '');
  $id_user = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;
  $lokasi = $_POST['location'] ?? '';

  // basic validation
  if ($name === '') {
    $message = 'Nama harus diisi.';
  } else {
    // CEK STOK DULU
    foreach($_SESSION['cart'] as $item){
      $prodId = isset($item['id']) ? (int)$item['id'] : 0;
      $cek = mysqli_query($koneksi, "SELECT stok FROM kelola_produk WHERE id_produk=" . $prodId);
      $data = $cek ? mysqli_fetch_assoc($cek) : null;
      $stokLeft = isset($data['stok']) ? (int)$data['stok'] : 0;

      if($stokLeft < ($item['qty'] ?? 0)){
        $_SESSION['error'] = "Stok " . ($item['name'] ?? 'Item') . " tidak mencukupi. Sisa " . $stokLeft;
        header("Location: keranjang.php");
        exit;
      }
    }

    // SIMPAN KE TABEL PESANAN
    $totalHarga = subtotal();
    $id_user_esc = (int)$id_user;
    $nameEsc = mysqli_real_escape_string($koneksi, $name);
    $methodEsc = mysqli_real_escape_string($koneksi, $method);
    $lokasiEsc = mysqli_real_escape_string($koneksi, $lokasi);
    // New orders start as 'Diterima' so admin can progress them through the pipeline
    $sqlInsert = "INSERT INTO pesanan (id_user, nama_pemesan, metode_bayar, lokasi, total_harga, status)
      VALUES ($id_user_esc, '$nameEsc', '$methodEsc', '$lokasiEsc', '$totalHarga', 'Diterima')";
    mysqli_query($koneksi, $sqlInsert);

    // AMBIL ID PESANAN BARU
    $id_pesanan = mysqli_insert_id($koneksi);

    // SIMPAN DETAIL PESANAN + KURANGI STOK
    foreach($_SESSION['cart'] as $item){
      $prodId = isset($item['id']) ? (int)$item['id'] : 0;
      $qty = isset($item['qty']) ? (int)$item['qty'] : 0;
      $price = _normalize_price($item['price'] ?? 0);
      $note = isset($item['note']) ? $item['note'] : '';

      $priceEsc = mysqli_real_escape_string($koneksi, (string)$price);
      $noteEsc = mysqli_real_escape_string($koneksi, $note);

      $sqlDet = "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, harga, catatan)
             VALUES ($id_pesanan, $prodId, $qty, '$priceEsc', '$noteEsc')";
      mysqli_query($koneksi, $sqlDet);

      mysqli_query($koneksi, "UPDATE kelola_produk SET stok = stok - $qty WHERE id_produk = $prodId");
    }

    // SIMPAN SESSION
    $_SESSION['id_pesanan'] = $id_pesanan;

    // KOSONGKAN KERANJANG
    $_SESSION['cart'] = [];

    // PINDAH KE STRUK
    header('Location: struk.php');
    exit;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Checkout - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container py-4">
    <h1 class="h1">Checkout</h1>
    <div class="row">
      <div class="col-md-6">
        <h2>Ringkasan Pesanan</h2>
        <?php if (empty($_SESSION['cart'])): ?>
          <h3>Keranjang kosong. <a href="menu.php">Kembali ke menu</a></h3>
        <?php else: ?>
          <ul class="list-group mb-3">
            <?php foreach ($_SESSION['cart'] as $c): ?>
              <?php $priceVal = _normalize_price($c['price'] ?? 0); $qtyVal = isset($c['qty']) ? (int)$c['qty'] : 0; $line = $priceVal * $qtyVal; ?>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <h3> <?php echo htmlspecialchars($c['name']); ?> x <?php echo $qtyVal; ?> </h3>
                <h3> <span>Rp <?php echo number_format($line,0,',','.'); ?></span></h3>
              </li>
            <?php endforeach; ?>
            <li class="list-group-item d-flex justify-content-between"><strong><h3>Total</h3></strong><strong><h3>Rp <?php echo number_format(subtotal(),0,',','.'); ?></h3></strong></li>
          </ul>
        <?php endif; ?>
      </div>
      <div class="col-md-6">
        <h2>Informasi Pembayaran</h2>
        <?php if ($message): ?>
          <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="POST" id="payForm">
          <div class="mb-3">
            <h5><label class="form-h3">Nama</label></h5>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <h5><label class="form-label">Pilih Tempat</label></h5>
            <select name="location" class="form-select" id="locationSelect">
              <option value="makan disini">Makan Di Tempat</option>
              <option value="bawa pulang">Bawa Pulang</option>
            </select>
          </div>
          <div class="mb-3">
            <h5><label class="form-label">Metode Pembayaran</label></h5>
            <select name="method" class="form-select" id="methodSelect">
              <option value="Cash">Tunai</option>
              <option value="QRIS">QRIS</option>
            </select>
            <div class="mt-3" id="qrisBox" style="display:none;">
            </div>
          </div>
          <input type="hidden" name="qris_confirm" id="qris_confirm" value="0">
          <button type="submit" name="pay" id="payButton" class="btn btn-success">Bayar Sekarang</button>
        </form>

        <!-- QRIS confirmation box (shown before final submit) -->
        <div id="qrisConfirmBox" style="display:none;" class="mt-3">
          <div class="card p-3 text-center">
            <p>Silakan scan QRIS berikut. Setelah melakukan pembayaran, tekan tombol "Konfirmasi Pembayaran".</p>
            <img src="../assets/img/image_qris.png" alt="QRIS" class="img-fluid mb-3" style="max-width:260px; margin:0 auto;">
            <div>
              <button id="confirmQris" class="btn btn-primary me-2">Konfirmasi Pembayaran</button>
              <button id="cancelQris" class="btn btn-outline-secondary">Batal</button>
            </div>
          </div>
        </div>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      var sel = document.getElementById('methodSelect');
      var qBox = document.getElementById('qrisBox');
      var qConfirm = document.getElementById('qrisConfirmBox');
      var payForm = document.getElementById('payForm');
      var payButton = document.getElementById('payButton');
      var qrisConfirmInput = document.getElementById('qris_confirm');

      function updateQBox(){
        if (sel.value === 'QRIS') qBox.style.display = 'block'; else qBox.style.display = 'none';
      }
      sel.addEventListener('change', updateQBox);
      updateQBox();

      // intercept submit
      payForm.addEventListener('submit', function(e){
        var method = sel.value;
        if (method === 'QRIS') {
          // if not yet confirmed, show confirmation box instead of submitting
          if (qrisConfirmInput.value !== '1') {
            e.preventDefault();
            qConfirm.style.display = 'block';
            // scroll into view
            qConfirm.scrollIntoView({behavior:'smooth'});
            return false;
          }
        }
        // otherwise allow submit
      });

      // confirm and cancel handlers
      document.getElementById('confirmQris').addEventListener('click', function(){
        qrisConfirmInput.value = '1';
        // hide confirm box and submit form
        qConfirm.style.display = 'none';
        payForm.submit();
      });
      document.getElementById('cancelQris').addEventListener('click', function(){
        qConfirm.style.display = 'none';
      });
    })();
  </script>
</body>
</html>
