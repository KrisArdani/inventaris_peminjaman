-- =============================================================
-- Tabel: barang
-- Deskripsi: Menyimpan data inventaris barang BEM
-- =============================================================

CREATE TABLE IF NOT EXISTS `barang` (
    `id_barang` INT(11) NOT NULL AUTO_INCREMENT,
    `kode_barang` VARCHAR(50) NOT NULL UNIQUE,
    `nama_barang` VARCHAR(255) NOT NULL,
    `kategori` ENUM('Elektronik', 'Furniture', 'Alat Tulis', 'Perlengkapan', 'Olahraga', 'Lainnya') NOT NULL DEFAULT 'Lainnya',
    `jumlah_total` INT(11) NOT NULL DEFAULT 0,
    `jumlah_tersedia` INT(11) NOT NULL DEFAULT 0,
    `kondisi` ENUM('Baik', 'Rusak Ringan', 'Rusak Berat') NOT NULL DEFAULT 'Baik',
    `lokasi` VARCHAR(255) DEFAULT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `gambar` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- Data Contoh
-- =============================================================
INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `jumlah_total`, `jumlah_tersedia`, `kondisi`, `lokasi`, `deskripsi`) VALUES
('BRG-001', 'Proyektor Epson EB-X51', 'Elektronik', 3, 2, 'Baik', 'Ruang Sekretariat', 'Proyektor untuk keperluan rapat dan presentasi'),
('BRG-002', 'Laptop ASUS VivoBook', 'Elektronik', 5, 3, 'Baik', 'Ruang Sekretariat', 'Laptop untuk keperluan administrasi BEM'),
('BRG-003', 'Meja Lipat', 'Furniture', 20, 15, 'Baik', 'Gudang Utama', 'Meja lipat untuk acara outdoor'),
('BRG-004', 'Kursi Plastik', 'Furniture', 50, 40, 'Baik', 'Gudang Utama', 'Kursi plastik untuk acara dan rapat'),
('BRG-005', 'Speaker Portable JBL', 'Elektronik', 2, 1, 'Baik', 'Ruang Sekretariat', 'Speaker untuk keperluan acara'),
('BRG-006', 'Papan Tulis Portable', 'Alat Tulis', 4, 4, 'Baik', 'Ruang Rapat', 'Papan tulis untuk presentasi'),
('BRG-007', 'Kabel Roll 10m', 'Perlengkapan', 6, 5, 'Rusak Ringan', 'Gudang Utama', 'Kabel roll untuk keperluan kelistrikan acara'),
('BRG-008', 'Tenda 3x3m', 'Perlengkapan', 4, 2, 'Baik', 'Gudang Utama', 'Tenda untuk acara outdoor'),
('BRG-009', 'Bola Futsal Mikasa', 'Olahraga', 3, 3, 'Baik', 'Ruang Olahraga', 'Bola futsal standar untuk kegiatan olahraga'),
('BRG-010', 'Kamera DSLR Canon', 'Elektronik', 1, 0, 'Baik', 'Ruang Sekretariat', 'Kamera untuk dokumentasi kegiatan BEM');
