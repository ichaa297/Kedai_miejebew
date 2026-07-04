<?php
// Compatibility alias: some links use underscore instead of hyphen.
// This file simply includes the canonical page.
$target = __DIR__ . '/pesanan-diproses.php';
if (file_exists($target)) {
    require $target;
    exit;
} else {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    echo "404 Not Found: pesanan-diproses.php is missing.";
    exit;
}
