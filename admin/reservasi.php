<?php
session_start();

// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// ==========================================
// BAGIAN 1: MENANGKAP PESAN DARI URL (GET)
// ==========================================

// Panggil koneksi (Karena butuh untuk SELECT data tabel)
require_once '../config/koneksi.php';

// Deklarasikan tipe variabel untuk VS Code
/** @var mysqli $koneksi */

// Handle conversion action: ?action=complete&id_order=...
if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id_order'])) {
    $idRes = (int) $_GET['id_order'];

    // Fetch reservation
    $qRes = mysqli_query($koneksi, "SELECT * FROM proses_reservasi WHERE id_reservasi = $idRes LIMIT 1");
    if ($qRes && mysqli_num_rows($qRes) > 0) {
        $res = mysqli_fetch_assoc($qRes);

        // Stage 1: mark as Dikonfirmasi (admin accepted)
        mysqli_query($koneksi, "UPDATE proses_reservasi SET status = 'Dikonfirmasi' WHERE id_reservasi = $idRes");

        // Create a pesanan row
        $id_user = isset($res['id_user']) ? (int) $res['id_user'] : 0;
        $nama = mysqli_real_escape_string($koneksi, $res['nama'] ?? ($res['name'] ?? 'Reservasi'));
        $total = isset($res['total']) ? (float) $res['total'] : 0.0;
        $totalEsc = mysqli_real_escape_string($koneksi, (string)$total);
        // default method and lokasi for converted reservation
        $methodRaw = 'Reservasi';
        $lokasiRaw = $res['place'] ?? 'Reservasi';

        // Determine safe value for lokasi based on pesanan.lokasi column
        $lokasiEsc = mysqli_real_escape_string($koneksi, (string)$lokasiRaw);
        $locColQ = mysqli_query($koneksi, "SELECT DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'lokasi' LIMIT 1");
        if ($locColQ && mysqli_num_rows($locColQ) > 0) {
            $locInfo = mysqli_fetch_assoc($locColQ);
            $locType = strtolower($locInfo['DATA_TYPE'] ?? '');
            $locColType = $locInfo['COLUMN_TYPE'] ?? '';
            $locMax = isset($locInfo['CHARACTER_MAXIMUM_LENGTH']) ? (int)$locInfo['CHARACTER_MAXIMUM_LENGTH'] : null;

            if ($locType === 'enum') {
                if (preg_match_all("/'([^']*)'/", $locColType, $m2)) {
                    $allowedLoc = $m2[1];
                    if (!in_array($lokasiRaw, $allowedLoc, true)) {
                        $lokasiRaw = $allowedLoc[0] ?? $lokasiRaw;
                    }
                    $lokasiEsc = mysqli_real_escape_string($koneksi, $lokasiRaw);
                }
            } elseif ($locMax !== null && $locMax > 0) {
                if (strlen($lokasiRaw) > $locMax) {
                    $lokasiRaw = substr($lokasiRaw, 0, $locMax);
                }
                $lokasiEsc = mysqli_real_escape_string($koneksi, $lokasiRaw);
            }
        }

        // Determine safe value for metode_bayar based on pesanan.metode_bayar column
        $methodEsc = mysqli_real_escape_string($koneksi, $methodRaw);
        $colQ = mysqli_query($koneksi, "SELECT DATA_TYPE, COLUMN_TYPE, CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pesanan' AND column_name = 'metode_bayar' LIMIT 1");
        if ($colQ && mysqli_num_rows($colQ) > 0) {
            $colInfo = mysqli_fetch_assoc($colQ);
            $dataType = strtolower($colInfo['DATA_TYPE'] ?? '');
            $colType = $colInfo['COLUMN_TYPE'] ?? '';
            $charMax = isset($colInfo['CHARACTER_MAXIMUM_LENGTH']) ? (int)$colInfo['CHARACTER_MAXIMUM_LENGTH'] : null;

            if ($dataType === 'enum') {
                // parse enum values: format is enum('A','B',...)
                if (preg_match_all("/'([^']*)'/", $colType, $m)) {
                    $allowed = $m[1];
                    if (!in_array($methodRaw, $allowed, true)) {
                        // fall back to first allowed enum
                        $methodRaw = $allowed[0] ?? $methodRaw;
                    }
                    $methodEsc = mysqli_real_escape_string($koneksi, $methodRaw);
                }
            } elseif ($charMax !== null && $charMax > 0) {
                // truncate to column length
                if (strlen($methodRaw) > $charMax) {
                    $methodRaw = substr($methodRaw, 0, $charMax);
                }
                $methodEsc = mysqli_real_escape_string($koneksi, $methodRaw);
            }
        }

    // Start the converted pesanan as 'Diterima' so it follows the same processing pipeline as normal orders
    $sqlIns = "INSERT INTO pesanan (id_user, nama_pemesan, metode_bayar, lokasi, total_harga, status) VALUES ($id_user, '$nama', '$methodEsc', '$lokasiEsc', '$totalEsc', 'Diterima')";
        mysqli_query($koneksi, $sqlIns);
        $id_pesanan = mysqli_insert_id($koneksi);

        // Copy items from detail_reservasi to detail_pesanan, and reduce stock
        $qItems = mysqli_query($koneksi, "SELECT * FROM detail_reservasi WHERE id_reservasi = $idRes");
        if ($qItems) {
            while ($it = mysqli_fetch_assoc($qItems)) {
                $prod = isset($it['id_produk']) ? (int) $it['id_produk'] : 0;
                $qty = isset($it['qty']) ? (int) $it['qty'] : 0;
                $harga = isset($it['harga']) ? mysqli_real_escape_string($koneksi, (string)$it['harga']) : '0';
                $cat = isset($it['catatan']) ? mysqli_real_escape_string($koneksi, $it['catatan']) : '';

                mysqli_query($koneksi, "INSERT INTO detail_pesanan (id_pesanan, id_produk, qty, harga, catatan) VALUES ($id_pesanan, $prod, $qty, '$harga', '$cat')");
                // reduce stock if product exists
                if ($prod > 0 && $qty > 0) {
                    mysqli_query($koneksi, "UPDATE kelola_produk SET stok = GREATEST(stok - $qty, 0) WHERE id_produk = $prod");
                }
            }
        }

        // Stage 2: mark original reservation as converted/confirmed
        mysqli_query($koneksi, "UPDATE proses_reservasi SET status = 'Dikonfirmasi' WHERE id_reservasi = $idRes");

        header("Location: reservasi.php?status=sukses&msg=" . urlencode('Reservasi dikonversi menjadi pesanan.'));
        exit;
    } else {
        header("Location: reservasi.php?status=error&msg=" . urlencode('Reservasi tidak ditemukan.'));
        exit;
    }
}

