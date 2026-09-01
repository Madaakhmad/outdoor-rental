<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

$errors = [];

if (isset($_POST['tambah'])) {
    $nama = trim($_POST['nama']);
    $kategori = trim($_POST['kategori']);
    $harga = (int) $_POST['harga'];
    $stok = (int) $_POST['stok'];

    if ($nama === '') {
        $errors[] = "Nama peralatan harus diisi.";
    }

    if ($kategori === '') {
        $errors[] = "Kategori harus diisi.";
    }

    if ($harga <= 0) {
        $errors[] = "Harga sewa harus lebih dari 0.";
    }

    if ($stok < 0) {
        $errors[] = "Stok tidak boleh kurang dari 0.";
    }

    $gambar = uploadGambar('gambar', '../assets/uploads/');
    if ($gambar === false) {
        $errors[] = "Gambar wajib diunggah (Format: JPG, PNG, WEBP, Maks: 3 MB).";
    }

    if (empty($errors)) {
        $data = [
            'nama' => $nama,
            'kategori' => $kategori,
            'harga' => $harga,
            'stok' => $stok,
            'gambar' => $gambar
        ];

        if (tambahPeralatan($conn, $data)) {
            header("Location: peralatan.php?success=add");
            exit;
        } else {
            $errors[] = "Peralatan gagal ditambahkan ke database.";
        }
    }
}

include '../includes/header.php';

?>

<section class="login">
    <h2>Tambah Peralatan Baru</h2>
    <p class="text-muted">Masukkan informasi peralatan outdoor yang ingin ditambahkan ke katalog inventaris.</p>

    <?php if (!empty($errors)) { ?>
        <div class="error-box mt-3">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data" class="mt-3">
        <label>Nama Peralatan</label>
        <input
            type="text"
            name="nama"
            placeholder="Contoh: Tenda Dome Kapasitas 4 Orang"
            value="<?= htmlspecialchars($_POST['nama'] ?? ''); ?>"
            required>

        <label>Kategori</label>
        <input
            type="text"
            name="kategori"
            placeholder="Contoh: Tenda, Carrier, Alat Masak, Penerangan"
            value="<?= htmlspecialchars($_POST['kategori'] ?? ''); ?>"
            required>

        <div class="form-grid">
            <div>
                <label>Harga per Hari (Rp)</label>
                <input
                    type="number"
                    name="harga"
                    placeholder="50000"
                    value="<?= htmlspecialchars($_POST['harga'] ?? ''); ?>"
                    min="1"
                    required>
            </div>
            <div>
                <label>Jumlah Stok Unit</label>
                <input
                    type="number"
                    name="stok"
                    placeholder="5"
                    value="<?= htmlspecialchars($_POST['stok'] ?? '1'); ?>"
                    min="0"
                    required>
            </div>
        </div>

        <label>Foto Produk</label>
        <input
            type="file"
            name="gambar"
            accept=".jpg,.jpeg,.png,.webp"
            required>
        <small class="text-muted">Format yang didukung: JPG, PNG, WEBP. Maksimal 3 MB.</small>

        <div class="mt-4" style="display:flex; gap:10px;">
            <button
                type="submit"
                name="tambah"
                class="btn btn-primary"
                style="flex: 2;">
                + Simpan Peralatan
            </button>
            <a href="peralatan.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
                Batal
            </a>
        </div>
    </form>
</section>

<?php

include '../includes/footer.php';

?>