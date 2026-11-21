-- ===================================================
-- DATABASE SISTEM PENDAFTARAN ONLINE HIMATIK
-- Himpunan Mahasiswa Teknik Informatika
-- 3 Divisi: Pemrograman, Mikro, Jaringan
-- Status: Diterima / Tidak Diterima / Menunggu Pengumuman
-- Kompatibel dengan: MySQL 5.7+, MySQL 8.0+, MariaDB (XAMPP / Laragon)
-- ===================================================

CREATE DATABASE IF NOT EXISTS `himatik_db` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `himatik_db`;

-- ---------------------------------------------------
-- TABEL: pengaturan (Biaya Pendaftaran oleh Admin)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan` (
    `kunci` VARCHAR(50) PRIMARY KEY,
    `nilai` VARCHAR(255) NOT NULL,
    `keterangan` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pengaturan` (`kunci`, `nilai`, `keterangan`) VALUES
('biaya_pendaftaran', '0', 'Biaya pendaftaran dalam Rupiah (0 = Gratis)'),
('catatan_pembayaran', 'Pendaftaran tidak dipungut biaya (Gratis)', 'Keterangan tambahan biaya')
ON DUPLICATE KEY UPDATE `kunci`=`kunci`;

-- ---------------------------------------------------
-- TABEL: pendaftar
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `pendaftar` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nomor_registrasi` VARCHAR(30) NOT NULL UNIQUE,
    `nama_lengkap` VARCHAR(150) NOT NULL,
    `nim` VARCHAR(30) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL,
    `no_whatsapp` VARCHAR(25) NOT NULL,
    `angkatan` VARCHAR(10) NOT NULL,
    `kelas` VARCHAR(50) DEFAULT NULL,
    `divisi_utama` VARCHAR(100) NOT NULL,
    `divisi_cadangan` VARCHAR(100) NOT NULL,
    `motivasi` TEXT NOT NULL,
    `link_portofolio` VARCHAR(255) DEFAULT NULL,
    `file_foto` VARCHAR(255) DEFAULT NULL,
    `file_ktm` VARCHAR(255) DEFAULT NULL,
    `status_seleksi` VARCHAR(50) DEFAULT 'Menunggu Pengumuman',
    `catatan_panitia` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_nim` (`nim`),
    INDEX `idx_no_reg` (`nomor_registrasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- TABEL: admin_panitia (Login Admin)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_panitia` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `nama_panitia` VARCHAR(100) NOT NULL,
    `divisi_panitia` VARCHAR(50) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------
-- SAMPLE DATA (Status: Diterima / Tidak Diterima / Menunggu)
-- ---------------------------------------------------
INSERT INTO `pendaftar` (
    `nomor_registrasi`, `nama_lengkap`, `nim`, `email`, `no_whatsapp`, 
    `angkatan`, `kelas`, `divisi_utama`, `divisi_cadangan`, `motivasi`, 
    `status_seleksi`, `catatan_panitia`
) VALUES
(
    'HIMATIK-2026-1001',
    'Ahmad Rizky Pratama',
    '202410370311204',
    'rizky.pratama@webmail.ac.id',
    '081234567890',
    '2024',
    'Informatika B',
    'Pemrograman',
    'Jaringan',
    'Ingin mengasah kemampuan web fullstack dan algoritma bersama tim HIMATIK.',
    'Menunggu Pengumuman',
    'Berkas pendaftaran sedang dalam peninjauan oleh Admin.'
),
(
    'HIMATIK-2026-1002',
    'Dewi Sekar Ayu',
    '202410370311105',
    'dewi.sekar@webmail.ac.id',
    '081398765432',
    '2024',
    'Informatika A',
    'Mikro',
    'Pemrograman',
    'Tertarik mendalami mikrokontroler Arduino, sensor otomatisasi, dan IoT hardware.',
    'Diterima',
    'Selamat! Anda dinyatakan Diterima sebagai Anggota HIMATIK Divisi Mikro.'
),
(
    'HIMATIK-2026-1003',
    'Farhan Maulana',
    '202410370311088',
    'farhan.m@webmail.ac.id',
    '085612345678',
    '2023',
    'Informatika C',
    'Jaringan',
    'Mikro',
    'Tertarik mengelola server Linux, router switching, dan workshop cyber security.',
    'Tidak Diterima',
    'Mohon maaf, Anda belum memenuhi kualifikasi seleksi pada periode ini. Tetap semangat!'
)
ON DUPLICATE KEY UPDATE 
    `status_seleksi`=VALUES(`status_seleksi`),
    `catatan_panitia`=VALUES(`catatan_panitia`);

-- ---------------------------------------------------
-- TABEL: pengaturan (Biaya & Buka/Tutup Pendaftaran)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan` (
    `kunci` VARCHAR(50) PRIMARY KEY,
    `nilai` TEXT NOT NULL,
    `keterangan` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pengaturan` (`kunci`, `nilai`, `keterangan`) VALUES
('biaya_pendaftaran', '0', 'Biaya pendaftaran dalam Rupiah (0 = Gratis)'),
('catatan_pembayaran', 'Gratis tanpa biaya', 'Keterangan tambahan biaya'),
('status_pendaftaran', 'buka', 'Status pendaftaran: buka atau tutup'),
('pesan_penutupan', 'Pendaftaran calon anggota HIMATIK periode ini telah resmi ditutup. Terima kasih atas partisipasi Anda.', 'Pesan yang tampil saat pendaftaran ditutup')
ON DUPLICATE KEY UPDATE `nilai`=`nilai`;
