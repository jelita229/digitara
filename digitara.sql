-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2026 at 05:41 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `digitara`
--

-- --------------------------------------------------------

--
-- Table structure for table `objek_wisata`
--

CREATE TABLE `objek_wisata` (
  `id_objek_wisata` int(10) NOT NULL,
  `title_objek_wisata` varchar(255) NOT NULL,
  `deskripsi_objek_wisata` text NOT NULL,
  `img_objek_wisata` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `objek_wisata`
--

INSERT INTO `objek_wisata` (`id_objek_wisata`, `title_objek_wisata`, `deskripsi_objek_wisata`, `img_objek_wisata`) VALUES
(1, 'Melihat Keindahan Kebun Raya Bedugul', 'Tempat wisata alam dengan udara sejuk, pepohonan hijau, dan pemandangan yang menenangkan.', '../img/bali1.jpg'),
(2, 'Melihat Keindahan Simbol pulau Bali', 'Ikon khas Bali yang menawarkan pemandangan indah serta suasana tenang untuk bersantai.', '../img/bali2.jpg'),
(3, 'Melihat Keindahan Pura Uluwatu', 'Pura terkenal di atas tebing dengan panorama laut yang indah dan suasana budaya Bali yang kuat.', '../img/bali3.jpg'),
(4, 'Melihat Keindahan Pura Simbol Bali', 'Pura khas Bali dengan arsitektur tradisional, udara segar, dan pemandangan alam yang memukau.', '../img/bali4.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `created_at`) VALUES
(1, 'lucky', '3M_BestOAT', '$2y$10$kwqHei5qJzqUDXoGFocrLOAmmXcWZgTODV9z3NlEQQsg9HfI2Anw2', '2026-06-08 02:53:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `objek_wisata`
--
ALTER TABLE `objek_wisata`
  ADD PRIMARY KEY (`id_objek_wisata`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `objek_wisata`
--
ALTER TABLE `objek_wisata`
  MODIFY `id_objek_wisata` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
