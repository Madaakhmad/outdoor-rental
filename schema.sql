-- =======================================================
-- Database Schema: Outdoor Rental
-- Database Name: outdoor_rental
-- =======================================================

CREATE DATABASE IF NOT EXISTS `outdoor_rental` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `outdoor_rental`;

-- =======================================================
-- Table: users
-- Menyimpan data akun pengguna (Admin dan Customer)
-- =======================================================
DROP TABLE IF EXISTS `booking`;
DROP TABLE IF EXISTS `peralatan`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- Table: peralatan
-- Menyimpan katalog dan stok inventaris peralatan outdoor
-- =======================================================
CREATE TABLE `peralatan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `kategori` VARCHAR(50) NOT NULL,
    `harga` INT NOT NULL,
    `stok` INT NOT NULL DEFAULT 0,
    `gambar` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- Table: booking
-- Menyimpan transaksi penyewaan alat oleh customer
-- =======================================================
CREATE TABLE `booking` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `id_user` INT NOT NULL,
    `id_peralatan` INT NOT NULL,
    `nama_penyewa` VARCHAR(100) NOT NULL,
    `no_hp` VARCHAR(25) NOT NULL,
    `tanggal_pinjam` DATE NOT NULL,
    `tanggal_kembali` DATE NOT NULL,
    `lama_sewa` INT NOT NULL,
    `jumlah` INT NOT NULL DEFAULT 1,
    `total_harga` INT NOT NULL,
    `bukti_pembayaran` VARCHAR(255) NULL,
    `status_pembayaran` ENUM('Belum Bayar', 'Menunggu Verifikasi', 'Lunas') NOT NULL DEFAULT 'Belum Bayar',
    `denda` INT NOT NULL DEFAULT 0,
    `status` ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Dikembalikan') NOT NULL DEFAULT 'Menunggu',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_booking_user` FOREIGN KEY (`id_user`)
        REFERENCES `users` (`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT `fk_booking_peralatan` FOREIGN KEY (`id_peralatan`) 
        REFERENCES `peralatan` (`id`) 
        ON UPDATE CASCADE 
        ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =======================================================
-- SEED DATA (Data Awal)
-- =======================================================

-- 1. Akun Default
-- Password akun:
-- - admin   : admin123
-- - budi    : user123
INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '$2y$12$zMGrs/di5VsuPAbhlZH8xutq1c2Q8kJs8eSxCLlh/ARwlbDKceh0y', 'admin'),
(2, 'Budi Santoso', 'budi', '$2y$12$NBw7MfaSbz7by.KMOW8ZQODtBqxsf3cK4woYh0G70Y26w3oF3Mrce', 'customer');

-- 2. Data Katalog Peralatan
INSERT INTO `peralatan` (`id`, `nama`, `kategori`, `harga`, `stok`, `gambar`) VALUES
(1, 'Tenda Dome Kapasitas 4 Orang', 'Tenda', 50000, 5, 'tenda.jpg'),
(2, 'Carrier Consina 60L', 'Tas & Carrier', 45000, 4, 'tas carryr.jpg'),
(3, 'Kompor Portable Camping', 'Alat Masak', 20000, 7, 'kompor.jpg'),
(4, 'Headlamp LED Outdoor', 'Penerangan', 15000, 10, 'headlamp.jpg'),
(5, 'Sepatu Trail Running / Trekking', 'Sepatu & Sandal', 35000, 3, 'sepatu trail.jpg'),
(6, 'Trekking Pole Ultralight', 'Aksesoris', 15000, 6, 'trekking pole.jpg');

-- 3. Data Contoh Transaksi Booking
INSERT INTO `booking` (`id`, `id_user`, `id_peralatan`, `nama_penyewa`, `no_hp`, `tanggal_pinjam`, `tanggal_kembali`, `lama_sewa`, `jumlah`, `total_harga`, `bukti_pembayaran`, `status_pembayaran`, `denda`, `status`) VALUES
(1, 2, 1, 'Budi Santoso', '081234567890', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 3, 1, 150000, NULL, 'Menunggu Verifikasi', 0, 'Menunggu'),
(2, 2, 2, 'Budi Santoso', '081234567890', DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 3, 1, 135000, NULL, 'Lunas', 0, 'Dikembalikan');
