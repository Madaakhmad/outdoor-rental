<?php

require_once '../config/config.php';
require_once '../config/functions.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID Booking tidak valid.");
}

$id = (int) $_GET['id'];

// Ambil data transaksi
$stmt = mysqli_prepare(
    $conn,
    "SELECT 
        booking.*, 
        peralatan.nama AS nama_peralatan,
        peralatan.kategori AS kategori_peralatan,
        peralatan.harga AS harga_satuan,
        users.nama AS nama_user,
        users.username
     FROM booking
     JOIN peralatan ON booking.id_peralatan = peralatan.id
     JOIN users ON booking.id_user = users.id
     WHERE booking.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($res);

if (!$data) {
    die("Data booking tidak ditemukan.");
}

// Pastikan hanya pemilik data atau admin yang bisa mengakses
if ($_SESSION['role'] !== 'admin' && $_SESSION['id_user'] != $data['id_user']) {
    die("Akses ditolak. Anda tidak memiliki izin untuk melihat nota ini.");
}

$grandTotal = $data['total_harga'] + $data['denda'];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Booking #<?= $data['id']; ?> - <?= APP_NAME; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f0f2f5;
            padding: 30px;
            color: #333;
        }
        .invoice-box {
            max-width: 750px;
            margin: auto;
            padding: 35px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #6b7280;
        }
        .invoice-number {
            text-align: right;
        }
        .invoice-number h2 {
            font-size: 20px;
            color: #f59e0b;
        }
        .invoice-number p {
            font-size: 12px;
            color: #6b7280;
        }
        .customer-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .customer-details div {
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table th {
            background: #1f2937;
            color: white;
            text-align: left;
            padding: 10px 12px;
            font-size: 13px;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }
        .total-section {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 14px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }

        .invoice-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #d1d5db;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.6;
        }
        .print-btn-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print-action {
            background: #f59e0b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
        }
        .btn-print-action:hover {
            background: #d97706;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .invoice-box {
                box-shadow: none;
                border-radius: 0;
                padding: 10px;
                max-width: 100%;
            }
            .print-btn-container {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-container">
        <button onclick="window.print()" class="btn-print-action">🖨️ Cetak / Simpan PDF</button>
        <button onclick="window.close()" class="btn-print-action" style="background:#6b7280;">Tutup</button>
    </div>

    <div class="invoice-box">
        <div class="invoice-header">
            <div>
                <div class="brand-title"><?= APP_NAME; ?></div>
                <div class="brand-subtitle">Outdoor Rental - Penyewaan Alat Camping & Kegiatan Gunung</div>
            </div>
            <div class="invoice-number">
                <h2>NOTA PENYEWAAN</h2>
                <p>No: <strong>#BK-<?= str_pad($data['id'], 5, '0', STR_PAD_LEFT); ?></strong></p>
                <p>Tgl Dibuat: <?= date('d/m/Y H:i', strtotime($data['created_at'])); ?></p>
            </div>
        </div>

        <div class="customer-details">
            <div>
                <strong>Data Penyewa:</strong><br>
                Nama: <?= htmlspecialchars($data['nama_penyewa']); ?><br>
                No. HP: <?= htmlspecialchars($data['no_hp']); ?><br>
                Akun: <?= htmlspecialchars($data['username']); ?>
            </div>
            <div>
                <strong>Jadwal Penyewaan:</strong><br>
                Mulai Pinjam: <strong><?= date('d M Y', strtotime($data['tanggal_pinjam'])); ?></strong><br>
                Batas Kembali: <strong><?= date('d M Y', strtotime($data['tanggal_kembali'])); ?></strong><br>
                Durasi Sewa: <?= $data['lama_sewa']; ?> Hari
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Peralatan</th>
                    <th>Kategori</th>
                    <th>Harga / Hari</th>
                    <th>Durasi</th>
                    <th>Jumlah</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><?= htmlspecialchars($data['nama_peralatan']); ?></strong></td>
                    <td><?= htmlspecialchars($data['kategori_peralatan']); ?></td>
                    <td><?= rupiah($data['harga_satuan']); ?></td>
                    <td><?= $data['lama_sewa']; ?> Hari</td>
                    <td><?= $data['jumlah']; ?> Unit</td>
                    <td style="text-align: right;"><strong><?= rupiah($data['total_harga']); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <div class="total-section">
            <div>
                <p>Status Sewa: 
                    <?php if ($data['status'] === 'Disetujui') { ?>
                        <span class="status-badge badge-success">Disetujui</span>
                    <?php } elseif ($data['status'] === 'Dikembalikan') { ?>
                        <span class="status-badge badge-info">Selesai / Dikembalikan</span>
                    <?php } elseif ($data['status'] === 'Ditolak') { ?>
                        <span class="status-badge badge-danger">Ditolak</span>
                    <?php } else { ?>
                        <span class="status-badge badge-warning">Menunggu Konfirmasi</span>
                    <?php } ?>
                </p>
                <p style="margin-top: 6px;">Status Pembayaran: 
                    <?php if ($data['status_pembayaran'] === 'Lunas') { ?>
                        <span class="status-badge badge-success">Lunas</span>
                    <?php } elseif ($data['status_pembayaran'] === 'Menunggu Verifikasi') { ?>
                        <span class="status-badge badge-warning">Menunggu Verifikasi</span>
                    <?php } else { ?>
                        <span class="status-badge badge-danger">Belum Bayar</span>
                    <?php } ?>
                </p>
            </div>
            <div style="text-align: right; line-height: 1.8;">
                <p>Total Sewa: <strong><?= rupiah($data['total_harga']); ?></strong></p>
                <?php if ($data['denda'] > 0) { ?>
                    <p style="color:#b91c1c;">Denda Keterlambatan: <strong>+ <?= rupiah($data['denda']); ?></strong></p>
                <?php } ?>
                <h3 style="color:#1f2937; margin-top: 5px;">Total Keseluruhan: <?= rupiah($grandTotal); ?></h3>
            </div>
        </div>

        <div class="invoice-footer">
            <p><strong>Syarat & Ketentuan Pengambilan Alat:</strong></p>
            <ol style="padding-left: 18px; margin-top: 5px;">
                <li>Tunjukkan bukti nota fisik atau digital ini saat pengambilan peralatan di toko / pos rental.</li>
                <li>Wajib meninggalkan kartu identitas asli (KTP/SIM/KTM) yang masih berlaku sebagai jaminan.</li>
                <li>Keterlambatan pengembalian melewati tanggal kembali akan dikenakan denda harian per unit barang.</li>
                <li>Penyewa bertanggung jawab penuh atas kebersihan dan keutuhan peralatan selama masa sewa.</li>
            </ol>
        </div>
    </div>

</body>
</html>

