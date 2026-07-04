<?php
session_start();

// show and clear any login error from previous attempt
$error = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
// if already logged in, redirect to menu unless ?force=1 is present
if (isset($_SESSION['email']) && !isset($_GET['force'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kedai Mie Jebew</title>
    <link rel="stylesheet" href="../assets/lib/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-danger">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <h4 class="card-title
                        text-center mb-4 fw-bold">Welcome Kedai Mie Jebew</h4>
                    <?php if ($error): ?>
                      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form action="proses-login.php" method="POST" id="loginForm">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mt-2">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>