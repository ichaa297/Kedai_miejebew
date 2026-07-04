<?php
session_start(); 

session_unset();
// Hancurkan semua data session (Memotong gelang)
session_destroy();

// Alihkan kembali ke halaman login
header("Location: login.php");
exit();

