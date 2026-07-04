<?php
// ==========================================
// FILE Khusus Proses Simpan (POST-CREATE)
// ==========================================

// Panggil koneksi database
session_start();
require_once '../config/koneksi.php';
/** @var mysqli $koneksi */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../app/reservasi.php');
    exit;
}

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$wa = trim($_POST['wa'] ?? '');
$tgl = trim($_POST['tgl'] ?? '');
$itemRaw = trim($_POST['item'] ?? ''); // expected JSON from frontend, we'll parse and store relationally
$total = (float) (trim($_POST['total'] ?? '0'));
$catatan = trim($_POST['catatan'] ?? '');
$status = trim($_POST['status'] ?? 'Menunggu');

// Determine id_user: prefer session, otherwise try to lookup by email
$id_user = isset($_SESSION['id_user']) ? (int) $_SESSION['id_user'] : 0;
if ($id_user === 0 && $email !== '') {
    $emailEscTmp = mysqli_real_escape_string($koneksi, $email);
    $qUser = mysqli_query($koneksi, "SELECT id_user FROM pengguna WHERE email = '$emailEscTmp' LIMIT 1");
    if ($qUser && mysqli_num_rows($qUser) > 0) {
        $id_user = (int) mysqli_fetch_assoc($qUser)['id_user'];
    }
}

// Ensure a relational detail table exists for reservation items
$createDetail = "CREATE TABLE IF NOT EXISTS detail_reservasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_reservasi INT NOT NULL,
    id_produk INT DEFAULT NULL,
    qty INT DEFAULT 0,
    harga DECIMAL(12,2) DEFAULT 0,
    catatan TEXT DEFAULT NULL,
    FOREIGN KEY (id_reservasi) REFERENCES proses_reservasi(id_reservasi) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($koneksi, $createDetail);

// Escape the main fields and insert into proses_reservasi (without storing items JSON)
$namaEsc = mysqli_real_escape_string($koneksi, $nama);
$emailEsc = mysqli_real_escape_string($koneksi, $email);
$waEsc = mysqli_real_escape_string($koneksi, $wa);
$tglEsc = mysqli_real_escape_string($koneksi, $tgl);
$totalEsc = mysqli_real_escape_string($koneksi, (string)$total);
$catatanEsc = mysqli_real_escape_string($koneksi, $catatan);
$statusEsc = mysqli_real_escape_string($koneksi, $status);
$idUserEsc = (int) $id_user;
// ensure item field has a safe value (DB may require it)
// ensure item field has a safe value (DB may require it)
$itemEscMain = '[]';
if ($itemRaw !== '') {
    // Check column constraints for proses_reservasi.item
    $itemColQ = mysqli_query($koneksi, "SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, COLUMN_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'proses_reservasi' AND column_name = 'item' LIMIT 1");
    $toStore = $itemRaw;
    if ($itemColQ && mysqli_num_rows($itemColQ) > 0) {
        $colInfo = mysqli_fetch_assoc($itemColQ);
        $dataType = strtolower($colInfo['DATA_TYPE'] ?? '');
        $charMax = isset($colInfo['CHARACTER_MAXIMUM_LENGTH']) ? (int)$colInfo['CHARACTER_MAXIMUM_LENGTH'] : null;

        if ($dataType === 'enum') {
            // not expected, but handle gracefully
            $toStore = '[]';
        } elseif ($charMax !== null && $charMax > 0) {
            // if JSON payload too long, store a short marker '[]' or truncated JSON
            if (strlen($toStore) > $charMax) {
                // prefer a compact marker to avoid invalid truncated JSON
                $toStore = substr($toStore, 0, $charMax);
                // If truncation risks creating invalid JSON, fallback to '[]'
                if (json_decode($toStore) === null) {
                    $toStore = '[]';
                }
            }
        }
    }
    $itemEscMain = mysqli_real_escape_string($koneksi, $toStore !== '' ? $toStore : '[]');
}

