<?php

require_once 'config/config.php';
require_once 'config/functions.php';

// Ambil semua data peralatan dan daftar kategori dari database
$peralatanAll = getPeralatan($conn) ?? [];
$kategoriList = getKategoriList($conn) ?? [];

// State awal pencarian
$initialKeyword = trim($_GET['search'] ?? '');
$initialKategori = trim($_GET['kategori'] ?? '');

include 'includes/header.php';
?>

<section class="hero">
    <div class="hero-text">
        <h1>Sewa Peralatan Outdoor Berkualitas</h1>
        <p>
            Penyewaan tenda, carrier, kantong tidur, kompor gunung, dan perlengkapan mendaki dengan mudah, aman, dan cepat.
        </p>
        <a href="pages/peralatan.php" class="btn btn-hero">⛺ Jelajahi Semua Peralatan &rarr;</a>
    </div>
</section>

<section class="produk">
    <h2>Peralatan Outdoor Unggulan</h2>

    <!-- Form Filter Interaktif Vue -->
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
            ✕ Atur Ulang
        </button>
    </div>

    <!-- Container Produk Reaktif Vue -->
    <div class="produk-container">
        <!-- Tampilan jika tidak ada hasil -->
        <div class="empty-state" v-if="filteredPeralatan.length === 0">
            <p>Tidak ada peralatan yang sesuai dengan kata kunci pencarian.</p>
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
                    Lihat Rincian
                </a>

                <a v-if="item.stok > 0" :href="'pages/booking.php?id=' + item.id" class="btn btn-booking">
                    Sewa Sekarang
                </a>
                <button v-else class="btn btn-disabled" disabled>
                    Stok Habis
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
            const searchKeyword = ref(<?= json_encode($initialKeyword); ?>);
            const selectedKategori = ref(<?= json_encode($initialKategori); ?>);
            const peralatanList = ref(<?= json_encode($peralatanAll, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);

            const filteredPeralatan = computed(() => {
                return peralatanList.value.filter(item => {
                    const matchNama = item.nama.toLowerCase().includes(searchKeyword.value.toLowerCase());
                    const matchKategori = selectedKategori.value === '' || item.kategori === selectedKategori.value;
                    return matchNama && matchKategori;
                });
            });

            const formatRupiah = (angka) => {
                return 'Rp ' + Number(angka).toLocaleString('id-ID');
            };

            const getStatusProduk = (stok) => {
                if (stok <= 0) return 'Habis';
                if (stok <= 2) return 'Terbatas';
                return 'Tersedia';
            };

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