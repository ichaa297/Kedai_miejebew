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

if(isset($_GET['action']) && isset($_GET['id_pesanan'])){

    $id = $_GET['id_pesanan'];

    if($_GET['action'] == 'advance'){

        mysqli_query($koneksi,"
        UPDATE pesanan
        SET status =
        CASE
            WHEN status='Diterima' THEN 'Dimasak'
            WHEN status='Dimasak' THEN 'Ditunggu'
            WHEN status='Ditunggu' THEN 'Selesai'
            ELSE status
        END
        WHERE id_pesanan='$id'
        ");

    }

    if($_GET['action'] == 'complete'){

        mysqli_query($koneksi,"
        UPDATE pesanan
        SET status='Selesai'
        WHERE id_pesanan='$id'
        ");

    }

    header("Location: proses_order.php");
    exit;
}
// Deklarasikan tipe variabel untuk VS Code
/** @var mysqli $koneksi */

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
    <title>Proses Order - Kedai Mie Jebew</title>
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
        <h4 class="mb-4">Proses Order</h4>

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
                    <th>Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th width="15%">Aksi</th>
                    
                </tr>
            </thead>
            <tbody>
            <?php

            $sqlRead = "SELECT * FROM pesanan ORDER BY id_pesanan DESC";
            $queryRead = mysqli_query($koneksi, $sqlRead);
            $no = 1;

            while($data = mysqli_fetch_assoc($queryRead)){

                $id_pesanan = $data['id_pesanan'];

                $qDetail = mysqli_query($koneksi,"
                SELECT kp.nama_produk
                FROM detail_pesanan dp
                JOIN kelola_produk kp
                ON dp.id_produk = kp.id_produk
                WHERE dp.id_pesanan = '$id_pesanan'
                ");

                $item = [];

                while($d = mysqli_fetch_assoc($qDetail)){
                    $item[] = $d['nama_produk'];
                }

                echo "<tr>";
                echo "<td>".$no++."</td>";
                echo "<td>".$data['nama_pemesan']."</td>";
                echo "<td>".implode(', ', $item)."</td>";
                echo "<td>Rp ".number_format($data['total_harga'],0,',','.')."</td>";
                echo "<td>".$data['status']."</td>";
                echo "<td>
                    <a href='proses_order.php?action=advance&id_pesanan=".$data['id_pesanan']."'
                    class='btn btn-primary btn-sm'>
                    Meningkat
                    </a>

                    <a href='proses_order.php?action=complete&id_pesanan=".$data['id_pesanan']."'
                    class='btn btn-success btn-sm'>
                    Selesai
                    </a>
                </td>";
                echo "</tr>";
            }
            if(mysqli_num_rows($queryRead)==0){
                echo "<tr>
                        <td colspan='6' class='text-center'>
                            Belum ada pesanan
                        </td>
                    </tr>";
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

