<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// =====================================
// CEK ID BOOKING
// =====================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: booking.php");
    exit;
}

$id = (int) $_GET['id'];
$pesanSukses = "";
$pesanError = "";

// =====================================
// PROSES SETUJUI BOOKING
// =====================================
if (isset($_POST['setujui'])) {
    $stmtCek = mysqli_prepare(
        $conn,
        "SELECT b.id, b.id_peralatan, b.jumlah, b.status, p.stok 
         FROM booking b 
         JOIN peralatan p ON b.id_peralatan = p.id 
         WHERE b.id = ?"
    );
    mysqli_stmt_bind_param($stmtCek, "i", $id);
    mysqli_stmt_execute($stmtCek);
    $dataB = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCek));

    if (!$dataB) {
        die("Booking tidak ditemukan.");
    }
    if ($dataB['status'] !== 'Menunggu') {
        die("Booking ini sudah diproses sebelumnya.");
    }
    if ($dataB['stok'] < $dataB['jumlah']) {
        $pesanError = "Stok peralatan tidak mencukupi! Sisa stok saat ini: " . $dataB['stok'] . " unit, sementara pesanan membutuhkan " . $dataB['jumlah'] . " unit.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            // 1. Kurangi stok sesuai jumlah unit sewa
            $stmtStok = mysqli_prepare(
                $conn,
                "UPDATE peralatan SET stok = stok - ? WHERE id = ? AND stok >= ?"
            );
            mysqli_stmt_bind_param($stmtStok, "iii", $dataB['jumlah'], $dataB['id_peralatan'], $dataB['jumlah']);
            mysqli_stmt_execute($stmtStok);

            // 2. Ubah status booking
            $stmtStatus = mysqli_prepare(
                $conn,
                "UPDATE booking SET status = 'Disetujui' WHERE id = ? AND status = 'Menunggu'"
            );
            mysqli_stmt_bind_param($stmtStatus, "i", $id);
            mysqli_stmt_execute($stmtStatus);

            mysqli_commit($conn);
            $pesanSukses = "Booking berhasil disetujui dan stok telah diperbarui.";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $pesanError = "Gagal memproses persetujuan booking: " . $e->getMessage();
        }
    }
}

// =====================================
// PROSES TOLAK BOOKING
// =====================================
if (isset($_POST['tolak'])) {
    $stmtTolak = mysqli_prepare(
        $conn,
        "UPDATE booking SET status = 'Ditolak' WHERE id = ? AND status = 'Menunggu'"
    );
    mysqli_stmt_bind_param($stmtTolak, "i", $id);
    if (mysqli_stmt_execute($stmtTolak)) {
        $pesanSukses = "Booking berhasil ditolak.";
    } else {
        $pesanError = "Gagal menolak booking.";
    }
}

// =====================================
// PROSES PENGEMBALIAN BARANG & HITUNG DENDA
// =====================================
if (isset($_POST['kembalikan'])) {
    $stmtCekKembali = mysqli_prepare(
        $conn,
        "SELECT b.*, p.harga AS harga_harian 
         FROM booking b 
         JOIN peralatan p ON b.id_peralatan = p.id 
         WHERE b.id = ?"
    );
    mysqli_stmt_bind_param($stmtCekKembali, "i", $id);
    mysqli_stmt_execute($stmtCekKembali);
    $dataK = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCekKembali));

    if (!$dataK || $dataK['status'] !== 'Disetujui') {
        die("Booking tidak dapat dikembalikan.");
    }

    // Hitung denda keterlambatan jika tanggal pengembalian lewat dari batas sewa
    $tglBatas = new DateTime($dataK['tanggal_kembali']);
    $tglHariIni = new DateTime(date('Y-m-d'));
    $denda = 0;

    if ($tglHariIni > $tglBatas) {
        $selisih = $tglHariIni->diff($tglBatas);
        $hariTelat = (int) $selisih->days;
        // Denda harian = harga sewa harian * jumlah unit * hari telat
        $denda = (int) ($hariTelat * $dataK['harga_harian'] * $dataK['jumlah']);
    }

    mysqli_begin_transaction($conn);
    try {
        // 1. Ubah status booking & simpan denda
        $stmtUpK = mysqli_prepare(
            $conn,
            "UPDATE booking 
             SET status = 'Dikembalikan', denda = ? 
             WHERE id = ? AND status = 'Disetujui'"
        );
        mysqli_stmt_bind_param($stmtUpK, "ii", $denda, $id);
        mysqli_stmt_execute($stmtUpK);

        // 2. Kembalikan stok sesuai jumlah unit sewa
        $stmtTambahStok = mysqli_prepare(
            $conn,
            "UPDATE peralatan SET stok = stok + ? WHERE id = ?"
        );
        mysqli_stmt_bind_param($stmtTambahStok, "ii", $dataK['jumlah'], $dataK['id_peralatan']);
        mysqli_stmt_execute($stmtTambahStok);

        mysqli_commit($conn);
        $pesanSukses = "Peralatan berhasil ditandai dikembalikan. Stok inventaris telah ditambahkan kembali.";
        if ($denda > 0) {
            $pesanSukses .= " Terdeteksi keterlambatan! Denda otomatis dihitung: " . rupiah($denda);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $pesanError = "Gagal memproses pengembalian: " . $e->getMessage();
    }
}

