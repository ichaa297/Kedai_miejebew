-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 04, 2026 at 05:43 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_kedaimiejebew`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_keranjang`
--

CREATE TABLE `detail_keranjang` (
  `id_detail` int NOT NULL,
  `id_keranjang` int NOT NULL,
  `id_produk` int NOT NULL,
  `qty` int NOT NULL,
  `catatan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga` int DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `qty`, `harga`, `catatan`) VALUES
(1, 1, 9, 1, 20000, ''),
(2, 2, 8, 1, 10000, ''),
(3, 2, 7, 1, 8000, ''),
(4, 2, 6, 1, 22000, ''),
(5, 2, 5, 1, 6000, ''),
(6, 2, 4, 1, 10000, ''),
(7, 2, 3, 1, 18000, ''),
(8, 2, 2, 1, 18000, ''),
(9, 2, 1, 1, 18000, ''),
(10, 3, 8, 1, 10000, ''),
(11, 3, 7, 1, 8000, ''),
(12, 3, 6, 1, 22000, ''),
(13, 3, 5, 1, 6000, ''),
(14, 3, 4, 1, 10000, ''),
(15, 3, 3, 1, 18000, ''),
(16, 3, 2, 1, 18000, ''),
(17, 3, 1, 1, 18000, ''),
(18, 4, 8, 1, 10000, ''),
(19, 4, 1, 1, 18000, ''),
(20, 5, 9, 1, 20000, ''),
(21, 5, 8, 1, 10000, ''),
(22, 5, 3, 1, 18000, ''),
(23, 6, 1, 5, 18000, ''),
(24, 6, 4, 5, 10000, ''),
(25, 6, 6, 5, 22000, ''),
(26, 6, 9, 5, 20000, ''),
(27, 7, 1, 5, 18000, ''),
(28, 7, 4, 5, 10000, ''),
(29, 7, 6, 5, 22000, ''),
(30, 7, 9, 5, 20000, ''),
(31, 8, 5, 2, 6000, ''),
(32, 8, 9, 2, 20000, ''),
(33, 9, 1, 2, 18000, ''),
(34, 9, 2, 2, 18000, ''),
(35, 9, 3, 2, 18000, ''),
(36, 9, 5, 2, 6000, ''),
(37, 9, 7, 2, 8000, ''),
(38, 9, 9, 1, 20000, ''),
(39, 10, 1, 1, 18000, ''),
(40, 10, 8, 1, 10000, ''),
(41, 11, 1, 1, 18000, ''),
(42, 11, 2, 1, 18000, ''),
(43, 11, 3, 1, 18000, ''),
(44, 11, 4, 1, 10000, ''),
(45, 11, 5, 1, 6000, ''),
(46, 11, 6, 1, 22000, ''),
(47, 11, 7, 1, 8000, ''),
(48, 11, 8, 1, 10000, ''),
(49, 11, 9, 1, 20000, ''),
(50, 12, 9, 1, 20000, ''),
(51, 12, 8, 1, 10000, ''),
(52, 13, 6, 2, 22000, ''),
(53, 13, 7, 1, 8000, '');

-- --------------------------------------------------------

--
-- Table structure for table `detail_reservasi`
--

