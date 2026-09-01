<?php

function rupiah($harga)
{
    return "Rp " . number_format((float)$harga, 0, ',', '.');
}

function statusProduk($stok)
{
    if ($stok > 0) {
        return "Tersedia";
    }
    return "Habis";
}

function getPeralatan($conn)
{
    $query = mysqli_query($conn, "SELECT * FROM peralatan ORDER BY id DESC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function getPeralatanFiltered($conn, $keyword = '', $kategori = '')
{
    $sql = "SELECT * FROM peralatan WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($keyword)) {
        $sql .= " AND (nama LIKE ? OR kategori LIKE ?)";
        $keywordParam = "%" . $keyword . "%";
        $params[] = $keywordParam;
        $params[] = $keywordParam;
        $types .= "ss";
    }

    if (!empty($kategori)) {
        $sql .= " AND kategori = ?";
        $params[] = $kategori;
        $types .= "s";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function getKategoriList($conn)
{
    $query = mysqli_query($conn, "SELECT DISTINCT kategori FROM peralatan WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC");
    $kategori = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $kategori[] = $row['kategori'];
    }
    return $kategori;
}

function uploadGambar($inputName = 'gambar', $targetDir = '../assets/uploads/')
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] == 4) {
        return false;
    }

    $namaFile = $_FILES[$inputName]['name'];
    $ukuranFile = $_FILES[$inputName]['size'];
    $tmpName = $_FILES[$inputName]['tmp_name'];

    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
    $ekstensiFile = strtolower(pathinfo($namaFile, PATHINFO_EXTENSION));

    if (!in_array($ekstensiFile, $ekstensiValid)) {
        return false;
    }

    // Maksimal 3 MB
    if ($ukuranFile > 3 * 1024 * 1024) {
        return false;
    }

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $namaBaru = uniqid() . "." . $ekstensiFile;
    if (!move_uploaded_file($tmpName, rtrim($targetDir, '/') . '/' . $namaBaru)) {
        return false;
    }

    return $namaBaru;
}

function uploadBuktiPembayaran($inputName = 'bukti_pembayaran', $targetDir = '../assets/uploads/')
{
    return uploadGambar($inputName, $targetDir);
}

function tambahPeralatan($conn, $data)
{
    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO peralatan (nama, kategori, harga, stok, gambar) VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssdis",
        $data['nama'],
        $data['kategori'],
        $data['harga'],
        $data['stok'],
        $data['gambar']
    );

    return mysqli_stmt_execute($stmt);
}

function editPeralatan($conn, $id, $data)
{
    $queryLama = mysqli_query($conn, "SELECT gambar FROM peralatan WHERE id=" . (int)$id);
    $produkLama = mysqli_fetch_assoc($queryLama);

    if (!empty($data['gambar']) && $produkLama['gambar'] != $data['gambar']) {
        $file = "../assets/uploads/" . $produkLama['gambar'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE peralatan SET nama=?, kategori=?, harga=?, stok=?, gambar=? WHERE id=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssdisi",
        $data['nama'],
        $data['kategori'],
        $data['harga'],
        $data['stok'],
        $data['gambar'],
        $id
    );

    return mysqli_stmt_execute($stmt);
}

function hapusPeralatan($conn, $id)
{
    $id = (int)$id;
    $query = mysqli_query($conn, "SELECT gambar FROM peralatan WHERE id=$id");
    $produk = mysqli_fetch_assoc($query);

    if ($produk && !empty($produk['gambar'])) {
        $file = "../assets/uploads/" . $produk['gambar'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM peralatan WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    return mysqli_stmt_execute($stmt);
}

function cekAdmin()
{
    if (!isset($_SESSION['login'])) {
        header("Location: ../pages/login.php");
        exit;
    }

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit;
    }
}
