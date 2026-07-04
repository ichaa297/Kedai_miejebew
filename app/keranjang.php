<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

require_once '../config/koneksi.php';
// Handle update quantity / spice / note
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['update'])) {
    foreach ($_POST['qty'] as $key => $q) {
      $k = $key;
      $q = max(0, (int)$q);

      if (!isset($_SESSION['cart'][$k])) {
          continue;
      }

      // Use the cart item data to check stock
      $cartItem = &$_SESSION['cart'][$k];
      $productId = isset($cartItem['id']) ? (int)$cartItem['id'] : 0;

      $cekStok = mysqli_query($koneksi, "SELECT stok FROM kelola_produk WHERE id_produk=" . $productId);
      $dataStok = $cekStok ? mysqli_fetch_assoc($cekStok) : null;
      $stok = isset($dataStok['stok']) ? (int)$dataStok['stok'] : 0;

      if ($q > $stok) {
          $_SESSION['error'] = "Stok " . ($cartItem['name'] ?? 'Item') . " hanya " . $stok;
          header("Location: keranjang.php");
          exit;
      }

      if ($q === 0) {
          unset($_SESSION['cart'][$k]);
          continue;
      }

      $_SESSION['cart'][$k]['qty'] = $q;
      if (isset($_POST['note'][$k])) {
        $_SESSION['cart'][$k]['note'] = trim($_POST['note'][$k]);
      }
    }
  }
  // handle clear
  if (isset($_POST['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: keranjang.php');
    exit;
  }
  // handle checkout button (redirect to checkout)
  if (isset($_POST['chekout'])) {
    header('Location: chekout.php');
    exit;
  }
}

// Helper to normalize price values (tolerate formatted strings)
if (!function_exists('_normalize_price')) {
  function _normalize_price($raw) {
    if (is_numeric($raw)) return (float)$raw;
    $digits = preg_replace('/[^0-9]/', '', (string)$raw);
    return $digits === '' ? 0.0 : (float)$digits;
  }
}

function subtotal() {
    $total = 0;
    foreach ($_SESSION['cart'] as $c) {
        $price = _normalize_price($c['price'] ?? 0);
        $qty = isset($c['qty']) ? (int)$c['qty'] : 0;
        $total += $price * $qty;
    }
    return $total;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Keranjang - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <div class="container py-4">
    <h1 class="h3 mb-3">Keranjang Anda</h1>
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
    <?php endif; ?>

    <form method="POST">
    <div class="table-responsive">
      <table class="table table-dark table-striped">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Harga</th>
            <th>Kepedasan / Catatan</th>
            <th>Qty</th>
            <th>Stok</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($_SESSION['cart'])): ?>
            <tr><td colspan="6" class="text-center">Keranjang kosong. <a href="menu.php">Lihat menu</a></td></tr>
          <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $key => $c): ?>
              <?php
              $cekStok = mysqli_query(
                  $koneksi,
                  "SELECT stok FROM kelola_produk WHERE id_produk=".$c['id']
              );

              $dataStok = mysqli_fetch_assoc($cekStok);
              $stok = $dataStok['stok'];
              ?>
              <tr>
                <td><?php echo htmlspecialchars($c['name']); ?></td>
                <?php
                  $priceVal = _normalize_price($c['price'] ?? 0);
                  $qtyVal = isset($c['qty']) ? (int)$c['qty'] : 0;
                  $lineSubtotal = $priceVal * $qtyVal;
                ?>
                <td>Rp <?php echo number_format($priceVal,0,',','.'); ?></td>
                <td style="min-width:180px;">
                  <input type="text" name="note[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($c['note'] ?? ''); ?>" class="form-control form-control-sm" placeholder="Catatan">
                </td>
                <td style="width:120px;">
                <input type="number" name="qty[<?php echo $key; ?>]" value="<?php echo $c['qty']; ?>" min="0" class="form-control"></td>
            <td><?php echo $stok; ?></td>
            <td>Rp <?php echo number_format($lineSubtotal,0,',','.'); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>
        <button type="submit" name="update" class="btn btn-primary">Update Keranjang</button>
        <button type="submit" name="clear" class="btn btn-primary">Bersihkan</button>
        <a href="menu.php" class="btn btn-primary">Tambahkan lagi</a>
        <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
      </div>
      <div class="text-end">
        <h3 class="mb-1">Total: <strong>Rp <?php echo number_format(subtotal(),0,',','.'); ?></strong></h3>
        <button type="submit" name="chekout" class="btn btn-success">Checkout</button>
      </div>
    </div>
    </form>
  </div>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>
