<?php

// Matikan deprecation dan error display agar tidak mengotori output file Excel
error_reporting(0);
ini_set('display_errors', '0');

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// Filter Tanggal & Status sesuai dengan laporan.php
$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$statusFilter = $_GET['status'] ?? '';
$bayarFilter = $_GET['status_bayar'] ?? '';

$sql = "SELECT 
            booking.*, 
            peralatan.nama AS nama_peralatan,
            peralatan.kategori AS kategori_peralatan,
            peralatan.harga AS harga_satuan,
            users.username
        FROM booking
        JOIN peralatan ON booking.id_peralatan = peralatan.id
        JOIN users ON booking.id_user = users.id
        WHERE DATE(booking.tanggal_pinjam) BETWEEN ? AND ?";

$params = [$tglAwal, $tglAkhir];
$types = "ss";

if (!empty($statusFilter)) {
    $sql .= " AND booking.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($bayarFilter)) {
    $sql .= " AND booking.status_pembayaran = ?";
    $params[] = $bayarFilter;
    $types .= "s";
}

$sql .= " ORDER BY booking.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Set Header untuk Download File Excel (.xls)
$filename = "Laporan_Keuangan_Mada_Adventure_" . str_replace('-', '', $tglAwal) . "_" . str_replace('-', '', $tglAkhir) . ".xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output tabel berformat XML/HTML yang didukung penuh oleh Microsoft Excel
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        th {
            background-color: #1f2937;
            color: #ffffff;
            font-weight: bold;
            padding: 10px;
            border: 1px solid #000000;
            text-align: center;
        }
        td {
            padding: 8px;
            border: 1px solid #cccccc;
            vertical-align: middle;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .title-header {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="title-header">LAPORAN KEUANGAN & TRANSAKSI MADA ADVENTURE (OUTDOOR RENTAL)</div>
    <div>Periode: <?= date('d/m/Y', strtotime($tglAwal)); ?> s/d <?= date('d/m/Y', strtotime($tglAkhir)); ?></div>
    <div>Dicetak pada: <?= date('d/m/Y H:i:s'); ?></div>
    <br>

    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>ID Transaksi</th>
                <th>Tanggal Pemesanan</th>
                <th>Nama Penyewa</th>
                <th>Nama Pengguna</th>
                <th>Nomor HP / WhatsApp</th>
                <th>Peralatan Disewa</th>
                <th>Kategori</th>
                <th>Jumlah Unit</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Durasi (Hari)</th>
                <th>Biaya Sewa (Rp)</th>
                <th>Denda (Rp)</th>
                <th>Total Biaya (Rp)</th>
                <th>Status Pembayaran</th>
                <th>Status Sewa</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $totalUnitDisewa = 0;
            $totalOmzetSewa = 0;
            $totalDenda = 0;
            $grandTotalSemua = 0;

            if (mysqli_num_rows($result) === 0) {
            ?>
                <tr>
                    <td colspan="17" class="text-center" style="padding: 20px;">Tidak ada data transaksi pada periode ini.</td>
                </tr>
            <?php
            } else {
                while ($row = mysqli_fetch_assoc($result)) {
                    $grand = (int) $row['total_harga'] + (int) $row['denda'];
                    $totalUnitDisewa += (int) $row['jumlah'];
                    $totalOmzetSewa += (int) $row['total_harga'];
                    $totalDenda += (int) $row['denda'];
                    $grandTotalSemua += $grand;
            ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center">#<?= $row['id']; ?></td>
                    <td class="text-center"><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                    <td><?= htmlspecialchars($row['nama_penyewa']); ?></td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td style="mso-number-format:'\@';"><?= htmlspecialchars($row['no_hp']); ?></td>
                    <td><?= htmlspecialchars($row['nama_peralatan']); ?></td>
                    <td><?= htmlspecialchars($row['kategori_peralatan']); ?></td>
                    <td class="text-center"><?= $row['jumlah']; ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></td>
                    <td class="text-center"><?= $row['lama_sewa']; ?></td>
                    <td class="text-right"><?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                    <td class="text-right"><?= number_format($row['denda'], 0, ',', '.'); ?></td>
                    <td class="text-right"><strong><?= number_format($grand, 0, ',', '.'); ?></strong></td>
                    <td class="text-center"><?= htmlspecialchars($row['status_pembayaran']); ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['status']); ?></td>
                </tr>
            <?php
                }
            ?>
                <tr class="total-row">
                    <td colspan="8" class="text-right">TOTAL KESELURUHAN:</td>
                    <td class="text-center"><?= $totalUnitDisewa; ?></td>
                    <td colspan="3"></td>
                    <td class="text-right"><?= number_format($totalOmzetSewa, 0, ',', '.'); ?></td>
                    <td class="text-right"><?= number_format($totalDenda, 0, ',', '.'); ?></td>
                    <td class="text-right"><strong><?= number_format($grandTotalSemua, 0, ',', '.'); ?></strong></td>
                    <td colspan="2"></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>
</html>
