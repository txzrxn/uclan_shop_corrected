-- Run this once if the existing uclan_shop database has already been imported.
-- For a clean installation, import uclan_shop_corrected.sql instead.

START TRANSACTION;

ALTER TABLE `tbl_products`
  ADD COLUMN `product_stock` enum('good-stock','last-few','out-of-stock') NOT NULL DEFAULT 'good-stock' AFTER `product_type`;

UPDATE `tbl_products`
SET `product_type` = 'UCLan Logo Tshirt'
WHERE `product_type` = '' OR `product_type` IS NULL;

UPDATE `tbl_products`
SET `product_title` = CASE `product_image`
  WHEN 'images/tshirts/tshirt1.jpg' THEN 'Red UCLan T-shirt'
  WHEN 'images/tshirts/tshirt2.jpg' THEN 'Green UCLan T-shirt'
  WHEN 'images/tshirts/tshirt3.jpg' THEN 'Blue UCLan T-shirt'
  WHEN 'images/tshirts/tshirt4.jpg' THEN 'Cyan UCLan T-shirt'
  WHEN 'images/tshirts/tshirt5.jpg' THEN 'Magenta UCLan T-shirt'
  WHEN 'images/tshirts/tshirt6.jpg' THEN 'Yellow UCLan T-shirt'
  WHEN 'images/tshirts/tshirt7.jpg' THEN 'Black UCLan T-shirt'
  WHEN 'images/tshirts/tshirt8.jpg' THEN 'Grey UCLan T-shirt'
  WHEN 'images/tshirts/tshirt9.jpg' THEN 'Burgundy UCLan T-shirt'
  WHEN 'images/tshirts/tshirt10.jpg' THEN 'White UCLan T-shirt'
  ELSE `product_title`
END;

UPDATE `tbl_products`
SET `product_stock` = CASE
  WHEN `product_image` IN (
    'images/tshirts/tshirt3.jpg', 'images/tshirts/tshirt5.jpg', 'images/tshirts/tshirt7.jpg',
    'images/hoodies/hoodie3.jpg', 'images/hoodies/hoodie6.jpg', 'images/hoodies/hoodie8.jpg',
    'images/jumpers/jumper3.jpg', 'images/jumpers/jumper6.jpg', 'images/jumpers/jumper8.jpg'
  ) THEN 'out-of-stock'
  WHEN `product_image` IN (
    'images/tshirts/tshirt2.jpg', 'images/tshirts/tshirt6.jpg', 'images/tshirts/tshirt10.jpg',
    'images/hoodies/hoodie2.jpg', 'images/hoodies/hoodie5.jpg', 'images/hoodies/hoodie9.jpg',
    'images/jumpers/jumper2.jpg', 'images/jumpers/jumper5.jpg', 'images/jumpers/jumper9.jpg'
  ) THEN 'last-few'
  ELSE 'good-stock'
END;

ALTER TABLE `tbl_orders`
  MODIFY `order_date` datetime NOT NULL DEFAULT current_timestamp(),
  MODIFY `product_ids` longtext NOT NULL;

COMMIT;
