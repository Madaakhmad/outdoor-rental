<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: peralatan.php");
    exit;
}

$id = (int) $_GET['id'];

// Ambil data peralatan
$stmt = mysqli_prepare($conn, "SELECT * FROM peralatan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$produk = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$produk) {
    header("Location: peralatan.php?error=not_found");
    exit;
}

// Cek apakah ada riwayat booking
$stmtCek = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM booking WHERE id_peralatan = ?");
mysqli_stmt_bind_param($stmtCek, "i", $id);
mysqli_stmt_execute($stmtCek);
$dataBooking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

if ($dataBooking['total'] > 0) {
    header("Location: peralatan.php?error=has_booking");
    exit;
}

// Eksekusi Hapus
if (hapusPeralatan($conn, $id)) {
    header("Location: peralatan.php?success=delete");
    exit;
} else {
    header("Location: peralatan.php?error=delete_failed");
    exit;
}
