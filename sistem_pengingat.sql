-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 02:26 PM
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
-- Database: `sistem_pengingat`
--

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `id_mk` int(11) NOT NULL,
  `Nama_mk` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`id_mk`, `Nama_mk`, `deskripsi`) VALUES
(4, 'OOP', 'Pemrograman berorientasi objek dengan konsep encapsulation, inheritance, polymorphism, dan abstraction.'),
(5, 'WCD', 'Pengembangan antarmuka web menggunakan HTML, CSS, JavaScript, dan framework frontend.'),
(6, 'DBMS', 'Pengembangan Database Manaejement System dan normalisasi data.');

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_tugas` int(11) NOT NULL,
  `waktu_kirim` datetime NOT NULL,
  `status_kirim` enum('terkirim','belum') DEFAULT 'belum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `statistik_user`
--

CREATE TABLE `statistik_user` (
  `id_user` int(11) NOT NULL,
  `total_tugas` int(11) DEFAULT 0,
  `tugas_selesai` int(11) DEFAULT 0,
  `tugas_belum` int(11) DEFAULT 0,
  `tugas_proses` int(11) DEFAULT 0,
  `rata_waktu_selesai` float NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `statistik_user`
--

INSERT INTO `statistik_user` (`id_user`, `total_tugas`, `tugas_selesai`, `tugas_belum`, `tugas_proses`, `rata_waktu_selesai`, `last_updated`) VALUES
(24, 3, 1, 1, 1, -2, '2025-07-08 18:41:47'),
(27, 0, NULL, NULL, NULL, 0, '2025-07-11 08:00:54'),
(28, 0, NULL, NULL, NULL, 0, '2025-07-10 06:07:58');

-- --------------------------------------------------------

--
-- Table structure for table `tugas`
--

CREATE TABLE `tugas` (
  `id_tugas` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_mk` int(11) NOT NULL,
  `judul` varchar(100) DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `Status` enum('belum','proses','selesai') DEFAULT 'belum',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_selesai` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tugas`
--

INSERT INTO `tugas` (`id_tugas`, `id_user`, `id_mk`, `judul`, `deadline`, `Status`, `dibuat_pada`, `tanggal_selesai`) VALUES
(31, 24, 4, 'data', '2025-07-10', 'selesai', '2025-07-08 18:44:47', '2025-07-07 17:00:00'),
(32, 24, 4, 'data', '2025-07-09', 'belum', '2025-07-08 19:05:31', '2025-07-08 19:05:31'),
(33, 24, 6, 'data', '2025-07-09', 'proses', '2025-07-09 14:13:12', '2025-07-09 14:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `Email`, `Password`) VALUES
(24, 'Elsa', 'ass@gmail.com', 'azaz'),
(27, 'rilas', 'rilasrundro@gmail.com', '12345'),
(28, 'Raihan', 'raihanmuhammadriswandi01@gmail.com', '12345');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`id_mk`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_tugas` (`id_tugas`);

--
-- Indexes for table `statistik_user`
--
ALTER TABLE `statistik_user`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `tugas`
--
ALTER TABLE `tugas`
  ADD PRIMARY KEY (`id_tugas`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_mk` (`id_mk`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  MODIFY `id_mk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tugas`
--
ALTER TABLE `tugas`
  MODIFY `id_tugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_tugas`) REFERENCES `tugas` (`id_tugas`) ON DELETE CASCADE;

--
-- Constraints for table `statistik_user`
--
ALTER TABLE `statistik_user`
  ADD CONSTRAINT `statistik_user_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `tugas`
--
ALTER TABLE `tugas`
  ADD CONSTRAINT `tugas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `tugas_ibfk_2` FOREIGN KEY (`id_mk`) REFERENCES `mata_kuliah` (`id_mk`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
