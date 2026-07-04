<?php
session_start();
// Load menu from central data file
require_once '../config/koneksi.php';

$items = [];
$query = mysqli_query($koneksi, "SELECT * FROM kelola_produk WHERE stok > 0 ORDER BY id_produk DESC");
if ($query) {
  while ($row = mysqli_fetch_assoc($query)) {
    $items[] = $row;
  }
}

// Helper: normalize price to numeric (defined once to avoid redeclare)
if (!function_exists('_normalize_price')) {
    function _normalize_price($raw) {
        if (is_numeric($raw)) return (float)$raw;
        $digits = preg_replace('/[^0-9]/', '', (string)$raw);
        return $digits === '' ? 0.0 : (float)$digits;
    }
}


// Handle add to cart (now with spice level and optional note)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    $id = (int)$_POST['id'];
    $qty = max(1, (int)$_POST['qty']);
    $note = trim($_POST['note'] ?? '');

    $ambilProduk = mysqli_query(
        $koneksi,
        "SELECT * FROM kelola_produk WHERE id_produk='$id'"
    );

    if(mysqli_num_rows($ambilProduk) > 0){

        $item = mysqli_fetch_assoc($ambilProduk);

        if($qty > $item['stok']){

            $_SESSION['error'] =
                "Stok ".$item['nama_produk']." hanya ".$item['stok'];

            header("Location: menu.php");
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $key = $id;

        if (!isset($_SESSION['cart'][$key])) {

            $_SESSION['cart'][$key] = [
                'id' => $item['id_produk'],
                'name' => $item['nama_produk'],
                'price' => $item['harga'],
                'qty' => 0,
                'note' => $note
            ];
        }

        $totalQty = $_SESSION['cart'][$key]['qty'] + $qty;

        if($totalQty > $item['stok']){

            $_SESSION['error'] =
                "Stok ".$item['nama_produk']." hanya ".$item['stok'];

            header("Location: menu.php");
            exit;
        }

        $_SESSION['cart'][$key]['qty'] = $totalQty;

        if ($note != '') {
            $_SESSION['cart'][$key]['note'] = $note;
        }
    }

    header("Location: keranjang.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menu - Kedai Mie Jebew</title>
  <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
  <?php if(isset($_SESSION['error'])): ?>
  <div class="alert alert-danger">
      <?php
      echo $_SESSION['error'];
      unset($_SESSION['error']);
      ?>
  </div>
  <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h3">Daftar Menu</h1>
      <a href="keranjang.php" class="btn btn-light">Lihat Keranjang (<?php echo isset($_SESSION['cart'])?array_sum(array_column($_SESSION['cart'],'qty')):0; ?>)</a>
    </div>

    <div class="row">
      <?php if (empty($items)): ?>
        <div class="col-12">
          <div class="alert alert-secondary">Tidak ada menu tersedia saat ini. Silakan cek kembali nanti atau periksa stok di admin.</div>
        </div>
      <?php else: ?>
        <?php foreach ($items as $item): ?>
          <div class="col-12 col-sm-6 col-md-4 mb-4">
            <div class="card h-200">
              <img
              src="../<?php echo htmlspecialchars($item['gambar']); ?>"
              class="card-img-top"
              alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"
              style="height:250px; object-fit:cover;"
              >
              <div class="card-body d-flex flex-column">
                <h6 class="card-title"><?php echo htmlspecialchars($item['nama_produk']); ?></h6>
                <?php
                  // Normalize price (helper defined once above)
                  $priceVal = _normalize_price($item['harga']);
                ?>
                <p class="card-text">Rp <?php echo number_format($priceVal, 0, ',', '.'); ?></p>
                <p class="text-muted"> Stok: <?php echo $item['stok']; ?></p>
                <form method="POST" class="mt-auto">
                  <input type="hidden" name="id" value="<?php echo $item['id_produk']; ?>">
                  <div class="mb-2">
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Catatan (mis. ekstra sambal)">
                  </div>
                  <div class="input-group mb-2">
                    <input type="number" name="qty" class="form-control" value="1" min="1" max="<?php echo $item['stok']; ?>"> 
                    <button class="btn btn-primary" name="add_to_cart" type="submit">Tambah</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    
    <div class="text-center mt-3">
      <a href="dashboard.php" class="btn btn-secondary">Kembali ke Dashboard</a>
    </div>
  </div>

  <script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>