CREATE TABLE `detail_reservasi` (
  `id` int NOT NULL,
  `id_reservasi` int DEFAULT NULL,
  `id_produk` int DEFAULT NULL,
  `qty` int DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_reservasi`
--

INSERT INTO `detail_reservasi` (`id`, `id_reservasi`, `id_produk`, `qty`, `harga`, `catatan`) VALUES
(1, 1, 8, 1, '10000.00', NULL),
(2, 1, 7, 1, '8000.00', NULL),
(3, 1, 6, 1, '22000.00', NULL),
(4, 1, 5, 1, '6000.00', NULL),
(5, 1, 4, 1, '10000.00', NULL),
(6, 1, 3, 1, '18000.00', NULL),
(7, 1, 2, 1, '18000.00', NULL),
(8, 1, 1, 1, '18000.00', NULL),
(9, 2, 8, 1, '10000.00', NULL),
(10, 2, 1, 1, '18000.00', NULL),
(11, 3, 9, 5, '20000.00', NULL),
(12, 3, 6, 5, '22000.00', NULL),
(13, 3, 4, 5, '10000.00', NULL),
(14, 3, 1, 5, '18000.00', NULL),
(15, 4, 9, 2, '20000.00', NULL),
(16, 4, 5, 2, '6000.00', NULL),
(17, 5, 9, 1, '20000.00', NULL),
(18, 5, 7, 2, '8000.00', NULL),
(19, 5, 5, 2, '6000.00', NULL),
(20, 5, 3, 2, '18000.00', NULL),
(21, 5, 2, 2, '18000.00', NULL),
(22, 5, 1, 2, '18000.00', NULL),
(23, 6, 9, 1, '20000.00', NULL),
(24, 6, 8, 1, '10000.00', NULL),
(25, 6, 7, 1, '8000.00', NULL),
(26, 6, 6, 1, '22000.00', NULL),
(27, 6, 5, 1, '6000.00', NULL),
(28, 6, 4, 1, '10000.00', NULL),
(29, 6, 3, 1, '18000.00', NULL),
(30, 6, 2, 1, '18000.00', NULL),
(31, 6, 1, 1, '18000.00', NULL),
(32, 7, 7, 1, '8000.00', NULL),
(33, 7, 6, 2, '22000.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `kelola_produk`
--

CREATE TABLE `kelola_produk` (
  `id_produk` int NOT NULL,
  `nama_produk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `harga` varchar(50) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `stok` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kelola_produk`
--

INSERT INTO `kelola_produk` (`id_produk`, `nama_produk`, `harga`, `gambar`, `stok`) VALUES
(1, 'Mie Jebew Keju', 'Rp. 18.000', 'uploads/jebewkeju_1781976671_1783136870.jpg', 82),
(2, 'Bola Bola Ubi', 'Rp. 18.000', 'uploads/image_bola_1781976611_1783136853.png', 26),
(3, 'Cireng Isi Ayam', 'Rp. 18.000', 'uploads/image_cireng_1781975482_1783136838.png', 9),
(4, 'Ice Coffee', 'Rp. 10.000', 'uploads/image_coffee_1781976596_1783136814.png', 7),
(5, 'Ice Tea', 'Rp. 6.000', 'uploads/image_icetea_1781977007_1783136796.png', 27),
(6, 'Mie Jebew Gila', 'Rp. 22.000', 'uploads/image_jebew_1781976640_1783136779.png', 0),
(7, 'Ice Jeruk', 'Rp. 8.000', 'uploads/image_jeruk_1781976567_1783136741.png', 43),
(8, 'Pop Ice', 'Rp. 10.000', 'uploads/image_pop_1781975760_1783136728.png', 10),
(9, 'Wonton Setan', 'Rp. 20.000', 'uploads/image_wonton_1781976626_1783136701.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `koneksi_admin`
--

CREATE TABLE `koneksi_admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `koneksi_admin`
--

INSERT INTO `koneksi_admin` (`id_admin`, `username`, `password`, `email`) VALUES
(1, 'admin', 'admin123', 'wrdtshshlcha@gmail.com\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `pengguna`
--

CREATE TABLE `pengguna` (
  `id_user` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengguna`
--

INSERT INTO `pengguna` (`id_user`, `nama`, `email`, `password`, `dibuat_pada`) VALUES
(1, 'Icha', 'wardatushsholicha29@gmail.com', '$2y$10$ojUGES5hi9yjAxik4PNsIOcN9xFPdoyeujVs/XOzkmwJoF.kR9.DC', '2026-06-30 07:14:05'),
(2, 'cicicuit', 'cicicuit@gmail.com', '$2y$10$tUEJgrI7y1Oom9LQqT/sDOJk/ZSEU018Fn9juAnQL7ktOJUBcGZbm', '2026-07-01 04:12:07'),
(3, 'kaeisan', 'kaeisan@gmail.com', '$2y$10$gxk0.BJy2vJVxDIxLGZY3eIDFPQs6BVqln.jMqqJmoM40wcIvg./O', '2026-07-02 04:58:23'),
(4, 'Wardatush Sholicha', 'wardatush@gmail.com', '$2y$10$c9PuI8zB7mTobSl8A1rinu2IXZod3UypA76S4gxDb7lBppmx9O4H6', '2026-07-02 05:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nama_pemesan` varchar(100) DEFAULT NULL,
  `metode_bayar` enum('Tunai','QRIS') DEFAULT NULL,
  `total_harga` decimal(12,2) DEFAULT NULL,
  `status` enum('Diterima','Dimasak','Ditunggu','Selesai') DEFAULT 'Diterima',
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `lokasi` enum('makan disini','bawa pulang') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `id_user`, `nama_pemesan`, `metode_bayar`, `total_harga`, `status`, `tanggal`, `lokasi`) VALUES
(1, 1, 'ich', 'QRIS', '20000.00', 'Selesai', '2026-06-30 07:37:41', 'makan disini'),
(2, 1, 'Wardatush Sholicha', 'Tunai', '110000.00', 'Selesai', '2026-06-30 07:42:46', 'makan disini'),
(3, 1, 'Wardatush Sholicha', 'Tunai', '110000.00', 'Selesai', '2026-06-30 07:43:18', 'makan disini'),
(4, 1, 'ich', 'Tunai', '28000.00', 'Selesai', '2026-06-30 09:26:59', 'makan disini'),
(5, 2, 'cici', 'QRIS', '48000.00', 'Selesai', '2026-07-01 04:13:52', 'bawa pulang'),
(6, 2, 'cici', 'Tunai', '350000.00', 'Selesai', '2026-07-01 04:20:50', 'makan disini'),
(7, 2, 'cici', 'Tunai', '350000.00', 'Selesai', '2026-07-01 04:21:10', 'makan disini'),
(8, 2, 'kaeisan', 'Tunai', '52000.00', 'Selesai', '2026-07-01 04:50:24', 'makan disini'),
(9, 2, 'Wardatush Sholicha', 'Tunai', '156000.00', 'Selesai', '2026-07-01 04:51:54', 'makan disini'),
(10, 1, 'Wardatush Sholicha', 'QRIS', '28000.00', 'Selesai', '2026-07-01 08:37:44', 'makan disini'),
(11, 1, 'Wardatush Sholicha', 'Tunai', '130000.00', 'Diterima', '2026-07-01 08:58:58', 'makan disini'),
(12, 4, 'ich', 'QRIS', '30000.00', 'Selesai', '2026-07-02 05:51:49', 'makan disini'),
(13, 4, 'Wardatush Sholicha', 'Tunai', '52000.00', 'Diterima', '2026-07-02 05:57:05', 'makan disini');

-- --------------------------------------------------------

--
-- Table structure for table `proses_order`
--

CREATE TABLE `proses_order` (
  `id_order` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `total` varchar(50) DEFAULT NULL,
  `progres` enum('Diterima','Dimasak','Ditunggu','Selesai') DEFAULT 'Diterima',
  `tanggal_order` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proses_reservasi`
--

CREATE TABLE `proses_reservasi` (
  `id_reservasi` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `wa` varchar(32) DEFAULT NULL,
  `tgl` date DEFAULT NULL,
  `item` varchar(255) DEFAULT NULL,
  `total` varchar(50) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu','Dikonfirmasi') DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `proses_reservasi`
--

INSERT INTO `proses_reservasi` (`id_reservasi`, `id_user`, `nama`, `email`, `wa`, `tgl`, `item`, `total`, `catatan`, `status`) VALUES
(1, 1, 'Wardatush Sholicha', 'wardatushsholicha29@gmail.com', '082326235085', '2026-06-30', '[]', '110000', '', 'Dikonfirmasi'),
(2, 1, 'ich', 'wardatushsholicha29@gmail.com', '085643097535', '2026-06-30', '[{\"id\":\"8\",\"name\":\"Pop Ice\\nRp 10.000\",\"qty\":1,\"price\":10000},{\"id\":\"1\",\"name\":\"Mie Jebew Keju\\nRp 18.000\",\"qty\":1,\"price\":18000}]', '28000', '', 'Dikonfirmasi'),
(3, 2, 'cici', 'cicicuit@gmail.com', '082326235085', '2026-07-10', '[]', '350000', '', 'Dikonfirmasi'),
(4, 2, 'kaeisan', 'cicicuit@gmail.com', '082326235085', '2026-07-01', '[{\"id\":\"9\",\"name\":\"Wonton Setan\\nRp 20.000\",\"qty\":2,\"price\":20000},{\"id\":\"5\",\"name\":\"Ice Tea\\nRp 6.000\",\"qty\":2,\"price\":6000}]', '52000', '', 'Dikonfirmasi'),
(5, 2, 'Wardatush Sholicha', 'cicicuit@gmail.com', '082326235085', '2026-07-01', '[]', '156000', '', 'Dikonfirmasi'),
(6, 1, 'Wardatush Sholicha', 'wardatushsholicha29@gmail.com', '082326235085', '2026-07-01', '[]', '130000', '', 'Dikonfirmasi'),
(7, 4, 'Wardatush Sholicha', 'wardatush@gmail.com', '082326235085', '2026-07-02', '[{\"id\":\"7\",\"name\":\"Ice Jeruk\\nRp 8.000\",\"qty\":1,\"price\":8000},{\"id\":\"6\",\"name\":\"Mie Jebew Gila\\nRp 22.000\",\"qty\":2,\"price\":22000}]', '52000', '', 'Dikonfirmasi');

-- --------------------------------------------------------

--
-- Table structure for table `reservasi`
--

CREATE TABLE `reservasi` (
  `id_reservasi` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam` time DEFAULT NULL,
  `jumlah_orang` int DEFAULT NULL,
  `tempat` enum('Indoor','Outdoor') DEFAULT NULL,
  `catatan` text,
  `status` enum('Menunggu','Dikonfirmasi') DEFAULT 'Menunggu',
  `dibuat_pada` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_keranjang` (`id_keranjang`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `detail_reservasi`
--
ALTER TABLE `detail_reservasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_reservasi` (`id_reservasi`,`id_produk`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kelola_produk`
--
ALTER TABLE `kelola_produk`
  ADD PRIMARY KEY (`id_produk`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `koneksi_admin`
--
ALTER TABLE `koneksi_admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `pengguna`
--
ALTER TABLE `pengguna`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `proses_order`
--
ALTER TABLE `proses_order`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `proses_reservasi`
--
ALTER TABLE `proses_reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD PRIMARY KEY (`id_reservasi`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `detail_reservasi`
--
ALTER TABLE `detail_reservasi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `kelola_produk`
--
ALTER TABLE `kelola_produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `koneksi_admin`
--
ALTER TABLE `koneksi_admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengguna`
--
ALTER TABLE `pengguna`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `proses_order`
--
ALTER TABLE `proses_order`
  MODIFY `id_order` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proses_reservasi`
--
ALTER TABLE `proses_reservasi`
  MODIFY `id_reservasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reservasi`
--
ALTER TABLE `reservasi`
  MODIFY `id_reservasi` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_keranjang`
--
ALTER TABLE `detail_keranjang`
  ADD CONSTRAINT `detail_keranjang_ibfk_1` FOREIGN KEY (`id_detail`) REFERENCES `detail_pesanan` (`id_detail`),
  ADD CONSTRAINT `detail_keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `kelola_produk` (`id_produk`),
  ADD CONSTRAINT `detail_keranjang_ibfk_3` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`);

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `kelola_produk` (`id_produk`);

--
-- Constraints for table `detail_reservasi`
--
ALTER TABLE `detail_reservasi`
  ADD CONSTRAINT `detail_reservasi_ibfk_1` FOREIGN KEY (`id_produk`) REFERENCES `kelola_produk` (`id_produk`),
  ADD CONSTRAINT `detail_reservasi_ibfk_2` FOREIGN KEY (`id_reservasi`) REFERENCES `proses_reservasi` (`id_reservasi`);

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`);

--
-- Constraints for table `proses_order`
--
ALTER TABLE `proses_order`
  ADD CONSTRAINT `proses_order_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`);

--
-- Constraints for table `proses_reservasi`
--
ALTER TABLE `proses_reservasi`
  ADD CONSTRAINT `proses_reservasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`);

--
-- Constraints for table `reservasi`
--
ALTER TABLE `reservasi`
  ADD CONSTRAINT `reservasi_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `pengguna` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