// =====================================
// PROSES UPDATE STATUS PEMBAYARAN
// =====================================
if (isset($_POST['update_pembayaran'])) {
    $statusBayarBaru = $_POST['status_pembayaran'];
    $stmtBayar = mysqli_prepare(
        $conn,
        "UPDATE booking SET status_pembayaran = ? WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmtBayar, "si", $statusBayarBaru, $id);
    if (mysqli_stmt_execute($stmtBayar)) {
        $pesanSukses = "Status pembayaran berhasil diperbarui menjadi: " . $statusBayarBaru;
    }
}

// =====================================
// AMBIL DATA DETAIL BOOKING
// =====================================
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        booking.*,
        peralatan.nama AS nama_peralatan,
        peralatan.kategori AS kategori_peralatan,
        peralatan.harga AS harga_peralatan,
        peralatan.stok AS stok_tersedia,
        users.nama AS nama_akun,
        users.username
     FROM booking
     JOIN peralatan ON booking.id_peralatan = peralatan.id
     JOIN users ON booking.id_user = users.id
     WHERE booking.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$booking) {
    echo "<script>alert('Data booking tidak ditemukan!'); window.location.href = 'booking.php';</script>";
    exit;
}

include '../includes/header.php';

?>

<section class="login form-booking-container">
    <h2>Detail & Verifikasi Booking #<?= $booking['id']; ?></h2>

    <?php if ($pesanSukses != "") { ?>
        <div class="success-box"><?= $pesanSukses; ?></div>
    <?php } ?>

    <?php if ($pesanError != "") { ?>
        <div class="error-box"><?= $pesanError; ?></div>
    <?php } ?>

    <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
        <tr>
            <th width="30%">Nama Penyewa</th>
            <td><strong><?= htmlspecialchars($booking['nama_penyewa']); ?></strong> (Akun: <?= htmlspecialchars($booking['username']); ?>)</td>
        </tr>
        <tr>
            <th>Nomor WhatsApp / HP</th>
            <td><?= htmlspecialchars($booking['no_hp']); ?></td>
        </tr>
        <tr>
            <th>Peralatan</th>
            <td><?= htmlspecialchars($booking['nama_peralatan']); ?> (<?= htmlspecialchars($booking['kategori_peralatan']); ?>)</td>
        </tr>
        <tr>
            <th>Harga Harian</th>
            <td><?= rupiah($booking['harga_peralatan']); ?> / hari</td>
        </tr>
        <tr>
            <th>Jumlah Unit Disewa</th>
            <td><strong><?= $booking['jumlah']; ?> unit</strong> (Sisa stok saat ini: <?= $booking['stok_tersedia']; ?> unit)</td>
        </tr>
        <tr>
            <th>Jadwal Sewa</th>
            <td>
                Pinjam: <strong><?= date('d M Y', strtotime($booking['tanggal_pinjam'])); ?></strong><br>
                Batas Kembali: <strong><?= date('d M Y', strtotime($booking['tanggal_kembali'])); ?></strong><br>
                Durasi: <?= $booking['lama_sewa']; ?> hari
            </td>
        </tr>
        <tr>
            <th>Total Biaya Sewa</th>
            <td><strong><?= rupiah($booking['total_harga']); ?></strong></td>
        </tr>
        <tr>
            <th>Denda Keterlambatan</th>
            <td>
                <?php if ($booking['denda'] > 0) { ?>
                    <span class="text-danger font-bold"><?= rupiah($booking['denda']); ?></span>
                <?php } else { ?>
                    Rp 0
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>Status Booking</th>
            <td>
                <?php if ($booking['status'] === 'Disetujui') { ?>
                    <span class="badge badge-success">Disetujui</span>
                <?php } elseif ($booking['status'] === 'Dikembalikan') { ?>
                    <span class="badge badge-info">Selesai / Dikembalikan</span>
                <?php } elseif ($booking['status'] === 'Ditolak') { ?>
                    <span class="badge badge-danger">Ditolak</span>
                <?php } else { ?>
                    <span class="badge badge-warning">Menunggu Konfirmasi</span>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>Status Pembayaran</th>
            <td>
                <form method="POST" style="display:inline-flex; gap:10px; align-items:center;">
                    <select name="status_pembayaran" style="padding:6px; border-radius:6px;">
                        <option value="Belum Bayar" <?= ($booking['status_pembayaran'] === 'Belum Bayar') ? 'selected' : ''; ?>>Belum Bayar</option>
                        <option value="Menunggu Verifikasi" <?= ($booking['status_pembayaran'] === 'Menunggu Verifikasi') ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                        <option value="Lunas" <?= ($booking['status_pembayaran'] === 'Lunas') ? 'selected' : ''; ?>>Lunas</option>
                    </select>
                    <button type="submit" name="update_pembayaran" class="btn btn-sm btn-primary">Ubah Status</button>
                </form>
            </td>
        </tr>
        <tr>
            <th>Bukti Pembayaran</th>
            <td>
                <?php if (!empty($booking['bukti_pembayaran'])) { ?>
                    <a href="../assets/uploads/<?= htmlspecialchars($booking['bukti_pembayaran']); ?>" target="_blank">
                        <img src="../assets/uploads/<?= htmlspecialchars($booking['bukti_pembayaran']); ?>" alt="Bukti Transfer" style="max-width: 200px; border-radius: 6px; border: 1px solid #ddd;">
                    </a>
                    <br><small><a href="../assets/uploads/<?= htmlspecialchars($booking['bukti_pembayaran']); ?>" target="_blank">Lihat Gambar Penuh ↗</a></small>
                <?php } else { ?>
                    <span class="text-muted">Customer belum mengunggah bukti pembayaran.</span>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="mt-4" style="display:flex; gap:10px; flex-wrap:wrap;">
        <?php if ($booking['status'] === 'Menunggu') { ?>
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui booking ini? Stok akan otomatis dikurangi.');">
                <button type="submit" name="setujui" class="btn btn-success">✓ Setujui Booking</button>
            </form>

            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak booking ini?');">
                <button type="submit" name="tolak" class="btn btn-danger">✕ Tolak Booking</button>
            </form>
        <?php } elseif ($booking['status'] === 'Disetujui') { ?>
            <form method="POST" onsubmit="return confirm('Apakah peralatan sudah dikembalikan lengkap? Stok akan otomatis dikembalikan ke inventaris.');">
                <button type="submit" name="kembalikan" class="btn btn-primary">📦 Tandai Dikembalikan</button>
            </form>
        <?php } ?>

        <a href="../pages/cetak_nota.php?id=<?= $booking['id']; ?>" target="_blank" class="btn btn-print">🖨️ Cetak Nota</a>
        <a href="booking.php" class="btn btn-secondary">← Kembali ke Data Booking</a>
    </div>
</section>

<?php

include '../includes/footer.php';

?>