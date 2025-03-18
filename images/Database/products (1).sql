-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2024 at 06:12 AM
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
-- Database: `macherie_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `productName` varchar(225) NOT NULL,
  `productPrice` decimal(20,2) NOT NULL,
  `productDiscount` decimal(20,2) NOT NULL,
  `productDescription` varchar(225) NOT NULL,
  `productSize` decimal(20,2) NOT NULL,
  `quantity` int(200) NOT NULL,
  `productImage` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `productName`, `productPrice`, `productDiscount`, `productDescription`, `productSize`, `quantity`, `productImage`) VALUES
(4, 'Harmony Classic Arc Earrings', 12000.00, 4000.00, 'Stylishly crafted for elegance', 7.00, 20, 'C:\\xampp\\htdocs\\Macherie\\images\\1 a.webp'),
(5, 'Beatitude Rings', 15700.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\9.webp'),
(6, '7th Heaven Classic Arc Earrings ', 19500.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\2.webp'),
(7, 'Unity Bangles', 18700.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\3.webp'),
(8, 'LOVEx Hammered Ring ', 15700.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\image17.webp'),
(9, 'Unity Linked Dainty Circle Silver Necklace', 19500.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\image18.webp'),
(10, 'Gladness Rings', 28700.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\1 a.webp'),
(11, 'The June Necklace', 14700.00, 0.00, '', 0.00, 0, 'C:\\xampp\\htdocs\\Macherie\\images\\earing-3.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
