<?php
// ==========================================
// FILE Khusus Proses Simpan (POST-CREATE)
// ==========================================

// Panggil koneksi database
require_once '../config/koneksi.php';

// Deklarasikan tipe variabel untuk VS Code (Menghilangkan Warning)
/** @var mysqli $koneksi */

// Cek apakah tombol Simpan benar-benar ditekan (Keamanan)
if (isset($_POST["btnSimpan"])) {

    // Ambil data dari form dan sanitasi dari serangan XSS
    $namaProduk = trim($_POST["nama_produk"] ?? '');
    $harga = trim($_POST["harga"] ?? '');

    // Tangani upload file (jika ada)
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $tmpName = $_FILES['gambar']['tmp_name'];
        $originalName = basename($_FILES['gambar']['name']);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9-_\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
        $newFileName = $safeName . '_' . time() . '.' . $ext;

        if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
            $gambar = 'uploads/' . $newFileName; // relative path to project root
        }
    }

    // Ambil stok jika dikirim
    $stok = isset($_POST['stok']) ? (int) $_POST['stok'] : 0;

    // Validasi sederhana
    if (empty($namaProduk) || empty($harga)) {
        // Jika gagal, kembalikan ke form dengan pesan error via URL (GET)
        header("Location: data-produk.php?status=error&msg=Nama Produk dan Harga wajib diisi!");
        exit();
    } else {
        // Susun perintah SQL INSERT
    // Escape sebelum query sederhana
    $namaEsc = mysqli_real_escape_string($koneksi, $namaProduk);
    $hargaEsc = mysqli_real_escape_string($koneksi, $harga);
    $gambarEsc = mysqli_real_escape_string($koneksi, $gambar);
    $stokEsc = (int) $stok;
    $sqlInsert = "INSERT INTO kelola_produk (nama_produk, harga, gambar, stok) VALUES ('$namaEsc', '$hargaEsc', '$gambarEsc', '$stokEsc')";


    // Eksekusi query
    $querySimpan = mysqli_query($koneksi, $sqlInsert);

        // Cek hasil eksekusi query
        if ($querySimpan) {
            // Jika sukses, kembalikan ke form dengan pesan sukses via URL (GET)
            header("Location: data-produk.php?status=sukses&msg=Data $namaProduk berhasil disimpan!");
            exit();
        } else {
            // Jika gagal query DB
            header("Location: data-produk.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
            exit();
        }
    }
} else {
    // Jika seseorang langsung membuka file ini lewat URL tanpa menekan tombol
    header("Location: data-produk.php");
    exit();
}

