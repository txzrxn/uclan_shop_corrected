-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 01:36 PM
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
-- Database: `uclan_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_offers`
--

CREATE TABLE `tbl_offers` (
  `offer_id` int(11) NOT NULL,
  `offer_title` varchar(255) NOT NULL,
  `offer_dec` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_offers`
--

INSERT INTO `tbl_offers` (`offer_id`, `offer_title`, `offer_dec`) VALUES
(1, 'Jumper Clearance', 'All jumpers are 25% off. Discount will be applied at checkout (hopefully).'),
(2, 'T-shirts: Buy 1 get 1 half-price.', 'Ready for summer? All T-shirts are buy 1 get 1 half-price. Promotion will be applied during checkout (hopefully).'),
(3, 'Graduation Promo-Code.', 'Graduating this year? Then you are entitled to 25% your total shop. Just add items to your cart and use discount code \"GRAD 25\" to apply the discount (hopefully).');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_orders`
--

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `product_ids` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_orders`
--

INSERT INTO `tbl_orders` (`order_id`, `order_date`, `user_id`, `product_ids`) VALUES
(10, '2026-07-16 15:33:31', 6, '{\"items\":[{\"product_id\":27,\"title\":\"Black UCLan Hoodie (Size M)\",\"quantity\":3,\"unit_price\":39.99,\"line_total\":119.97,\"size\":\"M\"}],\"subtotal\":119.97,\"discount_code\":\"\",\"discount_amount\":0,\"total\":119.97}'),
(11, '2026-07-16 15:40:13', 7, '{\"items\":[{\"product_id\":27,\"title\":\"Black UCLan Hoodie (Size M)\",\"quantity\":1,\"unit_price\":39.99,\"line_total\":39.99,\"size\":\"M\"},{\"product_id\":22,\"title\":\"Blue UCLan Hoodie (Size M)\",\"quantity\":2,\"unit_price\":39.99,\"line_total\":79.98,\"size\":\"M\"}],\"subtotal\":119.97,\"discount_code\":\"G21406018\",\"discount_amount\":29.99,\"total\":89.98}');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_products`
--

CREATE TABLE `tbl_products` (
  `product_id` int(11) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_desc` text NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_price` decimal(8,2) NOT NULL,
  `product_type` enum('UCLan Hoodie','UCLan Logo Tshirt','UCLan Logo Jumper') NOT NULL,
  `product_stock` enum('good-stock','last-few','out-of-stock') NOT NULL DEFAULT 'good-stock'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_products`
--

INSERT INTO `tbl_products` (`product_id`, `product_title`, `product_desc`, `product_image`, `product_price`, `product_type`, `product_stock`) VALUES
(1, 'Red UCLan T-shirt', 'Comfortable T-shirt with UCLan branding.', 'images/tshirts/tshirt1.jpg', 34.99, 'UCLan Logo Tshirt', 'good-stock'),
(2, 'Green UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt2.jpg', 14.99, 'UCLan Logo Tshirt', 'last-few'),
(3, 'White UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper8.jpg', 29.99, 'UCLan Logo Jumper', 'good-stock'),
(4, 'Blue UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt3.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(5, 'Cyan UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt4.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(6, 'Magenta UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt5.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(7, 'Yellow UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt6.jpg', 14.99, 'UCLan Logo Tshirt', 'last-few'),
(8, 'Black UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt7.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(9, 'Grey UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt8.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(10, 'Burgundy UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt9.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(11, 'Beige UCLan T-shirt', 'Comfortable T-shirt with UCLan branding.', 'images/tshirts/tshirt11.jpg', 34.99, 'UCLan Logo Tshirt', 'good-stock'),
(21, 'Red UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie1.jpg', 39.99, 'UCLan Hoodie', 'good-stock'),
(22, 'Blue UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie3.jpg', 39.99, 'UCLan Hoodie', 'last-few'),
(23, 'Green UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie2.jpg', 39.99, 'UCLan Hoodie', 'out-of-stock'),
(24, 'Cyan UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie4.jpg', 39.99, 'UCLan Hoodie', 'good-stock'),
(25, 'Salmon UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie5.jpg', 39.99, 'UCLan Hoodie', 'last-few'),
(26, 'Orange UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie6.jpg', 39.99, 'UCLan Hoodie', 'out-of-stock'),
(27, 'Black UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie7.jpg', 39.99, 'UCLan Hoodie', 'good-stock'),
(28, 'Light-grey UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie8.jpg', 39.99, 'UCLan Hoodie', 'out-of-stock'),
(29, 'Dark-grey UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie9.jpg', 39.99, 'UCLan Hoodie', 'last-few'),
(30, 'Red UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper1.jpg', 29.99, 'UCLan Logo Jumper', 'good-stock'),
(31, 'Green UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper2.jpg', 29.99, 'UCLan Logo Jumper', 'last-few'),
(32, 'Blue UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper3.jpg', 29.99, 'UCLan Logo Jumper', 'out-of-stock'),
(33, 'Burgundy UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie10.jpg', 29.99, 'UCLan Hoodie', 'good-stock'),
(34, 'Cyan UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper4.jpg', 29.99, 'UCLan Logo Jumper', 'good-stock'),
(35, 'Magenta UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper5.jpg', 29.99, 'UCLan Logo Jumper', 'last-few'),
(36, 'Yellow UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper6.jpg', 29.99, 'UCLan Logo Jumper', 'out-of-stock'),
(37, 'Black UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper7.jpg', 29.99, 'UCLan Logo Jumper', 'good-stock'),
(38, 'Burgundy UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper10.jpg', 29.99, 'UCLan Logo Jumper', 'good-stock'),
(39, 'Dark-grey UCLan Jumper', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Challenge winter weather in \'style\' this year.', 'images/jumpers/jumper9.jpg', 29.99, 'UCLan Logo Jumper', 'last-few');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_reviews`
--

CREATE TABLE `tbl_reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `review_title` varchar(120) NOT NULL,
  `review_desc` text NOT NULL,
  `review_rating` tinyint(3) UNSIGNED NOT NULL,
  `review_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `tbl_reviews`
--

INSERT INTO `tbl_reviews` (`review_id`, `user_id`, `product_id`, `review_title`, `review_desc`, `review_rating`, `review_timestamp`) VALUES
(5, 6, 27, 'cool hoodie!', 'i love it! very comfortable!', 5, '2026-07-16 09:32:15'),
(6, 7, 27, 'Charming color', 'Love the color of the hoodie! would buy again!', 5, '2026-07-16 09:39:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `created_at`) VALUES
(3, 'demo', 'demo@gmail.com', '$2y$10$lo9iqGqPS/TBRaPGjFPANOMPRsvt84eaObjH9mniybpq0BvJsXXvq', '2026-07-16 05:53:09'),
(4, 'demo123', 'demo123@gmail.com', '$2y$10$I44Qt6tx7D.5TrObpYDi8eJhoND7SQpTOG1LZm25kjgHMLo3b4Sd6', '2026-07-16 09:18:41'),
(6, '123123', '123123123@gmail.com', '$2y$10$xWumlQ1Fv45.R1KF047soeIClxX3Kn6r//1mT3JZ89W2cr5DnDzfa', '2026-07-16 09:28:38'),
(7, 'tazryanbinte', 'taz123@gmail.com', '$2y$10$eqn5hcj/646YZWWDbcKX4eDPqPLuS6aqrA5klEf4GWAwhCZHwo4d6', '2026-07-16 09:36:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_offers`
--
ALTER TABLE `tbl_offers`
  ADD PRIMARY KEY (`offer_id`);

--
-- Indexes for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

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
-- AUTO_INCREMENT for table `tbl_offers`
--
ALTER TABLE `tbl_offers`
  MODIFY `offer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_orders`
--
ALTER TABLE `tbl_orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_reviews`
--
ALTER TABLE `tbl_reviews`
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
