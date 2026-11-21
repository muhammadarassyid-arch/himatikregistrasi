USE himatik_db;

-- 1. Buat Tabel Pengaturan untuk Biaya Pendaftaran
CREATE TABLE IF NOT EXISTS pengaturan (
    kunci VARCHAR(50) PRIMARY KEY,
    nilai VARCHAR(255) NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('biaya_pendaftaran', '0', 'Biaya pendaftaran dalam Rupiah (0 = Gratis)'),
('catatan_pembayaran', 'Pendaftaran tidak dipungut biaya (Gratis)', 'Keterangan tambahan biaya')
ON DUPLICATE KEY UPDATE kunci=kunci;

-- 2. Ubah kolom status_seleksi menjadi VARCHAR agar fleksibel (Diterima, Tidak Diterima, Menunggu)
ALTER TABLE pendaftar MODIFY COLUMN status_seleksi VARCHAR(50) DEFAULT 'Menunggu Pengumuman';

-- 3. Update status data demo
UPDATE pendaftar SET status_seleksi='Menunggu Pengumuman' WHERE nim='202410370311204';
UPDATE pendaftar SET status_seleksi='Diterima' WHERE nim='202410370311105';
UPDATE pendaftar SET status_seleksi='Tidak Diterima' WHERE nim='202410370311088';
