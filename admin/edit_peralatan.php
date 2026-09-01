<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// =====================================
// CEK ID
// =====================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: peralatan.php");
    exit;
}

$id = (int) $_GET['id'];

// =====================================
// AMBIL DATA PERALATAN
// =====================================
$stmt = mysqli_prepare($conn, "SELECT * FROM peralatan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$produk = mysqli_fetch_assoc($result);

if (!$produk) {
    die("Peralatan tidak ditemukan.");
}

$errors = [];

// =====================================
// PROSES UPDATE
// =====================================
if (isset($_POST['update'])) {
    $nama = trim($_POST['nama']);
    $harga = (int) $_POST['harga'];
    $kategori = trim($_POST['kategori']);
    $stok = (int) $_POST['stok'];

    if ($nama === '') {
        $errors[] = "Nama peralatan harus diisi.";
    }

    if ($harga <= 0) {
        $errors[] = "Harga sewa harus lebih dari 0.";
    }

    if ($kategori === '') {
        $errors[] = "Kategori harus diisi.";
    }

    if ($stok < 0) {
        $errors[] = "Stok tidak boleh kurang dari 0.";
    }

    $gambar = $produk['gambar'];
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] != 4) {
        $gambarUpload = uploadGambar('gambar', '../assets/uploads/');
        if ($gambarUpload !== false) {
            $gambar = $gambarUpload;
        } else {
            $errors[] = "Format atau ukuran gambar tidak valid (Maks 3 MB, format JPG/PNG/WEBP).";
        }
    }

    if (empty($errors)) {
        $dataUpdate = [
            'nama' => $nama,
            'kategori' => $kategori,
            'harga' => $harga,
            'stok' => $stok,
            'gambar' => $gambar
        ];

        if (editPeralatan($conn, $id, $dataUpdate)) {
            header("Location: peralatan.php?success=edit");
            exit;
        } else {
            $errors[] = "Gagal memperbarui data peralatan.";
        }
    }
}

include '../includes/header.php';

?>

<section class="login">
    <h2>Edit Peralatan Outdoor</h2>

    <?php if (!empty($errors)) { ?>
        <div class="error-box">
            <ul>
                <?php foreach ($errors as $error) { ?>
                    <li><?= htmlspecialchars($error); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Nama Peralatan</label>
        <input
            type="text"
            name="nama"
            value="<?= htmlspecialchars($produk['nama']); ?>"
            required>

        <label>Kategori</label>
        <input
            type="text"
            name="kategori"
            value="<?= htmlspecialchars($produk['kategori']); ?>"
            required>

        <label>Harga per Hari (Rp)</label>
        <input
            type="number"
            name="harga"
            min="1"
            value="<?= htmlspecialchars($produk['harga']); ?>"
            required>

        <label>Stok Unit</label>
        <input
            type="number"
            name="stok"
            min="0"
            value="<?= htmlspecialchars($produk['stok']); ?>"
            required>

        <label>Gambar Saat Ini</label>
        <div style="margin: 10px 0;">
            <?php if (!empty($produk['gambar'])) { ?>
                <img
                    src="../assets/uploads/<?= htmlspecialchars($produk['gambar']); ?>"
                    alt="<?= htmlspecialchars($produk['nama']); ?>"
                    width="140"
                    style="border-radius: 8px; border: 1px solid #ddd;">
            <?php } else { ?>
                <p>Tidak ada gambar.</p>
            <?php } ?>
        </div>

        <label>Ganti Gambar (Opsional)</label>
        <input
            type="file"
            name="gambar"
            accept=".jpg,.jpeg,.png,.webp">
        <small>Kosongkan jika tidak ingin mengganti gambar produk.</small>

        <br><br>
        <button
            type="submit"
            name="update"
            class="btn btn-primary btn-block">
            Simpan Perubahan
        </button>
    </form>

    <br>
    <a href="peralatan.php">← Kembali ke Kelola Peralatan</a>
</section>

<?php

include '../includes/footer.php';

?>