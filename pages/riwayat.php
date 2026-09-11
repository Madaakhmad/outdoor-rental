<?php

require_once '../config/config.php';
require_once '../config/functions.php';

// =====================================
// CEK LOGIN
// =====================================
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$idUser = (int) $_SESSION['id_user'];
$pesanSukses = "";
$pesanError = "";

// Handle upload bukti susulan dari halaman riwayat
if (isset($_POST['upload_susulan'])) {
    $idBooking = (int) $_POST['id_booking'];
    $gambar = uploadBuktiPembayaran('bukti_file', '../assets/uploads/');

    if ($gambar !== false) {
        $stmtUp = mysqli_prepare(
            $conn,
            "UPDATE booking 
             SET bukti_pembayaran = ?, status_pembayaran = 'Menunggu Verifikasi' 
             WHERE id = ? AND id_user = ?"
        );
        mysqli_stmt_bind_param($stmtUp, "sii", $gambar, $idBooking, $idUser);
        if (mysqli_stmt_execute($stmtUp)) {
            $pesanSukses = "Bukti pembayaran berhasil diunggah! Menunggu verifikasi dari admin.";
        } else {
            $pesanError = "Gagal memperbarui data pembayaran.";
        }
    } else {
        $pesanError = "Berkas bukti pembayaran tidak valid atau terlalu besar (Maksimal 3 MB).";
    }
}

// =====================================
// AMBIL RIWAYAT BOOKING USER
// =====================================
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        booking.*,
        peralatan.nama AS nama_peralatan,
        peralatan.gambar AS gambar_peralatan
     FROM booking
     JOIN peralatan ON booking.id_peralatan = peralatan.id
     WHERE booking.id_user = ?
     ORDER BY booking.id DESC"
);

mysqli_stmt_bind_param($stmt, "i", $idUser);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

include '../includes/header.php';

?>

<section class="riwayat-section">
    <h2>Riwayat Penyewaan Saya</h2>
    <p>Halo, <strong><?= htmlspecialchars($_SESSION['nama']); ?></strong>. Berikut adalah daftar riwayat transaksi penyewaan alat outdoor Anda.</p>

    <?php if (isset($_GET['success'])) { ?>
        <div class="success-box">
            ✓ Penyewaan Anda berhasil dibuat! Silakan lakukan pembayaran dan simpan nota pemesanan Anda.
        </div>
    <?php } ?>

    <?php if ($pesanSukses != "") { ?>
        <div class="success-box"><?= $pesanSukses; ?></div>
    <?php } ?>

    <?php if ($pesanError != "") { ?>
        <div class="error-box"><?= $pesanError; ?></div>
    <?php } ?>

    <?php if (mysqli_num_rows($query) == 0) { ?>
        <div class="empty-state">
            <p>Anda belum memiliki riwayat transaksi penyewaan peralatan.</p>
            <a href="peralatan.php" class="btn btn-katalog mt-3">Mulai Sewa Sekarang →</a>
        </div>
    <?php } else { ?>
        <div class="table-responsive">
            <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Peralatan</th>
                        <th>Periode Sewa</th>
                        <th>Unit & Durasi</th>
                        <th>Total Biaya</th>
                        <th>Status Pembayaran</th>
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
                            <td><strong>#BK-<?= str_pad($booking['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($booking['nama_peralatan']); ?></strong>
                            </td>
                            <td>
                                <small>Pinjam: <?= date('d M Y', strtotime($booking['tanggal_pinjam'])); ?></small><br>
                                <small>Kembali: <?= date('d M Y', strtotime($booking['tanggal_kembali'])); ?></small>
                            </td>
                            <td>
                                <?= $booking['jumlah']; ?> Unit<br>
                                <small>(<?= $booking['lama_sewa']; ?> hari)</small>
                            </td>
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
                                    <button onclick="document.getElementById('upload-modal-<?= $booking['id']; ?>').style.display='block'" class="btn-sm btn-outline mt-1">Unggah Bukti</button>
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($booking['status'] === 'Disetujui') { ?>
                                    <span class="badge badge-success">Disetujui</span>
                                <?php } elseif ($booking['status'] === 'Dikembalikan') { ?>
                                    <span class="badge badge-info">Selesai</span>
                                <?php } elseif ($booking['status'] === 'Ditolak') { ?>
                                    <span class="badge badge-danger">Ditolak</span>
                                <?php } else { ?>
                                    <span class="badge badge-warning">Menunggu</span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="cetak_nota.php?id=<?= $booking['id']; ?>" target="_blank" class="btn btn-sm btn-print">
                                    🖨️ Cetak Nota
                                </a>
                            </td>
                        </tr>

                        <!-- Modal Unggah Bukti -->
                        <div id="upload-modal-<?= $booking['id']; ?>" class="modal-backdrop" style="display:none;">
                            <div class="modal-box">
                                <h3>Unggah Bukti Pembayaran #<?= $booking['id']; ?></h3>
                                <p>Transfer total <strong><?= rupiah($booking['total_harga'] + $booking['denda']); ?></strong> ke salah satu rekening resmi:</p>
                                <div class="bank-mini-list mb-3">
                                    <div class="bank-mini-item"><strong>BCA:</strong> <code>123-456-7890</code></div>
                                    <div class="bank-mini-item"><strong>BRI:</strong> <code>0987-01-000123-50-1</code></div>
                                    <div class="bank-mini-item"><strong>Mandiri:</strong> <code>137-00-1234567-8</code></div>
                                    <div class="bank-mini-item"><strong>Dompet Digital:</strong> <code>0812-3456-7890</code></div>
                                </div>
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_booking" value="<?= $booking['id']; ?>">
                                    <input type="file" name="bukti_file" accept=".jpg,.jpeg,.png,.webp" required>
                                    <div class="mt-3" style="display:flex; gap:8px;">
                                        <button type="submit" name="upload_susulan" class="btn btn-primary" style="flex:1;">Kirim Bukti Pembayaran</button>
                                        <button type="button" onclick="document.getElementById('upload-modal-<?= $booking['id']; ?>').style.display='none'" class="btn btn-secondary">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<?php

include '../includes/footer.php';

?>