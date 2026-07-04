<?php
// ==========================================
// FILE Khusus Proses Hapus (DELETE)
// ==========================================

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

// Cek apakah ada ID yang dikirim via URL (Metode GET)
if (isset($_GET["id_order"]) || isset($_GET["id"])) {
    // Terima baik ?id_order= maupun ?id=
    $idHapus = (int) (isset($_GET["id_order"]) ? $_GET["id_order"] : $_GET["id"]);

    // Perintah SQL DELETE (WAJIB pakai WHERE id)
    $sqlHapus = "DELETE FROM proses_order WHERE id_order = $idHapus";

    // Eksekusi query
    $queryHapus = mysqli_query($koneksi, $sqlHapus);

    // Cek berhasil atau gagal, lalu redirect
    if ($queryHapus) {
        header("Location: proses_order.php?status=sukses&msg=Data berhasil dihapus!");
        exit();
    } else {
        header("Location: proses_order.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} else {
    // Jika dibuka langsung tanpa membawa ID
    header("Location: proses_order.php");
    exit();
}

