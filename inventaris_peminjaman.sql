-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 11:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventaris_peminjaman`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `id_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` enum('Barang Habis Pakai','Barang Tidak Habis Pakai') NOT NULL DEFAULT 'Barang Tidak Habis Pakai',
  `stok_total` int(11) NOT NULL DEFAULT 0,
  `stok_tersedia` int(11) NOT NULL DEFAULT 0,
  `lokasi` varchar(100) DEFAULT NULL,
  `foto_barang` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`id_barang`, `nama_barang`, `kategori`, `stok_total`, `stok_tersedia`, `lokasi`, `foto_barang`, `created_at`, `gambar`) VALUES
('BRG-001', 'Proyektor Epson EB-X51', 'Barang Tidak Habis Pakai', 3, 2, 'Ruang Sekretariat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/proyektor.png'),
('BRG-002', 'Laptop ASUS VivoBook', 'Barang Habis Pakai', 5, 5, 'Ruang Sekretariat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/laptop.png'),
('BRG-003', 'Meja Lipat', 'Barang Habis Pakai', 20, 10, 'Gudang Utama', NULL, '2026-05-07 09:29:00', '../assets/images/barang/meja_lipat.png'),
('BRG-004', 'Kursi Plastik', 'Barang Habis Pakai', 50, 30, 'Gudang Utama', NULL, '2026-05-07 09:29:00', '../assets/images/barang/kursi_plastik.png'),
('BRG-005', 'Speaker Portable JBL', 'Barang Habis Pakai', 2, 1, 'Ruang Sekretariat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/speaker_jbl.png'),
('BRG-006', 'Spidol Whiteboard', 'Barang Habis Pakai', 30, 25, 'Ruang Rapat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/papan_tulis.png'),
('BRG-007', 'Kabel Roll 10m', 'Barang Habis Pakai', 6, 5, 'Gudang Utama', NULL, '2026-05-07 09:29:00', '../assets/images/barang/kabel_roll.png'),
('BRG-008', 'Tenda 3x3m', 'Barang Habis Pakai', 4, 4, 'Gudang Utama', NULL, '2026-05-07 09:29:00', '../assets/images/barang/tenda.png'),
('BRG-009', 'Kertas HVS A4', 'Barang Habis Pakai', 100, 80, 'Ruang Sekretariat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/bola_futsal.png'),
('BRG-010', 'Kamera DSLR Canon', 'Barang Habis Pakai', 1, 0, 'Ruang Sekretariat', NULL, '2026-05-07 09:29:00', '../assets/images/barang/kamera.png'),
('BRG-011', 'Kulkas Polytron', 'Barang Tidak Habis Pakai', 2, 2, 'Gudang Utama', NULL, '2026-05-09 06:22:05', NULL),
('BRG-012', 'tiker', 'Barang Tidak Habis Pakai', 1, 1, 'Gudang Utama', 'BRG-012_1778310496.png', '2026-05-09 07:08:16', '../assets/images/barang/BRG-012_1778310496.png');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` varchar(36) NOT NULL DEFAULT uuid(),
  `id_user` varchar(36) NOT NULL,
  `tgl_pengajuan` timestamp NOT NULL DEFAULT current_timestamp(),
  `status_approval` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `id_admin` varchar(36) DEFAULT NULL,
  `alasan_tolak` text DEFAULT NULL,
  `nama_kegiatan` varchar(255) DEFAULT NULL,
  `tujuan` text DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_user`, `tgl_pengajuan`, `status_approval`, `id_admin`, `alasan_tolak`, `nama_kegiatan`, `tujuan`, `lokasi`) VALUES
('PMJ-001', '87e06eea-478e-11f1-b45a-dd6d875e4335', '2026-05-01 01:00:00', 'disetujui', '87de4fbc-478e-11f1-b45a-dd6d875e4335', NULL, NULL, NULL, NULL),
('PMJ-002', '8e6c6323-4790-11f1-b45a-dd6d875e4335', '2026-05-03 03:30:00', 'disetujui', '87de4fbc-478e-11f1-b45a-dd6d875e4335', NULL, NULL, NULL, NULL),
('PMJ-003', '87e06eea-478e-11f1-b45a-dd6d875e4335', '2026-05-05 07:00:00', 'disetujui', '87de4fbc-478e-11f1-b45a-dd6d875e4335', NULL, NULL, NULL, NULL),
('PMJ-004', '8e6c6323-4790-11f1-b45a-dd6d875e4335', '2026-05-06 02:00:00', 'ditolak', '87de4fbc-478e-11f1-b45a-dd6d875e4335', 'kamu gaje', NULL, NULL, NULL),
('PMJ-005', '87e06eea-478e-11f1-b45a-dd6d875e4335', '2026-04-20 04:00:00', 'ditolak', '87de4fbc-478e-11f1-b45a-dd6d875e4335', 'Barang sedang dalam perbaikan', NULL, NULL, NULL),
('PMJ-79639', '87e06eea-478e-11f1-b45a-dd6d875e4335', '2026-05-09 05:33:31', 'disetujui', '87de4fbc-478e-11f1-b45a-dd6d875e4335', NULL, 'rapat', 'kurang kursi', 'aula ');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman_detail`
--

