<?php
// MULAI SESSION SECARA GLOBAL
session_start();
// ==========================================
// BAGIAN 1: LOGIKA SERVER-SIDE (PHP & DATABASE)
// ==========================================
// Panggil file koneksi (Clean Code)
require_once '../config/koneksi.php';

$errorPhp = "";


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi</title>
    <!-- Memanggil Bootstrap CSS Lokal -->
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
</head>

<body class="bg-danger">

<div class="container">
    <div class="row justify-content-center mt-5">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body p-4">

            <img src="../assets/img/logo.png" class="img-fluid mx-auto d-block rounded-circle mb-3" alt="Logo ORMAWA" width="100">
            <h3 class="text-center mb-1 fw-bold">Registrasi Akun</h3>
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
        <form action="proses-registrasi.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="nama" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Daftar</button>

            <a href ="login.php" class="btn btn-secondary">Login</a>
        </form>
            

        </div>
        </div>

    </div>
    </div>
</div>

<!-- Memanggil Bootstrap JS Lokal -->
<script src="../assets/lib/js/bootstrap.bundle.min.js"></script>


</body>
</html>