// Prepare safe insertion for 'wa' column: if the column is numeric type, insert numeric value (digits) or NULL when too large.
$waInsert = "'" . $waEsc . "'"; // default: quoted string
$colInfoQ = mysqli_query($koneksi, "SELECT DATA_TYPE, COLUMN_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'proses_reservasi' AND column_name = 'wa' LIMIT 1");
if ($colInfoQ && mysqli_num_rows($colInfoQ) > 0) {
    $col = mysqli_fetch_assoc($colInfoQ);
    $dataType = strtolower($col['DATA_TYPE'] ?? '');
    $colType = strtolower($col['COLUMN_TYPE'] ?? '');
    // integer types
    $intTypes = ['tinyint','smallint','mediumint','int','bigint','integer'];
    if (in_array($dataType, $intTypes, true)) {
        // keep only digits
        $digitsOnly = preg_replace('/[^0-9]/', '', $wa);
        if ($digitsOnly === '') {
            // empty digits -> use safe default for integer columns
            $waInsert = '0';
        } else {
            // determine signed/unsigned
            $unsigned = (strpos($colType, 'unsigned') !== false);
            // determine limits per type
            switch ($dataType) {
                case 'tinyint': $min = $unsigned ? 0 : -128; $max = $unsigned ? 255 : 127; break;
                case 'smallint': $min = $unsigned ? 0 : -32768; $max = $unsigned ? 65535 : 32767; break;
                case 'mediumint': $min = $unsigned ? 0 : -8388608; $max = $unsigned ? 16777215 : 8388607; break;
                case 'int': case 'integer': $min = $unsigned ? 0 : -2147483648; $max = $unsigned ? 4294967295 : 2147483647; break;
                case 'bigint': $min = $unsigned ? 0 : -9223372036854775808; $max = $unsigned ? 18446744073709551615 : 9223372036854775807; break;
                default: $min = null; $max = null; break;
            }

            // compare numerically (use float for big numbers)
            $numVal = $digitsOnly;
            // if exceeds max length of PHP int, treat as too large
            if ($max !== null) {
                // cast to float for comparison
                $numFloat = (float)$numVal;
                if ($numFloat < $min || $numFloat > $max) {
                    // out-of-range -> try to alter the column to a string type so we can preserve the phone number
                    $alterSql = "ALTER TABLE proses_reservasi MODIFY wa VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL";
                    $alterOk = @mysqli_query($koneksi, $alterSql);
                    if ($alterOk) {
                        // store as quoted string now that column is text
                        $waInsert = "'" . mysqli_real_escape_string($koneksi, $wa) . "'";
                    } else {
                        // if we cannot alter (permission/etc), fall back to 0 to avoid insert errors
                        $waInsert = '0';
                    }
                } else {
                    $waInsert = (string) (int) $numVal;
                }
            } else {
                // unknown limits: be conservative; try to switch to VARCHAR if the number looks long
                if (strlen($digitsOnly) > 10) {
                    $alterSql = "ALTER TABLE proses_reservasi MODIFY wa VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL";
                    $alterOk = @mysqli_query($koneksi, $alterSql);
                    if ($alterOk) {
                        $waInsert = "'" . mysqli_real_escape_string($koneksi, $wa) . "'";
                    } else {
                        $waInsert = (string) (int) $digitsOnly;
                    }
                } else {
                    $waInsert = (string) (int) $digitsOnly;
                }
            }
        }
    }
}

$sqlInsert = "INSERT INTO proses_reservasi (nama, email, wa, tgl, item, total, catatan, status, id_user)\n              VALUES ('$namaEsc', '$emailEsc', $waInsert, '$tglEsc', '$itemEscMain', '$totalEsc', '$catatanEsc', '$statusEsc', $idUserEsc)";

$querySimpan = mysqli_query($koneksi, $sqlInsert);

if (!$querySimpan) {
    header("Location: ../app/reservasi.php?status=error&msg=" . urlencode(mysqli_error($koneksi)));
    exit();
}

// Ambil id_reservasi yang baru
$id_reservasi = mysqli_insert_id($koneksi);

// Parse items sent from frontend (they are sent as JSON string); we will NOT store JSON in DB
$items = [];
if ($itemRaw !== '') {
    $decoded = json_decode($itemRaw, true);
    if (is_array($decoded)) $items = $decoded;
}

// Insert each item into detail_reservasi (if any)
if (!empty($items)) {
    $stmtValues = [];
    foreach ($items as $it) {
        $pid = isset($it['id']) ? (int) $it['id'] : null;
        $qty = isset($it['qty']) ? (int) $it['qty'] : 0;
        $price = isset($it['price']) ? (float) $it['price'] : 0;
        $note = isset($it['note']) ? $it['note'] : null;

        $pidEsc = $pid === null ? 'NULL' : (int)$pid;
        $qtyEsc = (int)$qty;
        $priceEsc = mysqli_real_escape_string($koneksi, (string)$price);
        $noteEsc = $note !== null ? "'" . mysqli_real_escape_string($koneksi, $note) . "'" : "NULL";

        $stmtValues[] = "($id_reservasi, $pidEsc, $qtyEsc, '$priceEsc', $noteEsc)";
    }

    if (!empty($stmtValues)) {
        $sqlItemsInsert = "INSERT INTO detail_reservasi (id_reservasi, id_produk, qty, harga, catatan) VALUES " . implode(',', $stmtValues);
        mysqli_query($koneksi, $sqlItemsInsert);
    }
}

// Redirect back to frontend reservasi page with success flag
header("Location: ../app/reservasi.php?ok=1");
exit();


