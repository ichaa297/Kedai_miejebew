<?php
// MULAI SESSION SECARA GLOBAL
session_start();
// ==========================================
// BAGIAN 1: LOGIKA SERVER-SIDE (PHP & DATABASE)
// ==========================================
// Panggil file koneksi (Clean Code)
require_once '../config/koneksi.php';

$errorPhp = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $user = $_POST["username"] ?? '';
    $pass = $_POST["password"] ?? '';

    // LOGIKA TERSTRUKTUR: Keamanan dasar (Jangan biarkan kolom kosong)
    if ($user == "" || $pass == "") {
        $errorPhp = "Username dan Password tidak boleh kosong!";
    } else {
        // LOGIKA TERSTRUKTUR: Query ke Database
        // 1. Tulis perintah SQL (Cari baris di tabel user yang usernamenya sama, dan passwordnya sama)
    // Escape values to reduce risk of SQL injection (consider using password hashing and prepared statements)
    $userEsc = mysqli_real_escape_string($koneksi, $user);
    $passEsc = mysqli_real_escape_string($koneksi, $pass);
    $sql = "SELECT * FROM admin WHERE username='$userEsc' AND password='$passEsc'";

        /** @var mysqli $koneksi */ // <--- extension VS Code akan langsung paham dan tanda problem merah akan hilang
        // VSCode akan otomatis memberikan autocomplete (saran kode) saat Anda mengetik $koneksi->

        // 2. Eksekusi Query
        $result = mysqli_query($koneksi, $sql);

        // 3. Cek apakah data ditemukan
        // mysqli_num_rows() menghitung jumlah baris yang dikembalikan database
        if (mysqli_num_rows($result) == 1) {
            // --- TAMBAHKAN KODE INI ---
            // Membuat Session Login
            $_SESSION['is_logged_in'] = true;    // Status: Sudah login
            $_SESSION['username'] = $user;       // Menyimpan nama user yang login

            // Jika hasilnya tepat 1 baris, berarti login benar
            header("Location: dashboard.php");
            exit();
        } else {
            // Jika 0 baris, berarti data tidak cocok
            $errorPhp = "Login Gagal! Username atau Password salah di Database.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Admin</title>
    <!-- Memanggil Bootstrap CSS Lokal -->
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">

            <img src="../assets/img/logo.png" class="img-fluid mx-auto d-block rounded-circle mb-3" alt="Logo ORMAWA" width="100">
            <h3 class="text-center mb-1 fw-bold">Kedai Mie Jebew</h3>
            <p class="text-center text-muted mb-4">Login via Database</p>

            <!-- MENAMPILKAN ERROR DARI PHP -->
            <?php if ($errorPhp != ""): ?>
                <div class="alert alert-danger py-2">
                    <?php echo $errorPhp; ?>
                </div>
            <?php endif; ?>

            <!-- TEMPAT ERROR JS -->
            <div id="errorJs" class="alert alert-warning py-2" style="display: none;"></div>

            <!-- Form -->
            <form id="formLogin" action="" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user) ? htmlspecialchars($user) : ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2 fw-bold">MASUK</button>
            </form>

            <p class="text-center text-muted mt-3 small">Login: admin / admin123</p>
        </div>
        </div>

    </div>
    </div>
</div>

<!-- Memanggil Bootstrap JS Lokal -->
<script src="../assets/lib/js/bootstrap.bundle.min.js"></script>

<!-- BAGIAN 2: LOGIKA CLIENT-SIDE (JAVASCRIPT) -->
<script>
const formLogin = document.getElementById("formLogin");
const errorJsDiv = document.getElementById("errorJs");

formLogin.addEventListener("submit", function(event) {
    errorJsDiv.style.display = "none";

    let inputUser = document.getElementById("username").value;
    let inputPass = document.getElementById("password").value;

    if (inputUser.trim() === "" || inputPass.trim() === "") {
        event.preventDefault(); // Cegah kirim ke PHP
        errorJsDiv.innerHTML = "ERROR: Form tidak boleh kosong! (Ditolak oleh JavaScript)";
        errorJsDiv.style.display = "block";
    }
});
</script>
</body>
</html>