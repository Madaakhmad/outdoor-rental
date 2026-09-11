<?php

require_once '../config/config.php';
require_once '../config/functions.php';

$keyword = trim($_GET['search'] ?? '');
$kategoriPilihan = trim($_GET['kategori'] ?? '');

$peralatan = getPeralatanFiltered($conn, $keyword, $kategoriPilihan);
$kategoriList = getKategoriList($conn);

include '../includes/header.php';

?>

<section class="produk">
    <h2>Semua Katalog Peralatan Outdoor</h2>

    <!-- Form Pencarian & Filter Kategori Modern -->
    <form method="GET" action="peralatan.php" class="filter-bar">
        <input 
            type="text" 
            name="search" 
            placeholder="Cari alat outdoor (tenda, carrier, sepatu)..." 
            value="<?= htmlspecialchars($keyword); ?>">

        <select name="kategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat) { ?>
                <option value="<?= htmlspecialchars($kat); ?>" <?= ($kategoriPilihan === $kat) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($kat); ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit" class="btn-search">🔍 Cari Alat</button>
        <?php if ($keyword !== '' || $kategoriPilihan !== '') { ?>
            <a href="peralatan.php" class="btn btn-reset">✕ Atur Ulang</a>
        <?php } ?>
    </form>

    <div class="produk-container">
        <?php if (empty($peralatan)) { ?>
            <div class="empty-state">
                <p>Peralatan outdoor tidak ditemukan. Silakan gunakan kata kunci atau kategori lain.</p>
            </div>
        <?php } else { ?>
            <?php foreach ($peralatan as $item) { ?>
                <div class="card">
                    <img 
                        src="../assets/uploads/<?= htmlspecialchars($item['gambar'] ?: 'default.jpg'); ?>" 
                        alt="<?= htmlspecialchars($item['nama']); ?>"
                        onerror="this.src='../assets/images/placeholder.jpg';">

                    <span class="badge-kategori"><?= htmlspecialchars($item['kategori']); ?></span>

                    <h3><?= htmlspecialchars($item['nama']); ?></h3>

                    <p class="harga-label">
                        <strong><?= rupiah($item['harga']); ?></strong> / hari
                    </p>

                    <p>
                        Stok: <strong><?= $item['stok']; ?> unit</strong> (<?= statusProduk($item['stok']); ?>)
                    </p>

                    <div class="card-action">
                        <a href="detail.php?id=<?= $item['id']; ?>" class="btn btn-detail">
                            Lihat Rincian
                        </a>
                        <?php if ($item['stok'] > 0) { ?>
                            <a href="booking.php?id=<?= $item['id']; ?>" class="btn btn-booking">
                                Sewa
                            </a>
                        <?php } else { ?>
                            <button class="btn btn-disabled" disabled>Stok Habis</button>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</section>

<?php

include '../includes/footer.php';

?>