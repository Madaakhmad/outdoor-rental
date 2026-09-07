# 🚀 Panduan Migrasi: Full Vue SPA di Root (PHP Murni sebagai REST API)

Dokumen ini adalah panduan langkah demi langkah saat Anda siap beralih sepenuhnya ke **Frontend Full Vue di Root Domain (`/`)** dan menjadikan **PHP murni sebagai Backend REST API (`/api/`)**.

---

## 🎯 Target Akhir
* **Domain Utama (`https://domain.com/`)**: Langsung membuka aplikasi Vue SPA.
* **URL Bersih**: Mengakses `/home`, `/peralatan`, `/booking`, `/login` tanpa embel-embel prefix `/vue/`.
* **Backend PHP**: Hanya melayani request data JSON melalui endpoint `/api/...` (misal: `/api/peralatan.php`).

---

## 📝 Langkah-Langkah yang Perlu Diubah

### 1. Ubah Base Path di `frontend/vite.config.js`
Hapus `base: '/vue/'` atau ganti menjadi `base: '/'`:

```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  base: '/', // <-- Ubah kembali ke root '/'
  plugins: [
    vue(),
    tailwindcss()
  ],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true
      }
    }
  }
})
```

---

### 2. Konfigurasi `frontend/src/router.js`
Pastikan router menggunakan `createWebHistory(import.meta.env.BASE_URL)` atau `createWebHistory()`:

```javascript
import { createRouter, createWebHistory } from 'vue-router'
import Home from './pages/Home.vue'
import Peralatan from './pages/Peralatan.vue'

const routes = [
  {
    path: '/',
    redirect: '/home'
  },
  {
    path: '/home',
    name: 'home',
    component: Home,
    meta: { title: 'Beranda' }
  },
  {
    path: '/peralatan',
    name: 'peralatan',
    component: Peralatan,
    meta: { title: 'Katalog Peralatan' }
  }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL), // Otomatis membaca base '/' dari Vite
  routes
})

export default router
```

---

### 3. Update File `frontend/public/.htaccess`
Ganti isi file `.htaccess` agar Apache meneruskan rute SPA di root, **sambil tetap mengizinkan request ke folder `/api/` dan file gambar**:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /

  # 1. Jangan redirect request yang menuju ke folder API backend atau asset upload
  RewriteRule ^(api|assets|config) - [L]

  # 2. Jangan redirect jika file atau folder fisik memang benar-benar ada
  RewriteCond %{REQUEST_FILENAME} -f [OR]
  RewriteCond %{REQUEST_FILENAME} -d
  RewriteRule ^ - [L]

  # 3. Teruskan seluruh rute SPA lainnya ke index.html di root
  RewriteRule ^ index.html [L]
</IfModule>
```

---

### 4. Update Pipeline Deployment di `.github/workflows/deploy.yaml`
Sesuaikan workflow deployment agar hasil build `frontend/dist` disalin langsung ke root server:

```yaml
      - name: 📦 Install & Build Vue Frontend
        run: |
          cd frontend
          npm ci
          npm run build
          cd ..
          # Salin seluruh isi dist ke root sebelum sync FTP
          cp -r frontend/dist/* .
          cp frontend/public/.htaccess .htaccess

      - name: 🚀 Sync Files via FTP
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.FTP_SERVER }}
          username: ${{ secrets.FTP_USERNAME }}
          password: ${{ secrets.FTP_PASSWORD }}
          server-dir: /htdocs/
          exclude: |
            **/.git*
            **/.git*/**
            **/node_modules/**
            .github/**
            frontend/src/**
            frontend/public/**
            frontend/*.json
            frontend/*.js
```

---

### 5. Struktur Folder Produksi di Server Hosting (`/htdocs/`)

Setelah migrasi penuh, struktur di server hosting akan menjadi seperti ini:

```text
/htdocs/
├── index.html        # Entrypoint Vue SPA hasil build
├── assets/           # Bundled JS & CSS Vue + Gambar Uploads
├── .htaccess         # Routing fallback Apache
├── api/              # Kumpulan Endpoint REST API (PHP)
│   ├── login.php
│   ├── peralatan.php
│   └── booking.php
├── config/           # config.php & functions.php
└── schema.sql
```

---

## 💡 Checklist Sebelum Beralih
- [ ] Semua halaman user & admin lama sudah dibuatkan komponen Vue-nya di `frontend/src/pages/`.
- [ ] Semua query database di PHP lama sudah dikonversi menjadi endpoint JSON di folder `api/`.
- [ ] Request formulir di Vue menggunakan `fetch()` / `axios` ke endpoint `/api/...`.

