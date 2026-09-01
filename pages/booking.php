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

// =====================================
// CEK ID PERALATAN
// =====================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$id = (int) $_GET['id'];
$idUser = (int) $_SESSION['id_user'];

// =====================================
// AMBIL DATA PERALATAN
// =====================================
$stmt = mysqli_prepare($conn, "SELECT * FROM peralatan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    echo "Peralatan tidak ditemukan.";
    exit;
}

$errors = [];

// =====================================
// PROSES FORM BOOKING
// =====================================
if (isset($_POST['booking'])) {
    $nama = trim($_POST['nama']);
    $telepon = trim($_POST['telepon']);
    $tanggal = trim($_POST['tanggal']);
    $lama = (int) ($_POST['lama'] ?? 0);
    $jumlah = (int) ($_POST['jumlah'] ?? 1);

    // Validasi input
    if ($nama === '') {
        $errors[] = "Nama penyewa harus diisi.";
    }

    if ($telepon === '') {
        $errors[] = "Nomor HP / WhatsApp harus diisi.";
    }

    if ($tanggal === '') {
        $errors[] = "Tanggal pinjam harus ditentukan.";
    } elseif ($tanggal < date('Y-m-d')) {
        $errors[] = "Tanggal pinjam tidak boleh sebelum hari ini.";
    }

    if ($lama <= 0) {
        $errors[] = "Lama sewa minimal 1 hari.";
    }

    if ($jumlah <= 0) {
        $errors[] = "Jumlah unit yang disewa minimal 1.";
    } elseif ($jumlah > $produk['stok']) {
        $errors[] = "Jumlah sewa melebihi kapasitas total stok (" . $produk['stok'] . " unit).";
    }

    if ($produk['stok'] <= 0) {
        $errors[] = "Peralatan saat ini sedang habis.";
    }

    // =================================
    // CEK KUOTA STOK PADA RENTANG TANGGAL
    // =================================
    if (empty($errors)) {
        $tanggalMulaiBaru = new DateTime($tanggal);
        $tanggalSelesaiBaru = clone $tanggalMulaiBaru;
        $tanggalSelesaiBaru->modify("+$lama days");
        $tglMulaiStr = $tanggalMulaiBaru->format('Y-m-d');
        $tglSelesaiStr = $tanggalSelesaiBaru->format('Y-m-d');

        // Cari jumlah unit yang aktif dibooking pada rentang tanggal bertabrakan
        $stmtCek = mysqli_prepare(
            $conn,
            "SELECT SUM(jumlah) AS total_terpakai 
             FROM booking 
             WHERE id_peralatan = ? 
             AND status IN ('Menunggu', 'Disetujui')
             AND NOT (tanggal_kembali <= ? OR tanggal_pinjam >= ?)"
        );
        mysqli_stmt_bind_param($stmtCek, "iss", $id, $tglMulaiStr, $tglSelesaiStr);
        mysqli_stmt_execute($stmtCek);
        $resCek = mysqli_stmt_get_result($stmtCek);
        $dataCek = mysqli_fetch_assoc($resCek);
        $unitTerpakai = (int) ($dataCek['total_terpakai'] ?? 0);

        $sisaTersedia = $produk['stok'] - $unitTerpakai;
        if ($jumlah > $sisaTersedia) {
            $errors[] = "Stok tidak mencukupi untuk rentang tanggal tersebut. Sisa unit yang tersedia pada tanggal ini: " . max(0, $sisaTersedia) . " unit.";
        }
    }

    // =================================
    // PROSES UPLOAD BUKTI PEMBAYARAN (OPSIONAL)
    // =================================
    $namaBukti = null;
    $statusPembayaran = 'Belum Bayar';

    if (empty($errors) && isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] != 4) {
        $namaBukti = uploadBuktiPembayaran('bukti_pembayaran', '../assets/uploads/');
        if ($namaBukti === false) {
            $errors[] = "Upload bukti pembayaran gagal. Pastikan format JPG, PNG, atau WEBP (Maks 3 MB).";
        } else {
            $statusPembayaran = 'Menunggu Verifikasi';
        }
    }

    // =================================
    // SIMPAN KE DATABASE
    // =================================
    if (empty($errors)) {
        $tanggalMulai = new DateTime($tanggal);
        $tanggalKembali = clone $tanggalMulai;
        $tanggalKembali->modify("+$lama days");
        $tglKembaliFinal = $tanggalKembali->format('Y-m-d');

        $totalHarga = (int) ($produk['harga'] * $lama * $jumlah);

        $stmtInsert = mysqli_prepare(
            $conn,
            "INSERT INTO booking (
                id_user, id_peralatan, nama_penyewa, no_hp, 
                tanggal_pinjam, tanggal_kembali, lama_sewa, jumlah, 
                total_harga, bukti_pembayaran, status_pembayaran, denda, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 'Menunggu')"
        );

        mysqli_stmt_bind_param(
            $stmtInsert,
            "iissssiisss",
            $idUser,
            $id,
            $nama,
            $telepon,
            $tanggal,
            $tglKembaliFinal,
            $lama,
            $jumlah,
            $totalHarga,
            $namaBukti,
            $statusPembayaran
        );

        if (mysqli_stmt_execute($stmtInsert)) {
            $newBookingId = mysqli_insert_id($conn);
            header("Location: riwayat.php?success=1&id=" . $newBookingId);
            exit;
        } else {
            $errors[] = "Gagal menyimpan data booking. Silakan coba lagi.";
        }
    }
}

