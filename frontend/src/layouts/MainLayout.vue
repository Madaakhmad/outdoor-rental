<script setup>
import { RouterLink as Link } from 'vue-router'
</script>

<template>
  <nav>
    <div class="nav-brand">
      <a href="<?= BASE_URL; ?>/index.php" class="brand-link">
        <span class="brand-icon">⛺</span>
        <div class="brand-text">
          <span class="brand-name">Mada Adventure</span>
          <span class="brand-tagline">Outdoor Rental</span>
        </div>
      </a>
    </div>

    <ul>
      <!-- Menu Khusus Admin -->
      <li><a href="<?= BASE_URL; ?>/admin/index.php"
          class="<?= ($currentPage === 'index.php' && $currentDir === 'admin') ? 'nav-active' : ''; ?>">Dasbor</a>
      </li>
      <li><a href="<?= BASE_URL; ?>/admin/peralatan.php"
          class="<?= in_array($currentPage, ['peralatan.php', 'tambah_peralatan.php', 'edit_peralatan.php']) && $currentDir === 'admin' ? 'nav-active' : ''; ?>">Kelola
          Peralatan</a></li>
      <li><a href="<?= BASE_URL; ?>/admin/booking.php"
          class="<?= in_array($currentPage, ['booking.php', 'detail_booking.php']) && $currentDir === 'admin' ? 'nav-active' : ''; ?>">Data
          Penyewaan</a></li>
      <li><a href="<?= BASE_URL; ?>/admin/laporan.php"
          class="<?= ($currentPage === 'laporan.php') ? 'nav-active' : ''; ?>">Laporan Keuangan</a></li>
      <li><a href="<?= BASE_URL; ?>/pages/logout.php" class="btn-nav-logout">Keluar (Admin)</a></li>

      <!-- Menu Khusus Pelanggan -->
      <li><a href="<?= BASE_URL; ?>/index.php"
          class="<?= ($currentPage === 'index.php' && $currentDir !== 'admin') ? 'nav-active' : ''; ?>">Beranda</a>
      </li>
      <li><a href="<?= BASE_URL; ?>/pages/peralatan.php"
          class="<?= in_array($currentPage, ['peralatan.php', 'detail.php']) ? 'nav-active' : ''; ?>">Katalog
          Peralatan</a></li>
      <li><a href="<?= BASE_URL; ?>/pages/riwayat.php"
          class="<?= in_array($currentPage, ['riwayat.php', 'booking.php']) ? 'nav-active' : ''; ?>">Riwayat
          Sewa</a></li>
      <li><a href="<?= BASE_URL; ?>/pages/logout.php" class="btn-nav-logout">Keluar</a></li>

      <!-- Menu Publik / Tamu -->
      <li><a href="<?= BASE_URL; ?>/index.php"
          class="<?= ($currentPage === 'index.php') ? 'nav-active' : ''; ?>">Beranda</a></li>
      <li><a href="<?= BASE_URL; ?>/pages/peralatan.php"
          class="<?= in_array($currentPage, ['peralatan.php', 'detail.php']) ? 'nav-active' : ''; ?>">Katalog Peralatan</a>
      </li>
      <li><a href="<?= BASE_URL; ?>/pages/login.php"
          class="btn-nav-login <?= ($currentPage === 'login.php') ? 'nav-active' : ''; ?>">Masuk</a></li>
      <li><a href="<?= BASE_URL; ?>/pages/register.php"
          class="<?= ($currentPage === 'register.php') ? 'nav-active' : ''; ?>">Daftar Akun</a></li>
    </ul>
  </nav>

  <slot></slot>

  <footer>
    <p>&copy; {{ new Date().getFullYear() }} Mada Adventure (Outdoor Rental). Semua Hak Dilindungi.</p>
  </footer>
  <!-- Floating WhatsApp Widget -->
  <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Mada%20Adventure,%20saya%20ingin%20bertanya%20seputar%20sewa%20peralatan"
    target="_blank" class="floating-whatsapp no-print" title="Hubungi Admin via WhatsApp">
    <span>💬</span> Hubungi Admin WA
  </a>
</template>