-- =============================================================
-- Migracion incremental: esquema de tienda SG Tienda
-- Fecha: 2026-09-12
-- -------------------------------------------------------------
-- Lleva una instalacion base IonAuth (SuplementosGym) al esquema
-- con catalogo y tienda. Idempotente en MariaDB 10.4.
--
-- Para instalaciones nuevas usar directamente database.sql.
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -------------------------------------------------------------
-- groups: ampliar e insertar grupo de cliente
-- -------------------------------------------------------------
ALTER TABLE `groups` MODIFY `name` varchar(30) NOT NULL;

SET @idx_exists = (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'groups' AND INDEX_NAME = 'uc_groups_name'
);
SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE `groups` ADD UNIQUE KEY `uc_groups_name` (`name`)',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO `groups` (`name`, `description`)
SELECT 'customer', 'Cliente - Comprador de la tienda'
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'customer');

-- -------------------------------------------------------------
-- countries
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(2) NOT NULL,
  `name` varchar(80) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'CRC',
  `currency_symbol` varchar(10) NOT NULL DEFAULT '₡',
  `phone_code` varchar(6) NOT NULL DEFAULT '506',
  `timezone` varchar(50) NOT NULL DEFAULT 'America/Costa_Rica',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_countries_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `countries` (`code`, `name`, `currency`, `currency_symbol`, `phone_code`, `timezone`, `is_active`, `sort_order`)
SELECT * FROM (
  SELECT 'CR', 'Costa Rica', 'CRC', '₡', '506', 'America/Costa_Rica', 1, 1
  UNION ALL
  SELECT 'SV', 'El Salvador', 'USD', '$', '503', 'America/El_Salvador', 1, 2
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM `countries`);

-- -------------------------------------------------------------
-- users: campos de tienda
-- -------------------------------------------------------------
ALTER TABLE `users`
  ADD COLUMN IF NOT EXISTS `country_id` int(10) unsigned DEFAULT NULL AFTER `id`,
  ADD COLUMN IF NOT EXISTS `phone2` varchar(30) DEFAULT NULL AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `delivery_zone` varchar(100) DEFAULT NULL AFTER `phone2`,
  ADD COLUMN IF NOT EXISTS `delivery_address` text DEFAULT NULL AFTER `delivery_zone`,
  ADD COLUMN IF NOT EXISTS `avatar` varchar(255) DEFAULT NULL AFTER `delivery_address`;

SET @fk_exists = (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'fk_users_country'
);
SET @sql = IF(@fk_exists = 0,
  'ALTER TABLE `users` ADD CONSTRAINT `fk_users_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------------
-- products
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `sku` varchar(30) NOT NULL,
  `product_type` varchar(100) NOT NULL DEFAULT '',
  `laboratory` varchar(120) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `servings` varchar(50) DEFAULT NULL,
  `flavor` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `stock_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `store_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `seo_title` varchar(150) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_products_country_sku` (`country_id`, `sku`),
  KEY `idx_products_type` (`product_type`),
  KEY `idx_products_laboratory` (`laboratory`),
  KEY `idx_products_active` (`is_active`),
  KEY `idx_products_featured` (`featured`),
  KEY `idx_products_name` (`name`),
  CONSTRAINT `fk_products_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- payment_methods
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_methods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_payment_methods_country_code` (`country_id`, `code`),
  CONSTRAINT `fk_payment_methods_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_orders
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `store_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `customer_phone2` varchar(30) DEFAULT NULL,
  `delivery_zone` varchar(100) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method_id` int(10) unsigned DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'pendiente',
  `status` varchar(30) NOT NULL DEFAULT 'pendiente',
  `inventory_applied` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_store_orders_country_number` (`country_id`, `order_number`),
  KEY `idx_store_orders_user` (`user_id`),
  KEY `idx_store_orders_status` (`status`),
  KEY `idx_store_orders_payment_status` (`payment_status`),
  KEY `idx_store_orders_created` (`created_at`),
  CONSTRAINT `fk_store_orders_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_store_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_store_orders_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_order_items
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `store_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `item_sku` varchar(30) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `line_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_store_order_items_order` (`order_id`),
  KEY `idx_store_order_items_product` (`product_id`),
  CONSTRAINT `fk_store_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_store_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- inventory_movements
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'salida',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `previous_qty` int(11) NOT NULL DEFAULT 0,
  `new_qty` int(11) NOT NULL DEFAULT 0,
  `reason` varchar(150) DEFAULT NULL,
  `created_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inventory_movements_product` (`product_id`),
  KEY `idx_inventory_movements_order` (`order_id`),
  KEY `idx_inventory_movements_type` (`type`),
  CONSTRAINT `fk_inventory_movements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_inventory_movements_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_settings
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `store_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `key` varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_store_settings_country_key` (`country_id`, `key`),
  CONSTRAINT `fk_store_settings_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
