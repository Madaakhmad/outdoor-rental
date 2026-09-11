<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// Filter Tanggal
$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01'); // Default awal bulan ini
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d'); // Default hari ini
$statusFilter = $_GET['status'] ?? '';
$bayarFilter = $_GET['status_bayar'] ?? '';

$sql = "SELECT booking.*, peralatan.nama AS nama_peralatan, peralatan.kategori AS kategori_peralatan 
        FROM booking 
        JOIN peralatan ON booking.id_peralatan = peralatan.id 
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

// Kalkulasi Ringkasan Metrik
$totalTransaksi = 0;
$totalUnitDisewa = 0;
$totalOmzetSewa = 0;
$totalDenda = 0;

$dataLaporan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $totalTransaksi++;
    $totalUnitDisewa += (int) $row['jumlah'];
    // Hitung omzet jika pembayaran lunas atau transaksi selesai/disetujui
    $totalOmzetSewa += (int) $row['total_harga'];
    $totalDenda += (int) $row['denda'];
    $dataLaporan[] = $row;
}

$grandTotalPendapatan = $totalOmzetSewa + $totalDenda;

include '../includes/header.php';

?>

<section class="riwayat-section">
    <div class="header-action-container">
        <div>
            <h2>Laporan Transaksi & Pendapatan Rental</h2>
            <p>Rekapitulasi performa transaksi penyewaan alat dan omzet pendapatan.</p>
        </div>
        <div style="display:flex; gap:8px;" class="no-print">
            <a href="export_laporan.php?tgl_awal=<?= urlencode($tglAwal); ?>&tgl_akhir=<?= urlencode($tglAkhir); ?>&status=<?= urlencode($statusFilter); ?>&status_bayar=<?= urlencode($bayarFilter); ?>" class="btn btn-success">
                📥 Unduh Laporan Excel
            </a>
            <button onclick="window.print()" class="btn btn-print">🖨️ Cetak Rekap Laporan</button>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-box no-print mt-3">
        <form method="GET" action="laporan.php" class="filter-grid">
            <div>
                <label>Dari Tanggal:</label>
                <input type="date" name="tgl_awal" value="<?= htmlspecialchars($tglAwal); ?>" required>
            </div>
            <div>
                <label>Sampai Tanggal:</label>
                <input type="date" name="tgl_akhir" value="<?= htmlspecialchars($tglAkhir); ?>" required>
            </div>
            <div>
                <label>Status Sewa:</label>
                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="Disetujui" <?= ($statusFilter === 'Disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                    <option value="Dikembalikan" <?= ($statusFilter === 'Dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                    <option value="Menunggu" <?= ($statusFilter === 'Menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                    <option value="Ditolak" <?= ($statusFilter === 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                </select>
            </div>
            <div>
                <label>Status Pembayaran:</label>
                <select name="status_bayar">
                    <option value="">Semua Pembayaran</option>
                    <option value="Lunas" <?= ($bayarFilter === 'Lunas') ? 'selected' : ''; ?>>Lunas</option>
                    <option value="Menunggu Verifikasi" <?= ($bayarFilter === 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                    <option value="Belum Bayar" <?= ($bayarFilter === 'Belum Bayar') ? 'selected' : ''; ?>>Belum Bayar</option>
                </select>
            </div>
            <div style="display:flex; align-items:flex-end; gap:5px;">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="laporan.php" class="btn btn-secondary">Atur Ulang</a>
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Laporan -->
    <div class="dashboard-stats mt-4">
        <div class="stat-box">
            <h3>Total Transaksi</h3>
            <p><?= $totalTransaksi; ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Unit Disewa</h3>
            <p><?= $totalUnitDisewa; ?> <small style="font-size:14px;">unit</small></p>
        </div>
        <div class="stat-box">
            <h3>Omzet Biaya Sewa</h3>
            <p style="font-size: 20px;"><?= rupiah($totalOmzetSewa); ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Denda</h3>
            <p style="font-size: 20px; color:#b91c1c;"><?= rupiah($totalDenda); ?></p>
        </div>
    </div>

    <div class="grand-total-banner mt-3">
        <h3>Grand Total Pendapatan (Sewa + Denda): <span><?= rupiah($grandTotalPendapatan); ?></span></h3>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="table-responsive mt-4">
        <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
            <thead>
                <tr>
                    <th>No</th>
                    <th>ID</th>
                    <th>Penyewa</th>
                    <th>Peralatan</th>
                    <th>Periode Sewa</th>
                    <th>Unit</th>
                    <th>Biaya Sewa</th>
                    <th>Denda</th>
                    <th>Total</th>
                    <th>Pembayaran</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dataLaporan)) { ?>
                    <tr>
                        <td colspan="11" style="text-align: center; padding: 25px;">
                            Tidak ada data transaksi pada rentang tanggal yang dipilih.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php $no = 1; foreach ($dataLaporan as $row) { 
                        $totalBaris = $row['total_harga'] + $row['denda'];
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>#<?= $row['id']; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($row['nama_penyewa']); ?></strong><br>
                                <small>HP: <?= htmlspecialchars($row['no_hp']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($row['nama_peralatan']); ?></td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?> - <?= date('d/m/Y', strtotime($row['tanggal_kembali'])); ?></small>
                            </td>
                            <td><?= $row['jumlah']; ?> unit</td>
                            <td><?= rupiah($row['total_harga']); ?></td>
                            <td>
                                <?php if ($row['denda'] > 0) { ?>
                                    <span class="text-danger"><?= rupiah($row['denda']); ?></span>
                                <?php } else { ?>
                                    -
                                <?php } ?>
                            </td>
                            <td><strong><?= rupiah($totalBaris); ?></strong></td>
                            <td>
                                <?php if ($row['status_pembayaran'] === 'Lunas') { ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php } elseif ($row['status_pembayaran'] === 'Menunggu Verifikasi') { ?>
                                    <span class="badge badge-warning">Verifikasi</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Belum</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'Disetujui') { ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php } elseif ($row['status'] === 'Dikembalikan') { ?>
                                    <span class="badge badge-info">Selesai</span>
                                <?php } elseif ($row['status'] === 'Ditolak') { ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php } else { ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<?php

include '../includes/footer.php';

?>

