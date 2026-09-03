<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vue di PHP Native</title>
    <!-- 1. Load Vue 3 via CDN -->
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>

<body>

    <!-- 2. Buat container tempat Vue dirender -->
    <div id="app">
        <h1>{{ pesan }}</h1>
        <button @click="jumlah++">Diklik: {{ jumlah }} kali</button>
    </div>

    <!-- 3. Inisialisasi Script Vue -->
    <script>
        const {
            createApp,
            ref
        } = Vue;

        createApp({
            setup() {
                const pesan = ref('Halo dari Vue.js di PHP Native!');
                const jumlah = ref(0);

                return {
                    pesan,
                    jumlah
                };
            }
        }).mount('#app');
    </script>

</body>

</html>