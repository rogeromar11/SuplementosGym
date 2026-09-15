-- =============================================================
-- SuplementosGym · SG Tienda — Esquema base completo (Fase 2)
-- -------------------------------------------------------------
-- Motor: MariaDB 10.4 / MySQL 8 · InnoDB · utf8mb4_unicode_ci
-- Base: suplementosgym
--
-- Contenido:
--   · IonAuth: groups, users, users_groups, login_attempts
--   · Catalogo: countries, products, payment_methods
--   · Tienda: store_orders, store_order_items,
--             inventory_movements, store_settings
--
-- Datos:
--   · countries (CR/SV) y un administrador de desarrollo.
--   · Los productos y metodos de pago se migran en la Fase 3
--     (ver upgrade_add_store_schema_20260912.sql y migrate).
--
-- Admin de desarrollo: admin@admin.com / password  (cambiar en prod)
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS `store_order_items`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `store_orders`;
DROP TABLE IF EXISTS `store_settings`;
DROP TABLE IF EXISTS `payment_methods`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `users_groups`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `groups`;
DROP TABLE IF EXISTS `countries`;

SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
-- countries
-- -------------------------------------------------------------
CREATE TABLE `countries` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `countries` (`id`, `code`, `name`, `currency`, `currency_symbol`, `phone_code`, `timezone`, `is_active`, `sort_order`) VALUES
  (1, 'CR', 'Costa Rica', 'CRC', '₡', '506', 'America/Costa_Rica', 1, 1),
  (2, 'SV', 'El Salvador', 'USD', '$', '503', 'America/El_Salvador', 1, 2);

-- -------------------------------------------------------------
-- groups (IonAuth)
-- -------------------------------------------------------------
CREATE TABLE `groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_groups_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `groups` (`id`, `name`, `description`) VALUES
  (1, 'admin', 'Administrador - Acceso completo'),
  (2, 'customer', 'Cliente - Comprador de la tienda');

-- -------------------------------------------------------------
-- users (IonAuth + campos de tienda)
-- -------------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(254) NOT NULL,
  `activation_selector` varchar(255) DEFAULT NULL,
  `activation_code` varchar(255) DEFAULT NULL,
  `forgotten_password_selector` varchar(255) DEFAULT NULL,
  `forgotten_password_code` varchar(255) DEFAULT NULL,
  `forgotten_password_time` int(11) unsigned DEFAULT NULL,
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_code` varchar(255) DEFAULT NULL,
  `created_on` int(11) unsigned NOT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `first_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone2` varchar(30) DEFAULT NULL,
  `delivery_zone` varchar(100) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_users_email` (`email`),
  UNIQUE KEY `uc_users_username` (`username`),
  UNIQUE KEY `uc_users_activation_selector` (`activation_selector`),
  UNIQUE KEY `uc_users_forgotten_password_selector` (`forgotten_password_selector`),
  UNIQUE KEY `uc_users_remember_selector` (`remember_selector`),
  KEY `idx_users_country` (`country_id`),
  CONSTRAINT `fk_users_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin de desarrollo (password: "password"). Cambiar en produccion.
INSERT INTO `users`
  (`id`, `country_id`, `ip_address`, `username`, `password`, `email`, `activation_code`, `forgotten_password_code`, `created_on`, `last_login`, `active`, `first_name`, `last_name`, `company`, `phone`) VALUES
  (1, 1, '127.0.0.1', 'administrator', '$2y$08$200Z6ZZbp3RAEXoaWcMA6uJOFicwNZaqk4oDhqTUiFXFe63MG.Daa', 'admin@admin.com', NULL, NULL, 1268889823, 1268889823, 1, 'Admin', 'Tienda', 'SG Tienda', '00000000');

-- -------------------------------------------------------------
-- users_groups (IonAuth)
-- -------------------------------------------------------------
CREATE TABLE `users_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_users_groups` (`user_id`, `group_id`),
  KEY `fk_users_groups_users1_idx` (`user_id`),
  KEY `fk_users_groups_groups1_idx` (`group_id`),
  CONSTRAINT `fk_users_groups_users1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users_groups` (`id`, `user_id`, `group_id`) VALUES
  (1, 1, 1);

-- -------------------------------------------------------------
-- login_attempts (IonAuth)
-- -------------------------------------------------------------
CREATE TABLE `login_attempts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `login` varchar(100) NOT NULL,
  `time` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- products (catalogo + campos de tienda)
-- -------------------------------------------------------------
CREATE TABLE `products` (
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
  KEY `fk_products_country` (`country_id`),
  KEY `fk_products_created_by` (`created_by`),
  KEY `fk_products_updated_by` (`updated_by`),
  CONSTRAINT `fk_products_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_products_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- payment_methods
-- -------------------------------------------------------------
CREATE TABLE `payment_methods` (
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
-- store_orders (pedido e-commerce)
-- -------------------------------------------------------------
CREATE TABLE `store_orders` (
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
  KEY `fk_store_orders_country` (`country_id`),
  KEY `fk_store_orders_payment_method` (`payment_method_id`),
  CONSTRAINT `fk_store_orders_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_store_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_store_orders_payment_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_order_items (detalle del pedido)
-- -------------------------------------------------------------
CREATE TABLE `store_order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `item_sku` varchar(30) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `item_flavor` varchar(120) DEFAULT NULL,
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
-- inventory_movements (movimientos de inventario)
-- -------------------------------------------------------------
CREATE TABLE `inventory_movements` (
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
  KEY `fk_inventory_movements_user` (`created_by`),
  CONSTRAINT `fk_inventory_movements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_inventory_movements_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_inventory_movements_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_settings (configuracion por pais: contacto, WhatsApp, envio)
-- -------------------------------------------------------------
CREATE TABLE `store_settings` (
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
