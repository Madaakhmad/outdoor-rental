<?php

require_once '../config/config.php';
require_once '../config/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = mysqli_prepare($conn, "SELECT * FROM peralatan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

include '../includes/header.php';

?>

<section class="detail-section">
    <div class="breadcrumb">
        <a href="../index.php">Beranda</a> <span>›</span> 
        <a href="peralatan.php">Katalog Peralatan</a> <span>›</span> 
        <strong><?= $produk ? htmlspecialchars($produk['nama']) : 'Rincian Alat'; ?></strong>
    </div>

    <?php if (!$produk) { ?>
        <div class="empty-state mt-4">
            <h2>Peralatan Tidak Ditemukan</h2>
            <p>Peralatan outdoor yang Anda cari mungkin sudah dihapus atau tidak tersedia.</p>
            <br>
            <a href="peralatan.php" class="btn btn-katalog">← Kembali ke Katalog</a>
        </div>
    <?php } else { ?>
        <div class="detail-container mt-3">
            <!-- Kolom Kiri: Foto Produk -->
            <div class="detail-image-card">
                <img
                    src="../assets/uploads/<?= htmlspecialchars($produk['gambar'] ?: 'default.jpg'); ?>"
                    alt="<?= htmlspecialchars($produk['nama']); ?>"
                    onerror="this.src='../assets/images/placeholder.jpg';">
                <div class="image-badge-container">
                    <span class="badge-kategori"><?= htmlspecialchars($produk['kategori']); ?></span>
                    <?php if ($produk['stok'] > 0) { ?>
                        <span class="badge badge-success">✓ Stok Tersedia (<?= $produk['stok']; ?> unit)</span>
                    <?php } else { ?>
                        <span class="badge badge-danger">✕ Stok Habis</span>
                    <?php } ?>
                </div>
            </div>

            <!-- Kolom Kanan: Detail & CTA -->
            <div class="detail-info-card">
                <h1 class="detail-title"><?= htmlspecialchars($produk['nama']); ?></h1>
                
                <div class="detail-price-box">
                    <span class="price-subtitle">Biaya Sewa:</span>
                    <h2 class="price-value"><?= rupiah($produk['harga']); ?> <small>/ hari</small></h2>
                </div>

                <div class="detail-specs">
                    <div class="spec-item">
                        <span class="spec-label">Kategori Alat</span>
                        <span class="spec-val"><?= htmlspecialchars($produk['kategori']); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Sisa Kuota Stok</span>
                        <span class="spec-val"><strong><?= $produk['stok']; ?> unit</strong> tersedia</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Kondisi Peralatan</span>
                        <span class="spec-val">Bersih, terawat & siap pakai</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Jaminan Sewa</span>
                        <span class="spec-val">KTP / SIM / Kartu Identitas Asli</span>
                    </div>
                </div>

                <div class="detail-actions mt-4">
                    <?php if ($produk['stok'] > 0) { ?>
                        <a href="booking.php?id=<?= $produk['id']; ?>" class="btn btn-primary btn-cta">
                            ⛺ Sewa Sekarang
                        </a>
                    <?php } else { ?>
                        <button class="btn btn-disabled btn-cta" disabled>
                            Maaf, Stok Sedang Habis
                        </button>
                    <?php } ?>

                    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Mada%20Adventure,%20saya%20tertarik%20menyewa%20<?= urlencode($produk['nama']); ?>" target="_blank" class="btn btn-whatsapp">
                        💬 Tanya via WhatsApp
                    </a>
                    
                    <a href="peralatan.php" class="btn btn-katalog">
                        ← Kembali ke Katalog
                    </a>
                </div>

                <div class="feature-bullets mt-4">
                    <div class="bullet-item">
                        <span>🛡️</span>
                        <div>
                            <strong>Peralatan Bersih & Teruji</strong>
                            <p>Selalu dibersihkan dan dicek kelengkapannya sebelum diserahkan.</p>
                        </div>
                    </div>
                    <div class="bullet-item">
                        <span>⚡</span>
                        <div>
                            <strong>Pemesanan Cepat & Mudah</strong>
                            <p>Proses pemesanan daring instan dengan bukti nota digital resmi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</section>

<?php

include '../includes/footer.php';

?>