<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

$filterStatus = $_GET['status'] ?? '';
$sql = "SELECT booking.*, peralatan.nama AS nama_peralatan, users.username 
        FROM booking 
        JOIN peralatan ON booking.id_peralatan = peralatan.id 
        JOIN users ON booking.id_user = users.id ";

if (!empty($filterStatus)) {
    $sql .= " WHERE booking.status = '" . mysqli_real_escape_string($conn, $filterStatus) . "'";
}
$sql .= " ORDER BY booking.id DESC";

$query = mysqli_query($conn, $sql);

include '../includes/header.php';

?>

<section class="riwayat-section">
    <h2>Kelola Data Transaksi Penyewaan</h2>
    <p>Daftar seluruh transaksi pemesanan alat outdoor oleh pelanggan.</p>

    <!-- Filter Status Tab -->
    <div class="filter-tab-container mt-3 mb-3">
        <a href="booking.php" class="tab-item <?= ($filterStatus === '') ? 'active' : ''; ?>">Semua</a>
        <a href="booking.php?status=Menunggu" class="tab-item <?= ($filterStatus === 'Menunggu') ? 'active' : ''; ?>">Menunggu</a>
        <a href="booking.php?status=Disetujui" class="tab-item <?= ($filterStatus === 'Disetujui') ? 'active' : ''; ?>">Disetujui</a>
        <a href="booking.php?status=Dikembalikan" class="tab-item <?= ($filterStatus === 'Dikembalikan') ? 'active' : ''; ?>">Dikembalikan</a>
        <a href="booking.php?status=Ditolak" class="tab-item <?= ($filterStatus === 'Ditolak') ? 'active' : ''; ?>">Ditolak</a>
    </div>

    <?php if (mysqli_num_rows($query) == 0) { ?>
        <div class="empty-state">
            <p>Tidak ada transaksi penyewaan pada kategori status ini.</p>
        </div>
    <?php } else { ?>
        <div class="table-responsive">
            <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Penyewa</th>
                        <th>Peralatan</th>
                        <th>Periode Sewa</th>
                        <th>Unit</th>
                        <th>Total Biaya</th>
                        <th>Pembayaran</th>
                        <th>Status Sewa</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($booking = mysqli_fetch_assoc($query)) {
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <strong><?= htmlspecialchars($booking['nama_penyewa']); ?></strong><br>
                                <small>HP: <?= htmlspecialchars($booking['no_hp']); ?></small>
                            </td>
                            <td><?= htmlspecialchars($booking['nama_peralatan']); ?></td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($booking['tanggal_pinjam'])); ?> - <?= date('d/m/Y', strtotime($booking['tanggal_kembali'])); ?></small><br>
                                <small>(<?= $booking['lama_sewa']; ?> hari)</small>
                            </td>
                            <td><strong><?= $booking['jumlah']; ?> unit</strong></td>
                            <td>
                                <strong><?= rupiah($booking['total_harga']); ?></strong>
                                <?php if ($booking['denda'] > 0) { ?>
                                    <br><small class="text-danger">+ Denda: <?= rupiah($booking['denda']); ?></small>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($booking['status_pembayaran'] === 'Lunas') { ?>
                                    <span class="badge badge-success">Lunas</span>
                                <?php } elseif ($booking['status_pembayaran'] === 'Menunggu Verifikasi') { ?>
                                    <span class="badge badge-warning">Verifikasi</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Belum Bayar</span>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'Disetujui') { ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php } elseif ($booking['status'] === 'Dikembalikan') { ?>
                                    <span class="badge badge-info">Dikembalikan</span>
                                <?php } elseif ($booking['status'] === 'Ditolak') { ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php } else { ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <a href="detail_booking.php?id=<?= $booking['id']; ?>" class="btn btn-sm btn-primary">
                                        Rincian
                                    </a>
                                    <a href="../pages/cetak_nota.php?id=<?= $booking['id']; ?>" target="_blank" class="btn btn-sm btn-print">
                                        Cetak Nota
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<?php

include '../includes/footer.php';

?>