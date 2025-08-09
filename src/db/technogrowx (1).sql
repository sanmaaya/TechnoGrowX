-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 08:24 AM
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
-- Database: `technogrowx`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_responded` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disease_reports`
--

CREATE TABLE `disease_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `crop_type` varchar(100) NOT NULL,
  `symptoms` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `report_status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `solution` text DEFAULT NULL,
  `expert_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disease_reports`
--

INSERT INTO `disease_reports` (`id`, `user_id`, `crop_type`, `symptoms`, `image_path`, `report_status`, `created_at`, `solution`, `expert_id`) VALUES
(39, 18, 'Wheat', 'Leaf Rust (Puccinia triticina): Orange-red pustules on leaves.\r\n\r\nStem Rust (Puccinia graminis): Dark red to black pustules on stems.\r\n\r\nStripe Rust (Puccinia striiformis): Yellow stripes on leaves.', 'uploads/img_68047dd4947b51.04037022.jpg', 'pending', '2025-04-20 04:53:40', NULL, 19),
(40, 18, 'Millet', '	Yellowing, stunted growth, \"green ear\" formation (ears turn leafy)', 'uploads/img_68047e15122f51.37020103.jpg', 'pending', '2025-04-20 04:54:45', NULL, 20),
(41, 18, 'Maize', 'Yellowing, white downy growth under leaves, stunted plants', 'uploads/img_68047e3b27b533.55520792.jpg', 'pending', '2025-04-20 04:55:23', NULL, 19),
(42, 18, 'Wheat', 'Powdery Mildew (Blumeria graminis)\r\n\r\nWhite, powdery fungal growth on leaves and stems.', 'uploads/img_68047e5a584197.41735124.jpg', 'pending', '2025-04-20 04:55:54', NULL, 20),
(43, 18, 'Barley', 'White, powdery fungal growth on leaves and stems', 'uploads/img_68047e959ffb94.40364004.jpg', 'resolved', '2025-04-20 04:56:53', '1 tablespoon baking soda\r\n1/2 teaspoon liquid soap (NOT detergent)\r\n1 gallon water\r\nSpray lightly on leaves.', 19);

-- --------------------------------------------------------

--
-- Table structure for table `experts`
--

CREATE TABLE `experts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `experts`
--

INSERT INTO `experts` (`id`, `user_id`, `specialization`, `photo`, `experience`, `bio`, `address`) VALUES
(13, 19, 'Crop irrigation. ', NULL, 5, 'I am expert in crop diagnosis .', 'Delhi , India'),
(14, 20, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('farmer','expert','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `address`, `password`, `user_type`, `created_at`, `is_active`) VALUES
(17, 'Prince Kumar', 'princekumar123@gmail.com', '6587421365', 'Punjab', '5f5d259d2f0d700bd8e77544a4842e11', 'admin', '2025-04-19 12:06:44', 1),
(18, 'Prince Kumar', 'user@gmail.com', '1234567891', 'Phagwara , Jalandhar , Near LPU', '8aefb93c85c4d20e7f3194a1fa7e88f9', 'farmer', '2025-04-20 04:47:20', 1),
(19, 'First Expert', 'expert1@gmail.com', NULL, NULL, '3c869c1ae6fb4e7778293b7e5ad5932c', 'expert', '2025-04-20 04:48:35', 1),
(20, 'Second Expert', 'expert2@gmail.com', NULL, NULL, '3c869c1ae6fb4e7778293b7e5ad5932c', 'expert', '2025-04-20 04:49:19', 1),
(21, 'Harsh Kumar', 'harsh@gmail.com', NULL, NULL, '8aefb93c85c4d20e7f3194a1fa7e88f9', 'farmer', '2025-04-20 04:50:14', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disease_reports`
--
ALTER TABLE `disease_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_expert_id` (`expert_id`);

--
-- Indexes for table `experts`
--
ALTER TABLE `experts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `disease_reports`
--
ALTER TABLE `disease_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `experts`
--
ALTER TABLE `experts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `disease_reports`
--
ALTER TABLE `disease_reports`
  ADD CONSTRAINT `disease_reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_expert_id` FOREIGN KEY (`expert_id`) REFERENCES `experts` (`user_id`);

--
-- Constraints for table `experts`
--
ALTER TABLE `experts`
  ADD CONSTRAINT `experts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