$alertHtml = "";

// Cek apakah ada parameter status di URL
if (isset($_GET["status"]) && isset($_GET["msg"])) {
    $status = $_GET["status"];
    $msg = htmlspecialchars($_GET["msg"]); // Sanitasi pesan dari URL

    if ($status == "sukses") {
        $alertHtml = 
       "<div class='alert alert-success alert-dismissible fade show' role='alert'>
           $msg
           <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
       </div>";
    } elseif ($status == "error") {
        $alertHtml = 
        "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
             $msg
             <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
         </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservasi - Kedai Mie Jebew</title>
    <!-- Memanggil Bootstrap CSS Lokal -->
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
</head>

<body>

<!-- PANGGIL KOMPONEN HEADER (Session & Security Guard ada di sini) -->
<?php require_once 'components/header.php'; ?>

<div class="container-fluid">
    <div class="row">

    <!-- PANGGIL KOMPONEN SIDEBAR -->
    <?php require_once 'components/sidebar.php'; ?>

    <!-- ========================================== -->
    <!-- KONTEN UTAMA: FORM INPUT & TABEL           -->
    <!-- ========================================== -->
    <div class="col-md-9 bg-light p-4">
        <h4 class="mb-4">Reservasi</h4>

        <!-- TAMPILKAN NOTIFIKASI JIKA ADA -->
        <?php echo $alertHtml; ?>

        <!-- FORM INPUT DATA (POST) -->
        <!-- Perhatikan action-nya sekarang mengarah ke file proses -->

        <!-- ========================================== -->
        <!-- LOGIKA READ (TAMPILKAN DATA KE TABEL)      -->
        <!-- ========================================== -->

        <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover bg-white shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th width="5%">No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Tanggal</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Catatan</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Perintah SQL SELECT
                $sqlRead = "SELECT * FROM proses_reservasi ORDER BY id_reservasi DESC";
                $queryRead = mysqli_query($koneksi, $sqlRead);
                $no = 1;

                // Looping menampilkan data
                while ($data = mysqli_fetch_assoc($queryRead)) {
                    echo "<tr>";
                    echo "<td class='text-center'>" . $no++ . "</td>";
                        // basic columns
                        echo "<td>" . htmlspecialchars($data['nama'] ?? $data['nama'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($data['email'] ?? '') . "</td>";

                        // WA: hide placeholder 0 or empty; otherwise show stored value
                        $waRaw = $data['wa'] ?? null;
                        $waDisplay = '';
                        if ($waRaw !== null) {
                            $waStr = trim((string)$waRaw);
                            if ($waStr !== '' && $waStr !== '0') {
                                $waDisplay = htmlspecialchars($waStr);
                            }
                        }
                        echo "<td>" . $waDisplay . "</td>";

                        echo "<td>" . htmlspecialchars($data['tgl'] ?? '') . "</td>";

                        // Prefer to display relational items from detail_reservasi (readable), fallback to stored JSON in item column
                        $itemCell = '';
                        $resIdForItems = (int) ($data['id_reservasi'] ?? 0);
                        if ($resIdForItems > 0) {
                            $qIt = mysqli_query($koneksi, "SELECT dr.qty, dr.harga, kp.nama_produk FROM detail_reservasi dr LEFT JOIN kelola_produk kp ON dr.id_produk = kp.id_produk WHERE dr.id_reservasi = $resIdForItems");
                            $names = [];
                            if ($qIt) {
                                while ($it = mysqli_fetch_assoc($qIt)) {
                                    $nm = $it['nama_produk'] ?? 'Item';
                                    $qty = isset($it['qty']) ? (int)$it['qty'] : 0;
                                    $names[] = htmlspecialchars($nm) . ($qty > 0 ? ' x' . $qty : '');
                                }
                            }
                            if (!empty($names)) {
                                $itemCell = implode(', ', $names);
                            }
                        }
                        if ($itemCell === '') {
                            // fallback: show raw item column (may be JSON or '[]')
                            $itemCell = htmlspecialchars($data['item'] ?? '[]');
                        }
                        echo "<td>" . $itemCell . "</td>";

                        // format total
                        $totalVal = $data['total'] ?? ($data['total_harga'] ?? 0);
                        echo "<td>" . htmlspecialchars((string)$totalVal) . "</td>";

                        echo "<td>" . htmlspecialchars($data['catatan'] ?? '') . "</td>";
                        echo "<td>" . htmlspecialchars($data['status'] ?? '') . "</td>";

                    // Tombol Aksi (Akan diisi di Pertemuan 12)
                    echo "<td class='text-center'>
                    <td>
                    <a href='reservasi.php?action=complete&id_order=" . $data['id_reservasi'] . "' class='btn btn-sm btn-success'>Konversi ke Pesanan</a>
                    <a href='hapus-reservasi.php?id=" . $data['id_reservasi'] . "' class='btn btn-danger btn-sm'
                    onclick=\"return confirm('Yakin ingin menghapus data ini?')\">Hapus</a>
                </td>";
                }

                // Jika data kosong
                if (mysqli_num_rows($queryRead) == 0) {
                    echo "<tr><td colspan='6' class='text-center text-muted py-3'>Belum ada data produk.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
    </div>
    <!-- AKHIR KONTEN UTAMA -->

    </div>
</div>

<!-- PANGGIL JS BOOTSTRAP SECARA LOKAL -->
<script src="../assets/lib/js/bootstrap.bundle.min.js"></script>
</body>

</html>

