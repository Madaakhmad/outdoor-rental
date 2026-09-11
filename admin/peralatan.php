<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

$peralatan = getPeralatan($conn);

include '../includes/header.php';

?>

<section class="riwayat-section">
    <div class="header-action-container">
        <div>
            <h2>Kelola Inventaris Peralatan</h2>
            <p>Daftar seluruh stok peralatan rental outdoor yang terdaftar di sistem.</p>
        </div>
        <div>
            <a href="tambah_peralatan.php" class="btn btn-primary">+ Tambah Peralatan Baru</a>
        </div>
    </div>

    <!-- Alert Notifikasi Flash -->
    <?php if (isset($_GET['success'])) { ?>
        <?php if ($_GET['success'] === 'add') { ?>
            <div class="success-box">✓ Peralatan baru berhasil ditambahkan ke katalog!</div>
        <?php } elseif ($_GET['success'] === 'edit') { ?>
            <div class="success-box">✓ Data peralatan berhasil diperbarui!</div>
        <?php } elseif ($_GET['success'] === 'delete') { ?>
            <div class="success-box">✓ Peralatan berhasil dihapus dari sistem!</div>
        <?php } ?>
    <?php } ?>

    <?php if (isset($_GET['error'])) { ?>
        <?php if ($_GET['error'] === 'has_booking') { ?>
            <div class="error-box">✕ Peralatan tidak dapat dihapus karena masih memiliki riwayat transaksi penyewaan.</div>
        <?php } elseif ($_GET['error'] === 'not_found') { ?>
            <div class="error-box">✕ Peralatan tidak ditemukan.</div>
        <?php } elseif ($_GET['error'] === 'delete_failed') { ?>
            <div class="error-box">✕ Gagal menghapus peralatan dari database.</div>
        <?php } ?>
    <?php } ?>

    <?php if (empty($peralatan)) { ?>
        <div class="empty-state">
            <p>Belum ada data peralatan terdaftar di database.</p>
            <a href="tambah_peralatan.php" class="btn btn-primary">Tambah Sekarang</a>
        </div>
    <?php } else { ?>
        <div class="table-responsive mt-3">
            <table border="1" cellpadding="10" cellspacing="0" class="table-custom">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Foto</th>
                        <th>Nama Peralatan</th>
                        <th>Kategori</th>
                        <th>Harga Sewa</th>
                        <th>Sisa Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($peralatan as $item) { ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td>
                                <?php if (!empty($item['gambar'])) { ?>
                                    <img
                                        src="../assets/uploads/<?= htmlspecialchars($item['gambar']); ?>"
                                        alt="<?= htmlspecialchars($item['nama']); ?>"
                                        width="70"
                                        style="border-radius: 6px; object-fit: cover; height: 50px;"
                                        onerror="this.src='../assets/images/placeholder.jpg';">
                                <?php } else { ?>
                                    <span class="text-muted">Tanpa Foto</span>
                                <?php } ?>
                            </td>
                            <td><strong><?= htmlspecialchars($item['nama']); ?></strong></td>
                            <td><span class="badge-kategori"><?= htmlspecialchars($item['kategori']); ?></span></td>
                            <td><?= rupiah($item['harga']); ?> / hari</td>
                            <td><strong><?= $item['stok']; ?> unit</strong></td>
                            <td>
                                <?php if ($item['stok'] > 0) { ?>
                                    <span class="badge badge-success">Tersedia</span>
                                <?php } else { ?>
                                    <span class="badge badge-danger">Habis</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="edit_peralatan.php?id=<?= $item['id']; ?>" class="btn btn-sm btn-primary">Ubah</a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="konfirmasiHapus('hapus_peralatan.php?id=<?= $item['id']; ?>', 'Hapus Peralatan Ini?', 'Peralatan <?= htmlspecialchars(addslashes($item['nama'])); ?> akan dihapus permanen dari sistem.')">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>

    <br>
    <a href="index.php" class="btn btn-secondary">← Kembali ke Dasbor Admin</a>
</section>

<?php

include '../includes/footer.php';

?>