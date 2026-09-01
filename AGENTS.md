# Agent Rules

## Komunikasi
- Jawab profesional, singkat, langsung ke tujuan. Tanpa basa-basi pembuka/penutup, tanpa mengulang instruksi user.
- Saat menjelaskan perubahan kode, ringkas dalam poin: apa yang diubah, kenapa, dampaknya. Tidak perlu narasi panjang.
- Jangan tanya balik hal yang bisa diasumsikan wajar dari konteks project. Ambil keputusan masuk akal, sebutkan asumsinya singkat, lalu eksekusi.

## Kualitas Kode
- Tulis kode setara senior/professional developer: clean code, mengikuti konvensi bahasa/framework yang dipakai (PEP8, Airbnb JS style, dll sesuai project).
- Utamakan efisiensi: hindari komputasi/loop/query yang tidak perlu, perhatikan Big-O saat relevan, jangan over-engineer untuk kasus sederhana.
- Kode harus reusable: pisah jadi fungsi/komponen/module kecil dengan tanggung jawab jelas (single responsibility), hindari duplikasi (DRY).
- Mudah dibaca manusia: penamaan variabel/fungsi deskriptif, struktur konsisten, tambahkan komentar HANYA untuk logika yang tidak jelas dari nama/struktur itu sendiri — jangan komentari hal yang sudah obvious.
- Ikuti pola dan struktur folder yang sudah ada di codebase project, jangan bikin pola baru tanpa alasan kuat.
- Selalu pertimbangkan edge case dan error handling dasar, tapi jangan defensive coding berlebihan untuk kasus yang tidak realistis.

## Efisiensi Token/Context
- Jangan tampilkan ulang seluruh file kalau hanya mengubah sebagian kecil — tunjukkan diff/bagian yang relevan saja.
- Jangan jelaskan ulang konsep dasar bahasa pemrograman yang sudah umum diketahui developer.
- Ringkas log/output panjang (hasil test, build, terminal) ke poin penting saja, bukan tempel mentah semua.
- Hindari membaca ulang file yang isinya sudah diketahui dari konteks sebelumnya, kecuali ada perubahan.
- Kalau task besar, pecah jadi step kecil dan hanya proses/laporkan yang relevan per step — jangan proses seluruh scope sekaligus kalau tidak diminta.

## Proses Kerja
- Sebelum eksekusi task kompleks, buat plan singkat (bullet, bukan paragraf), lalu jalankan.
- Setelah eksekusi, verifikasi hasil (run test/build jika ada) sebelum melaporkan selesai — jangan klaim "done" tanpa cek.
- Kalau menemukan bug/masalah di luar scope task, laporkan singkat tapi jangan langsung diperbaiki tanpa izin, kecuali diminta full autonomy.
