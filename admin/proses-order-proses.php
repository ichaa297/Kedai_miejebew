<?php
// ==========================================
// FILE Khusus Proses Simpan (POST-CREATE)
// ==========================================

// Panggil koneksi database
require_once '../config/koneksi.php';

// Deklarasikan tipe variabel untuk VS Code (Menghilangkan Warning)
/** @var mysqli $koneksi */

// Pastikan dipanggil lewat POST (misal ada tombol bernama btnSimpan atau submit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $item = trim($_POST['item'] ?? '');
    $total = trim($_POST['total'] ?? '0');
    $progres = trim($_POST['progres'] ?? 'Menunggu');

    // Escape sebelum query sederhana
    $namaEsc = mysqli_real_escape_string($koneksi, $nama);
    $itemEsc = mysqli_real_escape_string($koneksi, $item);
    $totalEsc = mysqli_real_escape_string($koneksi, $total);
    $progresEsc = mysqli_real_escape_string($koneksi, $progres);

    $sqlInsert = "INSERT INTO proses_order (nama, item, total, progres) VALUES ('$namaEsc', '$itemEsc', '$totalEsc', '$progresEsc')";

    // Eksekusi query
    $querySimpan = mysqli_query($koneksi, $sqlInsert);

    // Cek hasil eksekusi query
    if ($querySimpan) {
        // Jika sukses, kembalikan ke form dengan pesan sukses via URL (GET)
        header("Location: proses_order.php?status=sukses&msg=Data berhasil disimpan!");
        exit();
    } else {
        // Jika gagal query DB
        header("Location: proses_order.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
        exit();
    }
} else {
    // Jika seseorang langsung membuka file ini lewat URL tanpa menekan tombol
    header("Location: proses_order.php");
    exit();
}