include '../includes/header.php';

?>

<section class="login form-booking-container">
    <h2>Form Booking Peralatan</h2>

    <?php if (!empty($errors)) { ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <!-- Kartu Informasi Barang -->
    <div class="card card-horizontal">
        <img
            src="../assets/uploads/<?= htmlspecialchars($produk['gambar'] ?: 'default.jpg'); ?>"
            alt="<?= htmlspecialchars($produk['nama']); ?>"
            style="width: 160px; height: 120px; object-fit: cover;">
        <div>
            <h3><?= htmlspecialchars($produk['nama']); ?></h3>
            <p>Kategori: <strong><?= htmlspecialchars($produk['kategori']); ?></strong></p>
            <p>Harga Sewa: <strong><?= rupiah($produk['harga']); ?> / hari</strong></p>
            <p>Total Stok: <strong><?= $produk['stok']; ?> unit</strong></p>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data" class="mt-4">
        <label>Nama Lengkap Penyewa</label>
        <input
            type="text"
            name="nama"
            value="<?= htmlspecialchars($_POST['nama'] ?? $_SESSION['nama']); ?>"
            required>

        <label>Nomor HP / WhatsApp Aktif</label>
        <input
            type="text"
            name="telepon"
            placeholder="Contoh: 081234567890"
            value="<?= htmlspecialchars($_POST['telepon'] ?? ''); ?>"
            required>

        <div class="form-grid">
            <div>
                <label>Tanggal Mulai Pinjam</label>
                <input
                    type="date"
                    name="tanggal"
                    id="tanggalInput"
                    min="<?= date('Y-m-d'); ?>"
                    value="<?= htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>"
                    required>
            </div>

            <div>
                <label>Lama Sewa (Hari)</label>
                <input
                    type="number"
                    name="lama"
                    id="lamaInput"
                    min="1"
                    value="<?= htmlspecialchars($_POST['lama'] ?? '1'); ?>"
                    required>
            </div>

            <div>
                <label>Jumlah Unit</label>
                <input
                    type="number"
                    name="jumlah"
                    id="jumlahInput"
                    min="1"
                    max="<?= $produk['stok']; ?>"
                    value="<?= htmlspecialchars($_POST['jumlah'] ?? '1'); ?>"
                    required>
            </div>
        </div>

        <div class="info-ringkasan">
            <p>Tanggal Pengembalian: <strong id="tanggalKembaliPreview">-</strong></p>
            <p>Estimasi Total Biaya: <strong id="totalHargaPreview" class="text-highlight">Rp 0</strong></p>
        </div>

        <!-- Info Rekening Bank Resmi -->
        <div class="bank-info-card mt-3">
            <h4>💳 Rekening Pembayaran Resmi</h4>
            <p class="text-muted">Silakan lakukan transfer sesuai estimasi biaya di atas ke salah satu rekening resmi berikut:</p>
            <div class="bank-grid">
                <div class="bank-item">
                    <span class="bank-badge">BCA</span>
                    <strong>123-456-7890</strong>
                    <small>a.n. Outdoor Rental Official</small>
                </div>
                <div class="bank-item">
                    <span class="bank-badge">BRI</span>
                    <strong>0987-01-000123-50-1</strong>
                    <small>a.n. Outdoor Rental Official</small>
                </div>
                <div class="bank-item">
                    <span class="bank-badge">Mandiri</span>
                    <strong>137-00-1234567-8</strong>
                    <small>a.n. Outdoor Rental Official</small>
                </div>
                <div class="bank-item">
                    <span class="bank-badge">E-Wallet</span>
                    <strong>0812-3456-7890</strong>
                    <small>DANA / GoPay / ShopeePay</small>
                </div>
            </div>
        </div>

        <label class="mt-3">Upload Bukti Pembayaran / DP (Opsional)</label>
        <input
            type="file"
            name="bukti_pembayaran"
            accept=".jpg,.jpeg,.png,.webp">
        <small class="text-muted">Bisa diunggah sekarang atau nanti melalui menu Riwayat Booking.</small>

        <div style="display:flex; gap:10px; margin-top:20px;">
            <button
                type="submit"
                name="booking"
                class="btn btn-primary btn-cta"
                style="flex: 2;">
                ✓ Konfirmasi Booking
            </button>
            <a href="peralatan.php" class="btn btn-secondary" style="flex: 1; text-align:center;">
                Batal
            </a>
        </div>
    </form>
</section>

<script>
    const hargaPerHari = <?= (int)$produk['harga']; ?>;
    const lamaInput = document.getElementById('lamaInput');
    const jumlahInput = document.getElementById('jumlahInput');
    const tanggalInput = document.getElementById('tanggalInput');
    const tanggalKembaliPreview = document.getElementById('tanggalKembaliPreview');
    const totalHargaPreview = document.getElementById('totalHargaPreview');

    function hitungTotal() {
        const lama = parseInt(lamaInput.value) || 0;
        const jumlah = parseInt(jumlahInput.value) || 0;

        // Kalkulasi Total: Harga x Lama x Jumlah
        const grandTotal = hargaPerHari * lama * jumlah;
        totalHargaPreview.textContent = 'Rp ' + grandTotal.toLocaleString('id-ID');

        // Kalkulasi Tanggal Pengembalian
        if (tanggalInput.value && lama > 0) {
            const tgl = new Date(tanggalInput.value + 'T00:00:00');
            tgl.setDate(tgl.getDate() + lama);
            const yyyy = tgl.getFullYear();
            const mm = String(tgl.getMonth() + 1).padStart(2, '0');
            const dd = String(tgl.getDate()).padStart(2, '0');
            tanggalKembaliPreview.textContent = `${dd}-${mm}-${yyyy}`;
        } else {
            tanggalKembaliPreview.textContent = '-';
        }
    }

    lamaInput.addEventListener('input', hitungTotal);
    jumlahInput.addEventListener('input', hitungTotal);
    tanggalInput.addEventListener('change', hitungTotal);

    // Hitung saat load
    hitungTotal();
</script>

<?php

include '../includes/footer.php';

?>