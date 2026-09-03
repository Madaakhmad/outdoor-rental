<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentDir = basename(dirname($_SERVER['PHP_SELF'] ?? ''));
$isAdmin = isset($_SESSION['login']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('APP_NAME') ? APP_NAME : 'Outdoor Rental'; ?></title>
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/style.css?v=<?= file_exists(__DIR__ . '/../assets/css/style.css') ? filemtime(__DIR__ . '/../assets/css/style.css') : time(); ?>">
    <!-- CDN Vue 3 -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>

<body>

    <!-- Pembungkus Utama Aplikasi Vue -->
    <div id="app">

        <nav>
            <h2>
                <a href="<?= BASE_URL; ?>/index.php" style="color:white; font-weight:bold;">
                    ⛺ <?= defined('APP_NAME') ? APP_NAME : 'Outdoor Rental'; ?>
                </a>
            </h2>

            <ul>
                <?php if ($isAdmin) { ?>
                    <!-- Menu Khusus Admin -->
                    <li><a href="<?= BASE_URL; ?>/admin/index.php" class="<?= ($currentPage === 'index.php' && $currentDir === 'admin') ? 'nav-active' : ''; ?>">Dashboard</a></li>
                    <li><a href="<?= BASE_URL; ?>/admin/peralatan.php" class="<?= in_array($currentPage, ['peralatan.php', 'tambah_peralatan.php', 'edit_peralatan.php']) && $currentDir === 'admin' ? 'nav-active' : ''; ?>">Kelola Peralatan</a></li>
                    <li><a href="<?= BASE_URL; ?>/admin/booking.php" class="<?= in_array($currentPage, ['booking.php', 'detail_booking.php']) && $currentDir === 'admin' ? 'nav-active' : ''; ?>">Data Booking</a></li>
                    <li><a href="<?= BASE_URL; ?>/admin/laporan.php" class="<?= ($currentPage === 'laporan.php') ? 'nav-active' : ''; ?>">Laporan Omzet</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/logout.php" class="btn-nav-logout">Logout (Admin)</a></li>
                <?php } elseif (isset($_SESSION['login'])) { ?>
                    <!-- Menu Khusus Customer -->
                    <li><a href="<?= BASE_URL; ?>/index.php" class="<?= ($currentPage === 'index.php' && $currentDir !== 'admin') ? 'nav-active' : ''; ?>">Home</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/peralatan.php" class="<?= in_array($currentPage, ['peralatan.php', 'detail.php']) ? 'nav-active' : ''; ?>">Katalog Peralatan</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/riwayat.php" class="<?= in_array($currentPage, ['riwayat.php', 'booking.php']) ? 'nav-active' : ''; ?>">Riwayat Booking</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/logout.php" class="btn-nav-logout">Logout (<?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?>)</a></li>
                <?php } else { ?>
                    <!-- Menu Publik / Tamu -->
                    <li><a href="<?= BASE_URL; ?>/index.php" class="<?= ($currentPage === 'index.php') ? 'nav-active' : ''; ?>">Home</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/peralatan.php" class="<?= in_array($currentPage, ['peralatan.php', 'detail.php']) ? 'nav-active' : ''; ?>">Peralatan</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/login.php" class="btn-nav-login <?= ($currentPage === 'login.php') ? 'nav-active' : ''; ?>">Login</a></li>
                    <li><a href="<?= BASE_URL; ?>/pages/register.php" class="<?= ($currentPage === 'register.php') ? 'nav-active' : ''; ?>">Daftar</a></li>
                <?php } ?>
            </ul>
        </nav>