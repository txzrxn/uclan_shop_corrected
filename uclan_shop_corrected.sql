-- Corrected UCLan Legacy Shop database
-- Import this file into a new database named uclan_shop.
DROP DATABASE IF EXISTS uclan_shop;
CREATE DATABASE uclan_shop;
USE uclan_shop;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `tbl_reviews`;
DROP TABLE IF EXISTS `tbl_orders`;
DROP TABLE IF EXISTS `tbl_products`;
DROP TABLE IF EXISTS `tbl_offers`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_offers` (
  `offer_id` int(11) NOT NULL AUTO_INCREMENT,
  `offer_title` varchar(255) NOT NULL,
  `offer_dec` varchar(255) NOT NULL,
  PRIMARY KEY (`offer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_title` varchar(255) NOT NULL,
  `product_desc` text NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_price` decimal(8,2) NOT NULL,
  `product_type` enum('UCLan Hoodie','UCLan Logo Tshirt','UCLan Logo Jumper') NOT NULL,
  `product_stock` enum('good-stock','last-few','out-of-stock') NOT NULL DEFAULT 'good-stock',
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `product_ids` longtext NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tbl_reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `review_title` varchar(120) NOT NULL,
  `review_desc` text NOT NULL,
  `review_rating` tinyint unsigned NOT NULL,
  `review_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_review_rating` CHECK (`review_rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_offers` (`offer_id`, `offer_title`, `offer_dec`) VALUES
(1, 'Legacy Hoodie Offer', 'Get 10% off selected UCLan legacy hoodies while stocks last.'),
(2, 'Student Bundle xx', 'Buy a t-shirt and mug together for a reduced student bundle price.'),
(3, 'Final Week Discount', 'Limited time discount on selected Student Union merchandise.'),
(4, 'Test', '100% offer'),
(5, 'Test', '100% offer');

INSERT INTO `tbl_products` (`product_id`, `product_title`, `product_desc`, `product_image`, `product_price`, `product_type`, `product_stock`) VALUES
(1, 'Red UCLan T-shirt', 'Comfortable T-shirt with UCLan branding.', 'images/tshirts/tshirt1.jpg', 34.99, 'UCLan Logo Tshirt', 'good-stock'),
(2, 'Green UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt2.jpg', 14.99, 'UCLan Logo Tshirt', 'last-few'),
(4, 'Blue UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt3.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(5, 'Cyan UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt4.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(6, 'Magenta UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt5.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(7, 'Yellow UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt6.jpg', 14.99, 'UCLan Logo Tshirt', 'last-few'),
(8, 'Black UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt7.jpg', 14.99, 'UCLan Logo Tshirt', 'out-of-stock'),
(9, 'Grey UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt8.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(10, 'Burgundy UCLan T-shirt', 'Cotton t-shirt with printed university logo.', 'images/tshirts/tshirt9.jpg', 14.99, 'UCLan Logo Tshirt', 'good-stock'),
(21, 'Red UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie1.jpg', 39.99, 'UCLan Hoodie', 'good-stock'),
(22, 'Blue UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie2.jpg', 39.99, 'UCLan Hoodie', 'last-few'),
(23, 'Green UCLan Hoodie', 'Cotton authentic character and practicality are duly combined in this comfortable attire. Perfect for when the weather just cant decide.', 'images/hoodies/hoodie3.jpg', 39.99, 'UCLan Hoodie', 'out-of-stock'),
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

COMMIT;
