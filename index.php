<?php

// hallo aku chandra hehehe
// awokawoakwosakw
// testing 123

require_once 'config/config.php';
require_once 'config/functions.php';

// Ambil semua data peralatan dan list kategori dari database PHP
$peralatanAll = getPeralatan($conn) ?? [];
$kategoriList = getKategoriList($conn) ?? [];

// Jika query param URL ada (misal dari redirect), ambil untuk state awal Vue
$initialKeyword = trim($_GET['search'] ?? '');
$initialKategori = trim($_GET['kategori'] ?? '');

include 'includes/header.php';

?>

<section class="hero">
    <div class="hero-text">
        <h1>Sewa Peralatan Outdoor Berkualitas</h1>
        <p>
            Booking tenda, carrier, sleeping bag, kompor camping, dan perlengkapan outdoor dengan mudah dan cepat.
        </p>
        <a href="pages/peralatan.php" class="btn btn-hero">⛺ Jelajahi Semua Peralatan &rarr;</a>
    </div>
</section>

<section class="produk">
    <h2>Peralatan Outdoor Unggulan</h2>

    <!-- Form Filter Interaktif Vue (Tanpa reload) -->
    <div class="filter-bar">
        <input
            type="text"
            v-model="searchKeyword"
            placeholder="Cari alat outdoor (tenda, carrier, sepatu)...">

        <select v-model="selectedKategori">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $kat) { ?>
                <option value="<?= htmlspecialchars($kat); ?>">
                    <?= htmlspecialchars($kat); ?>
                </option>
            <?php } ?>
        </select>

        <button
            type="button"
            class="btn btn-reset"
            v-if="searchKeyword !== '' || selectedKategori !== ''"
            @click="resetFilter">
            ✕ Reset
        </button>
    </div>

    <!-- Container Produk Reaktif Vue -->
    <div class="produk-container">
        <!-- Tampilan jika tidak ada hasil -->
        <div class="empty-state" v-if="filteredPeralatan.length === 0">
            <p>Tidak ada peralatan yang sesuai dengan pencarian.</p>
        </div>

        <!-- Loop Card Peralatan -->
        <div class="card" v-for="item in filteredPeralatan" :key="item.id">
            <img
                :src="'assets/uploads/' + (item.gambar || 'default.jpg')"
                :alt="item.nama">

            <span class="badge-kategori">{{ item.kategori }}</span>

            <h3>{{ item.nama }}</h3>

            <p class="harga-label">
                <strong>{{ formatRupiah(item.harga) }}</strong> / hari
            </p>

            <p>
                Stok: <strong>{{ item.stok }} unit</strong> ({{ getStatusProduk(item.stok) }})
            </p>

            <div class="card-action">
                <a :href="'pages/detail.php?id=' + item.id" class="btn btn-detail">
                    Lihat Detail
                </a>

                <a v-if="item.stok > 0" :href="'pages/booking.php?id=' + item.id" class="btn btn-booking">
                    Booking
                </a>
                <button v-else class="btn btn-disabled" disabled>
                    Habis
                </button>
            </div>
        </div>
    </div>
</section>

<?php

include 'includes/footer.php';

?>

<!-- Inisialisasi Script Vue 3 -->
<script>
    const {
        createApp,
        ref,
        computed
    } = Vue;

    createApp({
        setup() {
            // State pencarian & filter
            const searchKeyword = ref(<?= json_encode($initialKeyword); ?>);
            const selectedKategori = ref(<?= json_encode($initialKategori); ?>);

            // Oper seluruh array data dari PHP ke JavaScript
            const peralatanList = ref(<?= json_encode($peralatanAll, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);

            // Realtime Filtering menggunakan computed property
            const filteredPeralatan = computed(() => {
                return peralatanList.value.filter(item => {
                    const matchNama = item.nama.toLowerCase().includes(searchKeyword.value.toLowerCase());
                    const matchKategori = selectedKategori.value === '' || item.kategori === selectedKategori.value;
                    return matchNama && matchKategori;
                });
            });

            // Helper format Rupiah sederhana
            const formatRupiah = (angka) => {
                return 'Rp ' + Number(angka).toLocaleString('id-ID');
            };

            // Helper penentuan status stok
            const getStatusProduk = (stok) => {
                if (stok <= 0) return 'Habis';
                if (stok <= 2) return 'Terbatas';
                return 'Tersedia';
            };

            // Reset Filter
            const resetFilter = () => {
                searchKeyword.value = '';
                selectedKategori.value = '';
            };

            return {
                searchKeyword,
                selectedKategori,
                filteredPeralatan,
                formatRupiah,
                getStatusProduk,
                resetFilter
            };
        }
    }).mount('#app');
</script>