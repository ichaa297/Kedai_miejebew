<?php
// ==========================================
// FILE Khusus Proses Hapus (DELETE)
// ==========================================

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

// Cek apakah ada ID yang dikirim via URL (Metode GET)
if (isset($_GET["id"])) {
    $idHapus = (int) $_GET["id_reservasi"]; // (int) untuk keamanan, memastikan ID hanya angka

    // Perintah SQL DELETE (WAJIB pakai WHERE id)
    $sqlHapus = "DELETE FROM proses_reservasi WHERE id_reservasi = $idHapus";

    // Eksekusi query
    $queryHapus = mysqli_query($koneksi, $sqlHapus);

    // Cek berhasil atau gagal, lalu redirect
    if ($queryHapus) {
        header("Location: reservasi.php?status=sukses&msg=Data berhasil dihapus!");
        exit();
    } else {
        header("Location: reservasi.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} else {
    // Jika dibuka langsung tanpa membawa ID
    header("Location: reservasi.php");
    exit();
}

