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

    <!-- Form Filter Interaktif Vue dengan Live Autocomplete Suggestion -->
    <div class="filter-bar">
        <div class="search-input-wrapper">
            <span class="search-input-icon">🔍</span>
            <input
                type="text"
                v-model="searchKeyword"
                @focus="isFocused = true"
                @blur="handleBlur"
                @keydown.esc="isFocused = false"
                placeholder="Ketik nama alat (misal: jaket, tenda, matras)..."
                autocomplete="off">

            <!-- Dropdown Live Suggestion / Autocomplete -->
            <div class="search-suggestions-dropdown" v-if="isFocused && matchingSuggestions.length > 0">
                <div class="suggestion-header">
                    <span>💡 Saran Cepat ({{ matchingSuggestions.length }})</span>
                    <small>Klik untuk melihat rincian</small>
                </div>
                <a
                    v-for="item in matchingSuggestions"
                    :key="item.id"
                    :href="'pages/detail.php?id=' + item.id"
                    class="suggestion-item">
                    <img
                        :src="'assets/uploads/' + (item.gambar || 'default.jpg')"
                        :alt="item.nama"
                        onerror="this.src='assets/images/placeholder.jpg';">
                    <div class="suggestion-info">
                        <span class="suggestion-name">{{ item.nama }}</span>
                        <span class="suggestion-meta">{{ item.kategori }} • <strong>{{ formatRupiah(item.harga) }}</strong>/hari</span>
                    </div>
                    <span class="suggestion-badge" :class="item.stok > 0 ? 'badge-in-stock' : 'badge-out-stock'">
                        {{ item.stok > 0 ? item.stok + ' unit' : 'Habis' }}
                    </span>
                </a>
            </div>
        </div>

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
            Atur Ulang
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
                :alt="item.nama"
                onerror="this.src='assets/images/placeholder.jpg';">

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
            const isFocused = ref(false);
            const peralatanList = ref(<?= json_encode($peralatanAll, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);

            // Filter real-time produk grid
            const filteredPeralatan = computed(() => {
                const keyword = searchKeyword.value.trim().toLowerCase();
                return peralatanList.value.filter(item => {
                    const matchNama = item.nama.toLowerCase().includes(keyword);
                    const matchKategori = selectedKategori.value === '' || item.kategori === selectedKategori.value;
                    return matchNama && matchKategori;
                });
            });

            // Live autocomplete suggestions (maksimal 6 item relevan saat mengetik minimal 1 huruf)
            const matchingSuggestions = computed(() => {
                const keyword = searchKeyword.value.trim().toLowerCase();
                if (keyword.length === 0) return [];
                return peralatanList.value.filter(item => {
                    const matchNama = item.nama.toLowerCase().includes(keyword);
                    const matchKategori = item.kategori.toLowerCase().includes(keyword);
                    return matchNama || matchKategori;
                }).slice(0, 6);
            });

            const handleBlur = () => {
                // Beri delay singkat agar klik item di dropdown sempat tereksekusi
                setTimeout(() => {
                    isFocused.value = false;
                }, 200);
            };

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
                isFocused.value = false;
            };

            return {
                searchKeyword,
                selectedKategori,
                isFocused,
                filteredPeralatan,
                matchingSuggestions,
                handleBlur,
                formatRupiah,
                getStatusProduk,
                resetFilter
            };
        }
    }).mount('#app');
</script>