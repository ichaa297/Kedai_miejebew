<?php
session_start();

if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

$alertHtml = "";
if (isset($_GET["status"]) && isset($_GET["msg"])) {
    $status = $_GET["status"];
    $msg = htmlspecialchars($_GET["msg"]);
    if ($status == "sukses") {
        $alertHtml = "<div class='alert alert-success alert-dismissible fade show' role='alert'>$msg<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    } elseif ($status == "error") {
        $alertHtml = "<div class='alert alert-danger alert-dismissible fade show' role='alert'>$msg<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Kedai Mie Jebew</title>
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
</head>
<body>
<?php require_once 'components/header.php'; ?>

<div class="container-fluid">
    <div class="row">

        <?php require_once 'components/sidebar.php'; ?>

        <div class="col-md-9 bg-light p-4">
            <h4 class="mb-4">Input Kelola Produk</h4>
            <?php echo $alertHtml; ?>

            <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
            <form action="data-produk-proses.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Produk</label>
                    <input type="text" class="form-control" name="nama_produk" placeholder="Masukkan Nama Produk" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Harga</label>
                    <input type="text" class="form-control" name="harga" placeholder="Contoh: 1000">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Gambar</label>
                    <input type="file" class="form-control" name="gambar" accept="image/*">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Stok</label>
                    <input type="number" class="form-control" name="stok" placeholder="Contoh: 10">
                </div>
            </div>
            <button type="submit" name="btnSimpan" class="btn btn-primary">SIMPAN DATA</button>
            </form>
            </div>
            </div>

            <h5 class="mt-4 mb-3">Daftar Produk Terdaftar</h5>
            <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Gambar</th>
                        <th>Stok</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $sqlRead = "SELECT * FROM kelola_produk ORDER BY id_produk DESC";
                $queryRead = mysqli_query($koneksi, $sqlRead);
                $no = 1;
                if ($queryRead && mysqli_num_rows($queryRead) > 0) {
                    while ($data = mysqli_fetch_assoc($queryRead)) {
                        echo "<tr>";
                        echo "<td class='text-center'>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($data['nama_produk']) . "</td>";
                        echo "<td>" . htmlspecialchars($data['harga']) . "</td>";
                        $gambarTampil = !empty($data['gambar']) ? "<img src='../".htmlspecialchars($data['gambar'])."' width='80' class='img-thumbnail'/>" : '-';
                        echo "<td>" . $gambarTampil . "</td>";
                        echo "<td class='text-center'>" . (int)($data['stok'] ?? 0) . "</td>";
                        echo "<td class='text-center'>
                                <a href='edit-produk.php?id=" . $data['id_produk'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                <a href='hapus-produk-proses.php?id=" . $data['id_produk'] . "' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus data ini?')\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center text-muted py-3'>Belum ada data produk.</td></tr>";
                }
                ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

<script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>
