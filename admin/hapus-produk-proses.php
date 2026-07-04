<?php
// ==========================================
// FILE Khusus Proses Hapus (DELETE)
// ==========================================

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

// Cek apakah ada ID yang dikirim via URL (Metode GET)
if (isset($_GET["id_produk"]) || isset($_GET["id"])) {
    $idHapus = (int) (isset($_GET["id_produk"]) ? $_GET["id_produk"] : $_GET["id"]);

    // Perintah SQL DELETE (WAJIB pakai WHERE id)
    $sqlHapus = "DELETE FROM kelola_produk WHERE id_produk = $idHapus";

    // Eksekusi query
    $queryHapus = mysqli_query($koneksi, $sqlHapus);

    // Cek berhasil atau gagal, lalu redirect
    if ($queryHapus) {
        header("Location: data-produk.php?status=sukses&msg=Data berhasil dihapus!");
        exit();
    } else {
        header("Location: data-produk.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} else {
    // Jika dibuka langsung tanpa membawa ID
    header("Location: data-produk.php");
    exit();
}

