<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// =====================================
// METRIK STATISTIK DASHBOARD
// =====================================

// 1. Total Booking
$qTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking");
$totalBooking = (int) mysqli_fetch_assoc($qTotal)['total'];

// 2. Booking Menunggu
$qMenunggu = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE status = 'Menunggu'");
$totalMenunggu = (int) mysqli_fetch_assoc($qMenunggu)['total'];

// 3. Booking Disetujui
$qDisetujui = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE status = 'Disetujui'");
$totalDisetujui = (int) mysqli_fetch_assoc($qDisetujui)['total'];

// 4. Booking Dikembalikan
$qKembali = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking WHERE status = 'Dikembalikan'");
$totalDikembalikan = (int) mysqli_fetch_assoc($qKembali)['total'];

// 5. Total Peralatan & Unit
$qPeralatan = mysqli_query($conn, "SELECT COUNT(*) AS total_jenis, SUM(stok) AS total_unit FROM peralatan");
$dataPeralatan = mysqli_fetch_assoc($qPeralatan);
$totalJenisPeralatan = (int) $dataPeralatan['total_jenis'];
$totalUnitStok = (int) ($dataPeralatan['total_unit'] ?? 0);

// 6. Total Pendapatan
$qPendapatan = mysqli_query($conn, "SELECT SUM(total_harga + denda) AS grand_omzet FROM booking WHERE status IN ('Disetujui', 'Dikembalikan')");
$grandOmzet = (int) (mysqli_fetch_assoc($qPendapatan)['grand_omzet'] ?? 0);

// 7. Booking Terbaru (Limit 5)
$qTerbaru = mysqli_query(
    $conn,
    "SELECT booking.*, peralatan.nama AS nama_peralatan 
     FROM booking 
     JOIN peralatan ON booking.id_peralatan = peralatan.id 
     ORDER BY booking.id DESC LIMIT 5"
);

include '../includes/header.php';

?>

<section class="riwayat-section">
    <div class="header-action-container">
        <div>
            <h2>Dashboard Utama Admin</h2>
            <p>Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>. Pantau metrik rental secara realtime.</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="tambah_peralatan.php" class="btn btn-primary">+ Tambah Peralatan</a>
            <a href="laporan.php" class="btn btn-secondary">📊 Laporan Omzet</a>
        </div>
    </div>

    <!-- Kotak Statistik Ringkasan -->
    <div class="dashboard-stats mt-4">
        <div class="stat-box">
            <h3>Antrean Menunggu</h3>
            <p style="color:#f59e0b;"><?= $totalMenunggu; ?></p>
            <a href="booking.php?status=Menunggu" class="text-sm">Proses Sekarang →</a>
        </div>
        <div class="stat-box">
            <h3>Sewa Aktif (Disetujui)</h3>
            <p style="color:#10b981;"><?= $totalDisetujui; ?></p>
            <a href="booking.php?status=Disetujui" class="text-sm">Lihat Transaksi →</a>
        </div>
        <div class="stat-box">
            <h3>Total Inventaris</h3>
            <p><?= $totalJenisPeralatan; ?> <small style="font-size:14px;">jenis (<?= $totalUnitStok; ?> unit)</small></p>
            <a href="peralatan.php" class="text-sm">Kelola Stok →</a>
        </div>
        <div class="stat-box">
            <h3>Total Omzet Rental</h3>
            <p style="font-size:22px; color:#1f2937;"><?= rupiah($grandOmzet); ?></p>
            <a href="laporan.php" class="text-sm">Buka Laporan Keuangan →</a>
        </div>
    </div>

    <!-- Tabel Booking Terbaru -->
    <div class="mt-5">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3>5 Transaksi Booking Terbaru</h3>
            <a href="booking.php" class="btn btn-sm btn-outline">Lihat Semua Data Booking →</a>
        </div>

        <?php if (mysqli_num_rows($qTerbaru) == 0) { ?>
            <div class="empty-state">
                <p>Belum ada aktivitas transaksi booking.</p>
            </div>
        <?php } else { ?>
            <div class="table-responsive">
                <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Penyewa</th>
                            <th>Peralatan</th>
                            <th>Jadwal Sewa</th>
                            <th>Unit</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($b = mysqli_fetch_assoc($qTerbaru)) { ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><strong><?= htmlspecialchars($b['nama_penyewa']); ?></strong></td>
                                <td><?= htmlspecialchars($b['nama_peralatan']); ?></td>
                                <td><small><?= date('d/m/Y', strtotime($b['tanggal_pinjam'])); ?> - <?= date('d/m/Y', strtotime($b['tanggal_kembali'])); ?></small></td>
                                <td><?= $b['jumlah']; ?> unit</td>
                                <td><strong><?= rupiah($b['total_harga']); ?></strong></td>
                                <td>
                                    <?php if ($b['status_pembayaran'] === 'Lunas') { ?>
                                        <span class="badge badge-success">Lunas</span>
                                    <?php } elseif ($b['status_pembayaran'] === 'Menunggu Verifikasi') { ?>
                                        <span class="badge badge-warning">Verifikasi</span>
                                    <?php } else { ?>
                                        <span class="badge badge-danger">Belum</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($b['status'] === 'Disetujui') { ?>
                                        <span class="badge badge-success">Disetujui</span>
                                    <?php } elseif ($b['status'] === 'Dikembalikan') { ?>
                                        <span class="badge badge-info">Selesai</span>
                                    <?php } elseif ($b['status'] === 'Ditolak') { ?>
                                        <span class="badge badge-danger">Ditolak</span>
                                    <?php } else { ?>
                                        <span class="badge badge-warning">Menunggu</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="detail_booking.php?id=<?= $b['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </div>
</section>

<?php

include '../includes/footer.php';

?>