CREATE TABLE `peminjaman_detail` (
  `id_detail` varchar(36) NOT NULL DEFAULT uuid(),
  `id_peminjaman` varchar(36) NOT NULL,
  `id_barang` varchar(20) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `tgl_pinjam` date DEFAULT NULL,
  `tgl_kembali_rencana` date DEFAULT NULL,
  `status_item` enum('dipinjam','dikembalikan','keluar','terlambat') DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `peminjaman_detail`
--

INSERT INTO `peminjaman_detail` (`id_detail`, `id_peminjaman`, `id_barang`, `jumlah`, `tgl_pinjam`, `tgl_kembali_rencana`, `status_item`) VALUES
('DTL-001', 'PMJ-001', 'BRG-001', 1, '2026-05-02', '2026-05-09', 'dipinjam'),
('DTL-002', 'PMJ-001', 'BRG-005', 1, '2026-05-02', '2026-05-09', 'dipinjam'),
('DTL-003', 'PMJ-002', 'BRG-002', 2, '2026-05-04', '2026-05-11', 'dikembalikan'),
('DTL-004', 'PMJ-002', 'BRG-008', 2, '2026-05-04', '2026-05-11', 'dikembalikan'),
('DTL-005', 'PMJ-003', 'BRG-003', 5, '2026-05-07', '2026-05-14', 'dipinjam'),
('DTL-006', 'PMJ-003', 'BRG-004', 10, '2026-05-07', '2026-05-14', 'dipinjam'),
('DTL-007', 'PMJ-004', 'BRG-010', 1, '2026-05-08', '2026-05-15', 'dipinjam'),
('DTL-008', 'PMJ-005', 'BRG-007', 2, '2026-04-22', '2026-04-29', 'terlambat'),
('DTL-55562', 'PMJ-79639', 'BRG-004', 1, '2026-05-09', '2026-05-19', 'dikembalikan');

-- --------------------------------------------------------

--
-- Table structure for table `pengembalian`
--

CREATE TABLE `pengembalian` (
  `id_pengembalian` varchar(36) NOT NULL DEFAULT uuid(),
  `id_detail` varchar(36) NOT NULL,
  `tgl_kembali_asli` timestamp NOT NULL DEFAULT current_timestamp(),
  `kondisi_barang` text DEFAULT NULL,
  `denda` decimal(10,2) DEFAULT 0.00,
  `id_admin_penerima` varchar(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengembalian`
--

INSERT INTO `pengembalian` (`id_pengembalian`, `id_detail`, `tgl_kembali_asli`, `kondisi_barang`, `denda`, `id_admin_penerima`) VALUES
('KMB-00340', 'DTL-004', '2026-05-09 05:49:53', 'Baik', 10000000.00, '87de4fbc-478e-11f1-b45a-dd6d875e4335'),
('KMB-03857', 'DTL-003', '2026-05-09 05:57:03', 'Baik', 0.00, '87de4fbc-478e-11f1-b45a-dd6d875e4335'),
('KMB-46488', 'DTL-55562', '2026-05-09 05:37:12', 'Baik', 0.00, '87de4fbc-478e-11f1-b45a-dd6d875e4335');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` varchar(36) NOT NULL DEFAULT uuid(),
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','anggota','kepala') NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `asal_ormawa` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password_hash`, `nama_lengkap`, `role`, `no_hp`, `asal_ormawa`, `created_at`) VALUES
('87de4fbc-478e-11f1-b45a-dd6d875e4335', 'admin', '$2y$10$GqArQd81BMJaMnWg5tWPfuyHt.dSMH7Dm59XGWZ67Oy91cTTmDa0S', 'Budi Admin', 'admin', NULL, NULL, '2026-05-04 07:54:51'),
('87e06b43-478e-11f1-b45a-dd6d875e4335', 'kepala', '$2y$10$GqArQd81BMJaMnWg5tWPfuyHt.dSMH7Dm59XGWZ67Oy91cTTmDa0S', 'Pak Kepala BEM', 'kepala', NULL, NULL, '2026-05-04 07:54:51'),
('87e06eea-478e-11f1-b45a-dd6d875e4335', 'anggota', '$2y$10$GqArQd81BMJaMnWg5tWPfuyHt.dSMH7Dm59XGWZ67Oy91cTTmDa0S', 'Siti Anggota', 'anggota', NULL, NULL, '2026-05-04 07:54:51'),
('8e6c6323-4790-11f1-b45a-dd6d875e4335', 'ahmad', '$2y$10$8OIoI/pbo3lgoKSRTSLSDOa/xXPRiFTVw0l32eugMylXHbhaRuIFK', 'Ahmad Anggota Baru', 'anggota', '', '', '2026-05-04 08:09:21'),
('f607be8d-4b4e-11f1-9808-0a002700000d', 'testuser123', '$2y$10$qpZRdRHLMg0OoN0.sDrbme5oBhVzMM9l8PVPLplW8UIzLPeZDN2eC', 'Test User', 'anggota', '08123456789', 'Test Ormawa', '2026-05-09 02:29:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_peminjaman` (`id_peminjaman`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD PRIMARY KEY (`id_pengembalian`),
  ADD UNIQUE KEY `id_detail` (`id_detail`),
  ADD KEY `id_admin_penerima` (`id_admin_penerima`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `peminjaman_detail`
--
ALTER TABLE `peminjaman_detail`
  ADD CONSTRAINT `peminjaman_detail_ibfk_1` FOREIGN KEY (`id_peminjaman`) REFERENCES `peminjaman` (`id_peminjaman`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_detail_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id_barang`) ON DELETE CASCADE;

--
-- Constraints for table `pengembalian`
--
ALTER TABLE `pengembalian`
  ADD CONSTRAINT `pengembalian_ibfk_1` FOREIGN KEY (`id_detail`) REFERENCES `peminjaman_detail` (`id_detail`) ON DELETE CASCADE,
  ADD CONSTRAINT `pengembalian_ibfk_2` FOREIGN KEY (`id_admin_penerima`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
