-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 24, 2026 at 09:40 AM
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
-- Database: `db_sipadu_dishub`
--

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Angkutan Umum'),
(2, 'Rambu & Marka Jalan'),
(3, 'Kondisi Jalan Rusak'),
(4, 'Parkir Liar'),
(5, 'Lampu Lalu Lintas'),
(6, 'Kemacetan'),
(7, 'Terminal & Halte'),
(8, 'Lainnya');

-- --------------------------------------------------------

--
-- Table structure for table `pelapor_google`
--

CREATE TABLE `pelapor_google` (
  `id_pelapor` int(11) NOT NULL,
  `google_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelapor_google`
--

INSERT INTO `pelapor_google` (`id_pelapor`, `google_id`, `email`, `nama`, `foto`, `created_at`) VALUES
(1, '102895148050829809852', 'tioapriansyah2@gmail.com', 'Tio Apriansyah', 'https://lh3.googleusercontent.com/a/ACg8ocIElXQyrfJzvbBeSDa93TSgVTiwMMXfy1OCIXeNHznifVL3jA=s96-c', '2026-08-17 13:09:47');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `id_pelapor` int(11) DEFAULT NULL,
  `ip_pelapor` varchar(45) DEFAULT NULL,
  `ticket` varchar(20) NOT NULL,
  `nama_pelapor` varchar(100) NOT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `kontak` varchar(50) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `lokasi` varchar(150) NOT NULL,
  `deskripsi` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` enum('diterima','diproses','selesai','ditolak') DEFAULT 'diterima',
  `disembunyikan` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `id_pelapor`, `ip_pelapor`, `ticket`, `nama_pelapor`, `telepon`, `email`, `kontak`, `id_kategori`, `lokasi`, `deskripsi`, `foto`, `latitude`, `longitude`, `status`, `disembunyikan`, `created_at`) VALUES
(1, NULL, NULL, 'HUB-260816-48DE', 'sfvcx', NULL, NULL, 'wefwevxc', 2, 'wevdxczx', 'vcx ', NULL, NULL, NULL, 'diterima', 0, '2026-08-16 20:47:04'),
(2, NULL, NULL, 'HUB-260816-FCBB', 'ewf', NULL, NULL, '2fqwesdc', 3, 'regvdscx', 'wrgvsdcx', NULL, NULL, NULL, 'selesai', 0, '2026-08-16 20:49:43'),
(8, NULL, '192.168.1.4', 'HUB-260817-B11C', 'hahavaa', NULL, NULL, 'hshavaga@gmail.com', 4, 'hsbagaga', 'hsbagsva', 'uploads/pengaduan/pengaduan_1786993180_d9b69082.jpg', -0.3868930, 102.5662136, 'diterima', 0, '2026-08-18 01:59:40'),
(9, NULL, '192.168.1.4', 'HUB-260817-D707', 'hahavaa', NULL, NULL, 'hsahvsyag@gmail.com', 3, 'hsahvaa', 'haababa', NULL, -0.3930979, 102.5803757, 'diterima', 0, '2026-08-18 02:00:53'),
(10, NULL, '192.168.1.4', 'HUB-260817-391B', 'havaag', NULL, NULL, 'aggaagayay@gmail.com', 5, 'yasgasg', 'baahvshs', NULL, NULL, NULL, 'diterima', 0, '2026-08-18 02:06:46'),
(11, NULL, '::1', 'HUB-260817-259E', 'wads', NULL, NULL, 'safasf', 3, 'sfasa', 'asdawfa', 'uploads/pengaduan/pengaduan_1786995229_b465f0bf.png', NULL, NULL, 'diterima', 0, '2026-08-18 02:33:49');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_status`
--

CREATE TABLE `riwayat_status` (
  `id_riwayat` int(11) NOT NULL,
  `id_pengaduan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `catatan` text DEFAULT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_status`
--

INSERT INTO `riwayat_status` (`id_riwayat`, `id_pengaduan`, `id_user`, `status`, `catatan`, `waktu`) VALUES
(1, 1, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-16 20:47:04'),
(2, 2, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-16 20:49:43'),
(3, 2, 1, 'diproses', '', '2026-08-16 20:55:45'),
(10, 2, 1, 'selesai', 'selesai', '2026-08-18 01:38:57'),
(11, 8, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-18 01:59:40'),
(12, 9, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-18 02:00:53'),
(13, 10, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-18 02:06:46'),
(14, 11, NULL, 'diterima', 'Pengaduan diterima sistem', '2026-08-18 02:33:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'petugas',
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `username`, `password`, `role`, `aktif`, `created_at`) VALUES
(1, 'Petugas Dishub', 'admin', '$2y$10$qGzMYInbhwkDgWPfsNJ4Yuxn/Lon3sM3UFVFmwflk41aCxoySDRpW', 'admin', 1, '2026-08-19 09:40:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `pelapor_google`
--
ALTER TABLE `pelapor_google`
  ADD PRIMARY KEY (`id_pelapor`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD UNIQUE KEY `ticket` (`ticket`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_pelapor` (`id_pelapor`);

--
-- Indexes for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD PRIMARY KEY (`id_riwayat`),
  ADD KEY `id_pengaduan` (`id_pengaduan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `pelapor_google`
--
ALTER TABLE `pelapor_google`
  MODIFY `id_pelapor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`),
  ADD CONSTRAINT `pengaduan_ibfk_2` FOREIGN KEY (`id_pelapor`) REFERENCES `pelapor_google` (`id_pelapor`);

--
-- Constraints for table `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD CONSTRAINT `riwayat_status_ibfk_1` FOREIGN KEY (`id_pengaduan`) REFERENCES `pengaduan` (`id_pengaduan`),
  ADD CONSTRAINT `riwayat_status_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
