<?php
// ==========================================
// FILE Khusus Proses Update (UPDATE)
// ==========================================

require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

// Cek apakah tombol Update ditekan
if (isset($_POST["btnUpdate"])) {

    // Ambil ID yang tersembunyi di form
    $idUpdate = (int) $_POST["id_produk"];

    // Ambil data baru dari form
    $namaProdukBaru = htmlspecialchars($_POST["nama_produk"]);
    $hargaBaru = htmlspecialchars($_POST["harga"]);
    // Jika ada file baru, proses upload, jika tidak gunakan gambar lama
    $gambarBaru = '';
    $gambarLama = $_POST['gambar_lama'] ?? '';
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
            $gambarBaru = 'uploads/' . $newFileName;
            // Optionally remove old file from disk
            if (!empty($gambarLama) && file_exists(__DIR__ . '/../' . $gambarLama)) {
                @unlink(__DIR__ . '/../' . $gambarLama);
            }
        } else {
            $gambarBaru = $gambarLama; // fallback
        }
    } else {
        $gambarBaru = $gambarLama;
    }

    // Ambil stok baru jika ada
    $stokBaru = isset($_POST['stok']) ? (int) $_POST['stok'] : null;

    // Validasi
    if (empty($namaProdukBaru) || empty($hargaBaru)) {
        header("Location: edit-produk.php?id=$idUpdate&status=error&msg=Nama Produk dan Harga wajib diisi!");
        exit();
    } else {
        // Susun perintah SQL UPDATE (WAJIB pakai WHERE id)
    // Escape values
    $namaEsc = mysqli_real_escape_string($koneksi, $namaProdukBaru);
    $hargaEsc = mysqli_real_escape_string($koneksi, $hargaBaru);
    $gambarEsc = mysqli_real_escape_string($koneksi, $gambarBaru);
    // Jika stok disediakan, gunakan; jika tidak, biarkan nilai stok tidak berubah
    if ($stokBaru !== null) {
        $stokEsc = (int) $stokBaru;
        $sqlUpdate = "UPDATE kelola_produk SET 
              nama_produk = '$namaEsc', 
              harga = '$hargaEsc', 
              gambar = '$gambarEsc', 
              stok = '$stokEsc'
              WHERE id_produk = $idUpdate";
    } else {
        $sqlUpdate = "UPDATE kelola_produk SET 
              nama_produk = '$namaEsc', 
              harga = '$hargaEsc', 
              gambar = '$gambarEsc'
              WHERE id_produk = $idUpdate";
    }

        // Eksekusi query
        $queryUpdate = mysqli_query($koneksi, $sqlUpdate);

        if ($queryUpdate) {
            // Jika sukses, kembalikan ke halaman tabel utama
    header("Location: data-produk.php?status=sukses&msg=Data berhasil diperbarui!");
            exit();
        } else {
            header("Location: data-produk.php?id=$idUpdate&status=error&msg=" . urlencode(mysqli_error($koneksi)));
            exit();
        }
    }
} else {
    // Jika dibuka langsung tanpa menekan tombol
    header("Location: data-produk.php");
    exit();
}
