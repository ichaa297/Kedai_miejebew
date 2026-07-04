<?php
// ==========================================
// BAGIAN 1: LOGIKA AMBIL DATA LAMA
// ==========================================
session_start();

// echo "<pre>";
// print_r($_POST);
// print_r($_FILES);
// echo "</pre>";
// exit;
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

$sumberEdit = $namaProdukEdit = $hargaEdit = $gambarEdit = "";
$idEdit = "";

// Cek apakah ada ID dikirim via URL
if (isset($_GET["id"])) {
    $idEdit = (int) $_GET["id"];

    // Ambil data produk berdasarkan ID
    $sqlGetData = "SELECT * FROM kelola_produk WHERE id_produk = $idEdit";
    $queryGetData = mysqli_query($koneksi, $sqlGetData);

    if (mysqli_num_rows($queryGetData) == 1) {
        $row = mysqli_fetch_assoc($queryGetData);

        // Isi variabel dengan data lama dari database
        $namaProdukEdit = $row["nama_produk"];
        $hargaEdit = $row["harga"];
        $gambarEdit = $row["gambar"];
        $stokEdit = $row["stok"];
    } else {
        // Jika ID tidak ditemukan di database, tendang ke halaman data
        header("Location: data-produk.php?status=error&msg=Data tidak ditemukan!");
        exit();
    }
} else {
    header("Location: data-produk.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelola Produk - Kedai Mie Jebew</title>
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
</head>

<body>

<!-- PANGGIL KOMPONEN HEADER -->
<?php require_once 'components/header.php'; ?>

<div class="container-fluid">
    <div class="row">

    <!-- PANGGIL KOMPONEN SIDEBAR -->
    <?php require_once 'components/sidebar.php'; ?>

    <!-- KONTEN UTAMA -->
    <div class="col-md-9 bg-light p-4">
        <h4 class="mb-4">Edit Data produk</h4>

        <a href="data-produk.php" class="btn btn-outline-secondary btn-sm mb-3">&larr; Kembali ke Kelola Produk</a>

        <!-- FORM EDIT DATA -->
        <!-- Action mengarah ke file proses terpisah -->
        <div class="card shadow-sm border-0">
            <div class="card-body">
            <!-- enctype required to allow file replacement -->
            <form action="edit-produk-proses.php" method="POST" enctype="multipart/form-data">
                <!-- Sembunyikan ID di dalam form agar dikirim ke file proses -->
                <input type="hidden" name="id_produk" value="<?php echo $idEdit; ?>">
                <!-- Sembunyikan nama file gambar lama agar bisa dipakai bila tidak ada file baru -->
                <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($gambarEdit); ?>">

                <div class="row">
                    <!-- VALUE DIISI VARIABEL YANG SUDAH BERISI DATA LAMA -->
                <div class="col-md-6 mb-3">
                    <label>Nama Produk</label>
                    <input type="text"
                        class="form-control"
                        name="nama_produk"
                        value="<?php echo $namaProdukEdit; ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Harga</label>
                    <input type="text"
                        class="form-control"
                        name="harga"
                        value="<?php echo $hargaEdit; ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Gambar</label>
                    <input type="file"
                        class="form-control"
                        name="gambar"
                        accept="image/*">
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label>Stok</label>
                <input type="number"
                    class="form-control"
                    name="stok"
                    value="<?php echo isset($stokEdit) ? $stokEdit : 0; ?>">
            </div>

            <div class="col-12 mt-3">
                <button type="submit" name="btnUpdate" class="btn btn-success">
                    UPDATE DATA
                </button>
            </div>
            </form>
        </div>

    </div>
    </div>
</div>

<script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>
</html>
