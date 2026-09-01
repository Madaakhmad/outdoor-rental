<?php

require_once '../config/config.php';
require_once '../config/functions.php';

cekAdmin();

// Filter Tanggal & Status sesuai dengan laporan.php
$tglAwal = $_GET['tgl_awal'] ?? date('Y-m-01');
$tglAkhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$statusFilter = $_GET['status'] ?? '';
$bayarFilter = $_GET['status_bayar'] ?? '';

$sql = "SELECT 
            booking.*, 
            peralatan.nama AS nama_peralatan,
            peralatan.kategori AS kategori_peralatan,
            peralatan.harga AS harga_satuan,
            users.username
        FROM booking
        JOIN peralatan ON booking.id_peralatan = peralatan.id
        JOIN users ON booking.id_user = users.id
        WHERE DATE(booking.tanggal_pinjam) BETWEEN ? AND ?";

$params = [$tglAwal, $tglAkhir];
$types = "ss";

if (!empty($statusFilter)) {
    $sql .= " AND booking.status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

if (!empty($bayarFilter)) {
    $sql .= " AND booking.status_pembayaran = ?";
    $params[] = $bayarFilter;
    $types .= "s";
}

$sql .= " ORDER BY booking.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

function columnToLetter(int $column): string
{
    $letter = '';
    while ($column > 0) {
        $mod = ($column - 1) % 26;
        $letter = chr(65 + $mod) . $letter;
        $column = intdiv($column - 1, 26);
    }
    return $letter;
}

function xlsxCellXml(int $columnIndex, int $rowIndex, $value): string
{
    $cellRef = columnToLetter($columnIndex + 1) . $rowIndex;
    $text = $value === null ? '' : (string) $value;
    $escaped = htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');

    return '<c r="' . $cellRef . '" t="inlineStr"><is><t xml:space="preserve">' . $escaped . '</t></is></c>';
}

function xlsxRowXml(array $row, int $rowIndex): string
{
    $cells = '';
    foreach ($row as $colIndex => $cellValue) {
        $cells .= xlsxCellXml((int) $colIndex, $rowIndex, $cellValue);
    }

    return '<row r="' . $rowIndex . '">' . $cells . '</row>';
}

function xlsxWorksheetXml(array $rows): string
{
    $sheetData = '';
    foreach ($rows as $index => $row) {
        $sheetData .= xlsxRowXml($row, $index + 1);
    }

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
    <worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
        <sheetData>' . $sheetData . '</sheetData>
    </worksheet>';
}

function buildXlsx(array $rows): string
{
    $zip = new ZipArchive();
    $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');

    if ($zip->open($tempFile, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
        throw new RuntimeException('Gagal membuat file Excel.');
    }

    $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML);

    $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML);

    $zip->addFromString('docProps/core.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:creator>Outdoor Rental</dc:creator>
  <cp:lastModifiedBy>Outdoor Rental</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">2026-01-01T00:00:00Z</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">2026-01-01T00:00:00Z</dcterms:modified>
</cp:coreProperties>
XML);

    $zip->addFromString('docProps/app.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Excel</Application>
</Properties>
XML);

    $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Laporan" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

    $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML);

    $zip->addFromString('xl/worksheets/sheet1.xml', xlsxWorksheetXml($rows));
    $zip->close();

    $content = file_get_contents($tempFile);
    unlink($tempFile);

    return $content;
}

// Set Header untuk Download Excel
$filename = "Laporan_Keuangan_Outdoor_Rental_" . str_replace('-', '', $tglAwal) . "_" . str_replace('-', '', $tglAkhir) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

// Matikan output warning/deprecation agar tidak tercampur ke file Excel
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

$rows = [[
    'No',
    'ID Booking',
    'Tanggal Transaksi (Dibuat)',
    'Nama Penyewa',
    'Username Akun',
    'No HP/WA',
    'Peralatan Disewa',
    'Kategori',
    'Jumlah Unit',
    'Tanggal Pinjam',
    'Tanggal Kembali',
    'Durasi Sewa (Hari)',
    'Biaya Sewa (Rp)',
    'Denda (Rp)',
    'Grand Total (Rp)',
    'Status Pembayaran',
    'Status Sewa'
]];

$no = 1;
$totalUnitDisewa = 0;
$totalOmzetSewa = 0;
$totalDenda = 0;
$grandTotalSemua = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $grand = (int) $row['total_harga'] + (int) $row['denda'];
    $totalUnitDisewa += (int) $row['jumlah'];
    $totalOmzetSewa += (int) $row['total_harga'];
    $totalDenda += (int) $row['denda'];
    $grandTotalSemua += $grand;

    $rows[] = [
        $no++,
        '#' . $row['id'],
        $row['created_at'],
        $row['nama_penyewa'],
        $row['username'],
        "'" . $row['no_hp'],
        $row['nama_peralatan'],
        $row['kategori_peralatan'],
        $row['jumlah'],
        $row['tanggal_pinjam'],
        $row['tanggal_kembali'],
        $row['lama_sewa'],
        $row['total_harga'],
        $row['denda'],
        $grand,
        $row['status_pembayaran'],
        $row['status']
    ];
}

$rows[] = ['','','','','','','TOTAL','','','','','','','','',''];
$rows[] = [
    '', '', '', '', '', '', '',
    $totalUnitDisewa,
    '', '', '',
    $totalOmzetSewa,
    $totalDenda,
    $grandTotalSemua,
    '', ''
];

echo buildXlsx($rows);
exit;
