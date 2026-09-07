# ⛺ Outdoor Rental - Setup & Development Guide

Panduan ringkas untuk setup dan menjalankan environment proyek **Outdoor Rental** (Backend: PHP Native + MySQL, Frontend: Vue 3 + Tailwind CSS + Vite).

---

## 📋 Prasyarat Sistem

* **PHP** 8.0+
* **MySQL / MariaDB**
* **Node.js** 18.x / 20.x+ & **npm**

---

## ⚙️ 1. Setup Database & Backend

1. Buat database baru di MySQL dengan nama:
   ```sql
   CREATE DATABASE outdoor_rental;
   ```
2. Import file database di root:
   ```text
   schema.sql
   ```
3. Sesuaikan konfigurasi koneksi di `config/config.php`:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "outdoor_rental";
   ```

---

## 📦 2. Setup Frontend (Vue 3)

Buka terminal dan jalankan instalasi dependensi di folder `frontend`:

```bash
cd frontend
npm install
```

> **Catatan Dependensi Utama yang Terpasang:**
> * `vue` & `vue-router` : UI & navigasi rute SPA
> * `tailwindcss` & `@tailwindcss/vite` : Styling utility CSS (v4)
> * `@unhead/vue` : Pengelolaan `<title>` dan meta tag halaman dinamis via `<Head>`

---

## 🚀 3. Menjalankan Aplikasi (Development)

Jalankan 2 terminal secara terpisah:

### Terminal 1 — Backend PHP
Jalankan dari **root folder** (`outdoor-rental/`):
```bash
php -S localhost:8080
```
> Melayani REST API di `http://localhost:8080`

### Terminal 2 — Frontend Vue
Jalankan dari **folder `frontend/`**:
```bash
cd frontend
npm run dev
```
> Akses aplikasi di browser: **`http://localhost:5173`**

---

## 📂 4. Struktur Folder Singkat (`frontend/src`)

```text
frontend/src/
├── App.vue           # Root wrapper (<router-view />)
├── main.js           # Inisialisasi Vue, Vue Router, dan Unhead
├── router.js         # Pendaftaran endpoint rute & navigasi
├── style.css         # Import Tailwind CSS (@import "tailwindcss";)
├── layouts/          # Layout template wrapper (MainLayout.vue)
├── pages/            # Komponen halaman (Home.vue, Halaman.vue, dll)
└── components/       # Komponen kecil reusable
```

---

## 🛠️ 5. Build Production

Untuk mengompilasi frontend menjadi aset statis siap deploy:

```bash
cd frontend
npm run build
```
*Hasil build tersimpan di `frontend/dist/`.*
