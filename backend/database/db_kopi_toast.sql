-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2026 at 03:32 AM
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
-- Database: `db_kopi_toast`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `nama_item` varchar(100) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `category` varchar(20) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `nama_item`, `size`, `category`, `harga`, `notes`) VALUES
(1, 'Espresso Single', 'Regular', 'Hot', 15000, 'Pure concentrated coffee shot, bold and intense.'),
(2, 'Americano Gayo', 'Large', 'Ice', 22000, 'Diluted espresso using premium Gayo Arabica beans.'),
(3, 'Cappuccino', 'Medium', 'Hot', 25000, 'Perfect balance of espresso, steamed milk, and thick foam.'),
(4, 'Caramel Macchiato', 'Large', 'Ice', 32000, 'Layered vanilla milk and espresso topped with caramel drizzle.'),
(5, 'Hazelnut Latte', 'Medium', 'Ice', 28000, 'Creamy cafe latte with a sweet nutty hazelnut syrup.'),
(6, 'V60 Manual Brew', 'Regular', 'Hot', 26000, 'Hand-poured coffee with bright acidity and clean finish.'),
(7, 'Classic Choco Toast', 'Regular', 'Food', 18000, 'Thick toasted bread with melted premium dark chocolate.'),
(8, 'Cheese Burst Toast', 'Regular', 'Food', 22000, 'Double toasted bread with creamy cheddar and mozzarella.'),
(9, 'Tiramisu Crunch Toast', 'Regular', 'Food', 24000, 'Toast topped with tiramisu glaze and biscuit crumbs.'),
(10, 'Garlic Butter Toast', 'Regular', 'Food', 20000, 'Savory toast infused with aromatic garlic and salted butter.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `api_key`, `created_at`) VALUES
(1, 'admin_kopi', 'admin@kopitoast.com', 'hashed_password_123', 'KOPI-ABC-12345', '2026-03-31 00:26:35'),
(2, 'mahasiswa_upn', 'mhs@upnjatim.ac.id', 'hashed_password_456', 'KOPI-XYZ-67890', '2026-03-31 00:26:35'),
(3, 'kelompok4', 'kopi@upn.ac.id', '$2y$10$pVur1NLsY4vcJ7FDrgIpK.KE1rkB0eI4oIG2XHHJ88Z0fnM3.lIV6', 'KOPI-88b17dc6522f3221', '2026-03-31 01:00:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
