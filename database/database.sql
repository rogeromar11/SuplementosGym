-- =============================================================
-- SuplementosGym · SG Tienda + Administración — Esquema base completo
-- -------------------------------------------------------------
-- Motor: MariaDB 10.4 / MySQL 8 · InnoDB · utf8mb4_unicode_ci
-- Base: suplementosgym
--
-- Contenido:
--   · IonAuth: groups, users, users_groups, login_attempts
--   · Catalogo: countries, products, payment_methods
--   · Tienda: store_orders, store_order_items,
--             inventory_movements, store_settings
--   · Administración (/admin): clients, warehouses, transports,
--             route_shifts, delivery_failure_reasons, routes,
--             route_orders, route_transfer_history,
--             warehouse_preparation_history, delivery_attempts,
--             order_status_history, attachments, payments,
--             payment_history, cash_deposits, cash_deposit_routes,
--             permissions, group_permissions, audit_logs,
--             system_settings (incluido al final del archivo).
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

DROP TABLE IF EXISTS `cash_deposit_routes`;
DROP TABLE IF EXISTS `cash_deposits`;
DROP TABLE IF EXISTS `store_visits`;
DROP TABLE IF EXISTS `payment_history`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `attachments`;
DROP TABLE IF EXISTS `order_status_history`;
DROP TABLE IF EXISTS `delivery_attempts`;
DROP TABLE IF EXISTS `warehouse_preparation_history`;
DROP TABLE IF EXISTS `route_transfer_history`;
DROP TABLE IF EXISTS `route_orders`;
DROP TABLE IF EXISTS `routes`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `group_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `store_order_items`;
DROP TABLE IF EXISTS `inventory_movements`;
DROP TABLE IF EXISTS `store_orders`;
DROP TABLE IF EXISTS `store_settings`;
DROP TABLE IF EXISTS `delivery_failure_reasons`;
DROP TABLE IF EXISTS `route_shifts`;
DROP TABLE IF EXISTS `transports`;
DROP TABLE IF EXISTS `warehouses`;
DROP TABLE IF EXISTS `clients`;
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


-- =============================================================
-- Migracion incremental: esquema de administracion (backoffice)
-- Fecha: 2026-09-15
-- Destino: suplementosgym
-- -------------------------------------------------------------
-- Integra la funcionalidad de SGMensajeria como panel /admin
-- dentro de SG Tienda, usando `store_orders` como pedido maestro.
--
-- Agrega:
--   · clients (homologado con users via user_id nullable)
--   · almacenes, turnos, rutas, orden de ruta y transferencias
--   · preparacion, intentos de entrega y motivos de no entrega
--   · pagos, historial de pagos y depositos de efectivo
--   · historial de estados, adjuntos, auditoria
--   · permisos granulares y configuracion del sistema
--   · grupos de staff y columnas de origen/saldo/geo en store_orders
--
-- Idempotente en MariaDB 10.4 (IF NOT EXISTS y guardas).
-- Para instalaciones nuevas usar directamente database.sql.
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- -------------------------------------------------------------
-- groups: agregar grupos de staff (IonAuth)
-- -------------------------------------------------------------
INSERT INTO `groups` (`name`, `description`)
SELECT 'vendedor', 'Vendedor - Registra y consulta pedidos'
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'vendedor');

INSERT INTO `groups` (`name`, `description`)
SELECT 'bodeguero', 'Bodeguero - Prepara pedidos en bodega'
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'bodeguero');

INSERT INTO `groups` (`name`, `description`)
SELECT 'mensajero', 'Mensajero - Consulta su ruta y gestiona entregas'
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'mensajero');

INSERT INTO `groups` (`name`, `description`)
SELECT 'auxiliar_admin', 'Auxiliar Admin - Apoyo al administrador'
WHERE NOT EXISTS (SELECT 1 FROM `groups` WHERE `name` = 'auxiliar_admin');

-- -------------------------------------------------------------
-- clients (homologado con users: user_id nullable)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `phone2` varchar(30) DEFAULT NULL,
  `zone` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `client_type` varchar(20) NOT NULL DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_clients_country_code` (`country_id`,`code`),
  UNIQUE KEY `uc_clients_user` (`user_id`),
  KEY `idx_clients_name` (`name`),
  KEY `idx_clients_phone` (`phone`),
  KEY `fk_clients_country` (`country_id`),
  KEY `fk_clients_user` (`user_id`),
  KEY `fk_clients_created_by` (`created_by`),
  KEY `fk_clients_updated_by` (`updated_by`),
  CONSTRAINT `fk_clients_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_clients_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_clients_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_clients_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- warehouses
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `warehouses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(120) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `directions` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_warehouses_country_code` (`country_id`,`code`),
  KEY `fk_warehouses_country` (`country_id`),
  KEY `fk_warehouses_created_by` (`created_by`),
  KEY `fk_warehouses_updated_by` (`updated_by`),
  CONSTRAINT `fk_warehouses_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_warehouses_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_warehouses_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- transports (transportes de encomienda)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `transports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_transports_country_code` (`country_id`,`code`),
  KEY `fk_transports_country` (`country_id`),
  CONSTRAINT `fk_transports_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- route_shifts (turnos)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `route_shifts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_route_shifts_country_code` (`country_id`,`code`),
  KEY `fk_route_shifts_country` (`country_id`),
  CONSTRAINT `fk_route_shifts_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- delivery_failure_reasons (motivos de no entrega)
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_failure_reasons` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `code` varchar(40) NOT NULL,
  `name` varchar(100) NOT NULL,
  `requires_description` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_delivery_failure_reasons_country_code` (`country_id`,`code`),
  KEY `fk_delivery_failure_reasons_country` (`country_id`),
  CONSTRAINT `fk_delivery_failure_reasons_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- routes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `routes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `route_number` varchar(30) NOT NULL,
  `route_date` date NOT NULL,
  `shift_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned DEFAULT NULL,
  `courier_user_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'borrador',
  `notes` text DEFAULT NULL,
  `started_at` datetime DEFAULT NULL,
  `finished_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) unsigned DEFAULT NULL,
  `updated_by` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_routes_country_number` (`country_id`,`route_number`),
  KEY `idx_routes_date_shift` (`route_date`,`shift_id`),
  KEY `idx_routes_courier` (`courier_user_id`),
  KEY `idx_routes_warehouse` (`warehouse_id`),
  KEY `idx_routes_status` (`status`),
  KEY `fk_routes_country` (`country_id`),
  KEY `fk_routes_shift` (`shift_id`),
  KEY `fk_routes_created_by` (`created_by`),
  CONSTRAINT `fk_routes_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_routes_shift` FOREIGN KEY (`shift_id`) REFERENCES `route_shifts` (`id`),
  CONSTRAINT `fk_routes_courier` FOREIGN KEY (`courier_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_routes_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_routes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- route_orders
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `route_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `route_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `delivery_order` int(11) NOT NULL DEFAULT 0,
  `route_status` varchar(20) NOT NULL DEFAULT 'pendiente',
  `assigned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `active_key` tinyint(1) DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_route_orders_route_order` (`route_id`,`order_id`),
  UNIQUE KEY `uc_route_orders_active_order` (`order_id`,`active_key`),
  KEY `idx_route_orders_delivery_order` (`route_id`,`delivery_order`),
  KEY `fk_route_orders_order` (`order_id`),
  CONSTRAINT `fk_route_orders_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_route_orders_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- route_transfer_history
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `route_transfer_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `from_route_id` int(10) unsigned NOT NULL,
  `to_route_id` int(10) unsigned NOT NULL,
  `from_delivery_order` int(11) DEFAULT NULL,
  `to_delivery_order` int(11) DEFAULT NULL,
  `transferred_by` int(11) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_route_transfer_order` (`order_id`),
  KEY `fk_rt_from_route` (`from_route_id`),
  KEY `fk_rt_to_route` (`to_route_id`),
  KEY `fk_rt_transferred_by` (`transferred_by`),
  CONSTRAINT `fk_rt_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rt_from_route` FOREIGN KEY (`from_route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rt_to_route` FOREIGN KEY (`to_route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rt_transferred_by` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- warehouse_preparation_history
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `warehouse_preparation_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_wph_order` (`order_id`),
  KEY `fk_wph_user` (`user_id`),
  CONSTRAINT `fk_wph_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wph_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- delivery_attempts
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `delivery_attempts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `route_id` int(10) unsigned DEFAULT NULL,
  `route_order_id` int(10) unsigned DEFAULT NULL,
  `courier_user_id` int(11) unsigned DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `failure_reason_id` int(10) unsigned DEFAULT NULL,
  `failure_other` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `attempted_at` datetime DEFAULT NULL,
  `received_by_name` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `reschedule_requested` tinyint(1) NOT NULL DEFAULT 0,
  `rescheduled_date` date DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_delivery_attempts_order` (`order_id`),
  KEY `idx_delivery_attempts_route` (`route_id`),
  KEY `idx_delivery_attempts_courier` (`courier_user_id`),
  KEY `idx_delivery_attempts_failure_reason` (`failure_reason_id`),
  KEY `fk_da_route_order` (`route_order_id`),
  CONSTRAINT `fk_da_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_da_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_da_route_order` FOREIGN KEY (`route_order_id`) REFERENCES `route_orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_da_courier` FOREIGN KEY (`courier_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_da_failure_reason` FOREIGN KEY (`failure_reason_id`) REFERENCES `delivery_failure_reasons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- order_status_history
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `from_status` varchar(30) DEFAULT NULL,
  `to_status` varchar(30) NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `courier_user_id` int(11) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `route_id` int(10) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_status_history_order` (`order_id`),
  KEY `idx_order_status_history_created` (`created_at`),
  KEY `fk_osh_user` (`user_id`),
  KEY `fk_osh_courier` (`courier_user_id`),
  KEY `fk_osh_route` (`route_id`),
  CONSTRAINT `fk_osh_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_osh_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_osh_courier` FOREIGN KEY (`courier_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_osh_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- attachments
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attachments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `delivery_attempt_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'evidencia',
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` bigint(20) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_attachments_order` (`order_id`),
  KEY `fk_attachments_attempt` (`delivery_attempt_id`),
  KEY `fk_attachments_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_attachments_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attachments_attempt` FOREIGN KEY (`delivery_attempt_id`) REFERENCES `delivery_attempts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_attachments_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- payments
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `delivery_attempt_id` int(10) unsigned DEFAULT NULL,
  `payment_method_id` int(10) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `received_at` datetime NOT NULL DEFAULT current_timestamp(),
  `received_by` int(11) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payments_order` (`order_id`),
  KEY `fk_payments_attempt` (`delivery_attempt_id`),
  KEY `fk_payments_method` (`payment_method_id`),
  KEY `fk_payments_received_by` (`received_by`),
  CONSTRAINT `fk_payments_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_attempt` FOREIGN KEY (`delivery_attempt_id`) REFERENCES `delivery_attempts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_payments_method` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`),
  CONSTRAINT `fk_payments_received_by` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- payment_history
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `from_payment_method_id` int(10) unsigned DEFAULT NULL,
  `to_payment_method_id` int(10) unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `previous_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `new_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `user_id` int(11) unsigned DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_payment_history_order` (`order_id`),
  KEY `fk_ph_order` (`order_id`),
  KEY `fk_ph_from_method` (`from_payment_method_id`),
  KEY `fk_ph_to_method` (`to_payment_method_id`),
  CONSTRAINT `fk_ph_order` FOREIGN KEY (`order_id`) REFERENCES `store_orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ph_from_method` FOREIGN KEY (`from_payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_ph_to_method` FOREIGN KEY (`to_payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- cash_deposits / cash_deposit_routes
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cash_deposits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `courier_user_id` int(11) unsigned NOT NULL,
  `receiver_user_id` int(11) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `route_count` int(11) NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'pendiente',
  `notes` text DEFAULT NULL,
  `courier_receipt_file` varchar(255) DEFAULT NULL,
  `courier_receipt_original` varchar(255) DEFAULT NULL,
  `courier_receipt_mime` varchar(100) DEFAULT NULL,
  `courier_receipt_size` bigint(20) DEFAULT NULL,
  `courier_receipt_at` datetime DEFAULT NULL,
  `aux_confirmed_by` int(11) unsigned DEFAULT NULL,
  `aux_confirmed_at` datetime DEFAULT NULL,
  `admin_receiver_user_id` int(11) unsigned DEFAULT NULL,
  `aux_receipt_file` varchar(255) DEFAULT NULL,
  `aux_receipt_original` varchar(255) DEFAULT NULL,
  `aux_receipt_mime` varchar(100) DEFAULT NULL,
  `aux_receipt_size` bigint(20) DEFAULT NULL,
  `aux_receipt_at` datetime DEFAULT NULL,
  `confirmed_by` int(11) unsigned DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cd_courier` (`courier_user_id`),
  KEY `idx_cd_receiver` (`receiver_user_id`),
  KEY `idx_cd_status` (`status`),
  KEY `idx_cd_created` (`created_at`),
  KEY `fk_cd_country` (`country_id`),
  CONSTRAINT `fk_cd_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_cd_courier` FOREIGN KEY (`courier_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cash_deposit_routes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `deposit_id` bigint(20) unsigned NOT NULL,
  `route_id` int(10) unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_cdr_route` (`route_id`),
  KEY `idx_cdr_deposit` (`deposit_id`),
  CONSTRAINT `fk_cdr_deposit` FOREIGN KEY (`deposit_id`) REFERENCES `cash_deposits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cdr_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- permissions / group_permissions
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_permissions_name` (`name`),
  KEY `idx_permissions_module` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `module`, `action`, `description`, `is_active`) VALUES
  (1,'usuarios.ver','usuarios','ver','Ver usuarios',1),
  (2,'usuarios.crear','usuarios','crear','Crear usuarios',1),
  (3,'usuarios.editar','usuarios','editar','Editar usuarios',1),
  (4,'usuarios.eliminar','usuarios','eliminar','Eliminar/desactivar usuarios',1),
  (5,'clientes.ver','clientes','ver','Ver clientes',1),
  (6,'clientes.crear','clientes','crear','Crear clientes',1),
  (7,'clientes.editar','clientes','editar','Editar clientes',1),
  (8,'clientes.eliminar','clientes','eliminar','Eliminar/desactivar clientes',1),
  (9,'bodegas.ver','bodegas','ver','Ver bodegas',1),
  (10,'bodegas.crear','bodegas','crear','Crear bodegas',1),
  (11,'bodegas.editar','bodegas','editar','Editar bodegas',1),
  (12,'bodegas.eliminar','bodegas','eliminar','Eliminar bodegas',1),
  (13,'productos.ver','productos','ver','Ver productos',1),
  (14,'productos.crear','productos','crear','Crear productos',1),
  (15,'productos.editar','productos','editar','Editar productos',1),
  (16,'productos.eliminar','productos','eliminar','Eliminar productos',1),
  (17,'pedidos.ver','pedidos','ver','Ver pedidos',1),
  (18,'pedidos.ver_todos','pedidos','ver_todos','Ver pedidos de todos los vendedores',1),
  (19,'pedidos.crear','pedidos','crear','Crear pedidos',1),
  (20,'pedidos.editar','pedidos','editar','Editar pedidos',1),
  (21,'pedidos.anular','pedidos','anular','Anular pedidos',1),
  (22,'pedidos.preparar','pedidos','preparar','Preparar pedidos en bodega',1),
  (23,'pedidos.modificar_precio','pedidos','modificar_precio','Modificar precios dentro del pedido',1),
  (24,'pedidos.aplicar_descuento','pedidos','aplicar_descuento','Aplicar descuentos',1),
  (25,'rutas.ver','rutas','ver','Ver rutas',1),
  (26,'rutas.crear','rutas','crear','Crear rutas',1),
  (27,'rutas.editar','rutas','editar','Editar rutas',1),
  (28,'rutas.asignar','rutas','asignar','Asignar mensajeros a rutas',1),
  (29,'rutas.reordenar','rutas','reordenar','Reordenar pedidos de una ruta',1),
  (30,'rutas.transferir_pedido','rutas','transferir_pedido','Transferir pedidos entre rutas',1),
  (31,'entregas.ver','entregas','ver','Ver entregas',1),
  (32,'entregas.actualizar_estado','entregas','actualizar_estado','Actualizar estado de entrega',1),
  (33,'entregas.registrar_pago','entregas','registrar_pago','Registrar pagos',1),
  (34,'entregas.subir_evidencia','entregas','subir_evidencia','Subir evidencias',1),
  (35,'entregas.reprogramar','entregas','reprogramar','Reprogramar entregas',1),
  (36,'reportes.ver','reportes','ver','Ver reportes',1),
  (37,'reportes.exportar','reportes','exportar','Exportar reportes a PDF/Excel',1),
  (38,'auditoria.ver','auditoria','ver','Ver bitacora de auditoria',1),
  (39,'configuracion.editar','configuracion','editar','Editar configuracion del sistema',1),
  (40,'pedidos.eliminar','pedidos','eliminar','Eliminar pedidos',1),
  (41,'dashboard.ver','dashboard','ver','Ver el dashboard principal',1),
  (42,'depositos.ver','depositos','ver','Ver depositos de efectivo',1),
  (43,'depositos.confirmar','depositos','confirmar','Confirmar recepcion de depositos del mensajero',1),
  (44,'depositos.entregar','depositos','entregar','Entregar efectivo al administrador con comprobante',1),
  (45,'depositos.aprobar','depositos','aprobar','Visto bueno final de depositos',1)
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

CREATE TABLE IF NOT EXISTS `group_permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_group_permissions` (`group_id`,`permission_id`),
  KEY `fk_group_permissions_group` (`group_id`),
  KEY `fk_group_permissions_permission` (`permission_id`),
  CONSTRAINT `fk_group_permissions_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_group_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- admin: todos los permisos
INSERT INTO `group_permissions` (`group_id`, `permission_id`)
SELECT g.`id`, p.`id`
FROM `groups` g
CROSS JOIN `permissions` p
WHERE g.`name` = 'admin'
  AND NOT EXISTS (
    SELECT 1 FROM `group_permissions` gp
    WHERE gp.`group_id` = g.`id` AND gp.`permission_id` = p.`id`
  );

-- vendedor
INSERT INTO `group_permissions` (`group_id`, `permission_id`)
SELECT g.`id`, p.`id`
FROM `groups` g
JOIN `permissions` p ON p.`name` IN (
  'dashboard.ver','pedidos.ver','pedidos.ver_todos','pedidos.crear','pedidos.editar',
  'pedidos.modificar_precio','pedidos.aplicar_descuento','clientes.ver','clientes.crear',
  'clientes.editar','productos.ver','bodegas.ver','entregas.ver','reportes.ver'
)
WHERE g.`name` = 'vendedor'
  AND NOT EXISTS (
    SELECT 1 FROM `group_permissions` gp
    WHERE gp.`group_id` = g.`id` AND gp.`permission_id` = p.`id`
  );

-- bodeguero
INSERT INTO `group_permissions` (`group_id`, `permission_id`)
SELECT g.`id`, p.`id`
FROM `groups` g
JOIN `permissions` p ON p.`name` IN (
  'dashboard.ver','pedidos.ver','pedidos.preparar','productos.ver','bodegas.ver',
  'entregas.ver','clientes.ver'
)
WHERE g.`name` = 'bodeguero'
  AND NOT EXISTS (
    SELECT 1 FROM `group_permissions` gp
    WHERE gp.`group_id` = g.`id` AND gp.`permission_id` = p.`id`
  );

-- mensajero
INSERT INTO `group_permissions` (`group_id`, `permission_id`)
SELECT g.`id`, p.`id`
FROM `groups` g
JOIN `permissions` p ON p.`name` IN (
  'dashboard.ver','rutas.ver','entregas.ver','entregas.actualizar_estado',
  'entregas.registrar_pago','entregas.subir_evidencia','entregas.reprogramar',
  'depositos.ver','depositos.entregar'
)
WHERE g.`name` = 'mensajero'
  AND NOT EXISTS (
    SELECT 1 FROM `group_permissions` gp
    WHERE gp.`group_id` = g.`id` AND gp.`permission_id` = p.`id`
  );

-- auxiliar_admin: apoyo operativo
INSERT INTO `group_permissions` (`group_id`, `permission_id`)
SELECT g.`id`, p.`id`
FROM `groups` g
JOIN `permissions` p ON p.`name` IN (
  'dashboard.ver','pedidos.ver','pedidos.ver_todos','pedidos.preparar','entregas.ver',
  'rutas.ver','clientes.ver','productos.ver','bodegas.ver','depositos.ver',
  'depositos.confirmar','reportes.ver','reportes.exportar'
)
WHERE g.`name` = 'auxiliar_admin'
  AND NOT EXISTS (
    SELECT 1 FROM `group_permissions` gp
    WHERE gp.`group_id` = g.`id` AND gp.`permission_id` = p.`id`
  );

-- -------------------------------------------------------------
-- audit_logs
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  `table_name` varchar(60) DEFAULT NULL,
  `record_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_logs_user` (`user_id`),
  KEY `idx_audit_logs_action` (`action`),
  KEY `idx_audit_logs_created` (`created_at`),
  KEY `idx_audit_logs_record` (`table_name`,`record_id`),
  KEY `fk_audit_logs_country` (`country_id`),
  CONSTRAINT `fk_audit_logs_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- system_settings
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned NOT NULL,
  `key` varchar(80) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uc_system_settings_country_key` (`country_id`,`key`),
  KEY `fk_system_settings_country` (`country_id`),
  CONSTRAINT `fk_system_settings_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_visits (contador de visitas de la tienda web)
-- Una fila por vista de página; los visitantes únicos se calculan
-- por session_id distinto en una fecha.
-- -------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `store_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `session_id` varchar(64) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `visit_date` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sv_date` (`visit_date`),
  KEY `idx_sv_country_date` (`country_id`,`visit_date`),
  KEY `idx_sv_session` (`session_id`),
  CONSTRAINT `fk_sv_country` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------------------------------------------------
-- store_orders: columnas de origen, saldo, logistica y geo
-- -------------------------------------------------------------
ALTER TABLE `store_orders`
  ADD COLUMN IF NOT EXISTS `client_id` int(10) unsigned DEFAULT NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `origin` varchar(20) NOT NULL DEFAULT 'web' AFTER `order_number`,
  ADD COLUMN IF NOT EXISTS `seller_user_id` int(11) unsigned DEFAULT NULL AFTER `client_id`,
  ADD COLUMN IF NOT EXISTS `warehouse_id` int(10) unsigned DEFAULT NULL AFTER `origin`,
  ADD COLUMN IF NOT EXISTS `delivery_reference` text DEFAULT NULL AFTER `delivery_address`,
  ADD COLUMN IF NOT EXISTS `map_url` text DEFAULT NULL AFTER `delivery_reference`,
  ADD COLUMN IF NOT EXISTS `latitude` decimal(10,8) DEFAULT NULL AFTER `map_url`,
  ADD COLUMN IF NOT EXISTS `longitude` decimal(11,8) DEFAULT NULL AFTER `latitude`,
  ADD COLUMN IF NOT EXISTS `discount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `shipping`,
  ADD COLUMN IF NOT EXISTS `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `total`,
  ADD COLUMN IF NOT EXISTS `balance_amount` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `paid_amount`,
  ADD COLUMN IF NOT EXISTS `includes_gifts` tinyint(1) NOT NULL DEFAULT 0 AFTER `balance_amount`,
  ADD COLUMN IF NOT EXISTS `gift_description` text DEFAULT NULL AFTER `includes_gifts`,
  ADD COLUMN IF NOT EXISTS `is_parcel` tinyint(1) NOT NULL DEFAULT 0 AFTER `gift_description`,
  ADD COLUMN IF NOT EXISTS `transport_id` int(10) unsigned DEFAULT NULL AFTER `is_parcel`,
  ADD COLUMN IF NOT EXISTS `requested_delivery_date` date DEFAULT NULL AFTER `transport_id`,
  ADD COLUMN IF NOT EXISTS `rescheduled_delivery_date` date DEFAULT NULL AFTER `requested_delivery_date`,
  ADD COLUMN IF NOT EXISTS `customer_phone_intl` varchar(20) DEFAULT NULL AFTER `customer_phone2`,
  ADD COLUMN IF NOT EXISTS `customer_phone_whatsapp` varchar(20) DEFAULT NULL AFTER `customer_phone_intl`,
  ADD COLUMN IF NOT EXISTS `customer_phone2_intl` varchar(20) DEFAULT NULL AFTER `customer_phone_whatsapp`,
  ADD COLUMN IF NOT EXISTS `customer_phone2_whatsapp` varchar(20) DEFAULT NULL AFTER `customer_phone2_intl`,
  ADD COLUMN IF NOT EXISTS `created_by` int(11) unsigned DEFAULT NULL AFTER `updated_at`,
  ADD COLUMN IF NOT EXISTS `updated_by` int(11) unsigned DEFAULT NULL AFTER `created_by`;

-- Indices de soporte
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND INDEX_NAME = 'idx_store_orders_client');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `store_orders` ADD KEY `idx_store_orders_client` (`client_id`)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND INDEX_NAME = 'idx_store_orders_origin');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `store_orders` ADD KEY `idx_store_orders_origin` (`origin`)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND INDEX_NAME = 'idx_store_orders_seller');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `store_orders` ADD KEY `idx_store_orders_seller` (`seller_user_id`)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND INDEX_NAME = 'idx_store_orders_warehouse');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `store_orders` ADD KEY `idx_store_orders_warehouse` (`warehouse_id`)', 'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Claves foraneas nuevas (con guardas)
SET @fk = 'fk_store_orders_client';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = 'fk_store_orders_seller';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_seller` FOREIGN KEY (`seller_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = 'fk_store_orders_warehouse';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = 'fk_store_orders_transport';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_transport` FOREIGN KEY (`transport_id`) REFERENCES `transports` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = 'fk_store_orders_created_by';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk = 'fk_store_orders_updated_by';
SET @exists = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'store_orders' AND CONSTRAINT_NAME = @fk);
SET @sql = IF(@exists = 0,
  'ALTER TABLE `store_orders` ADD CONSTRAINT `fk_store_orders_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE',
  'DO 0');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- -------------------------------------------------------------
-- store_orders.user_id opcional: los pedidos manuales del admin pueden
-- no tener un usuario de tienda asociado (cliente sin cuenta).
-- -------------------------------------------------------------
ALTER TABLE `store_orders`
  MODIFY COLUMN `user_id` int(11) unsigned DEFAULT NULL;

-- -------------------------------------------------------------
-- store_order_items: descripcion y linea manual
-- -------------------------------------------------------------
ALTER TABLE `store_order_items`
  ADD COLUMN IF NOT EXISTS `description` text DEFAULT NULL AFTER `item_name`,
  ADD COLUMN IF NOT EXISTS `is_manual` tinyint(1) NOT NULL DEFAULT 0 AFTER `line_total`;

-- -------------------------------------------------------------
-- Normaliza pedidos web antiguos con estado 'confirmado' al estado
-- inicial 'registrado', igual que los pedidos manuales.
-- -------------------------------------------------------------
UPDATE `store_orders` SET `status` = 'registrado', `updated_at` = NOW()
WHERE `origin` = 'web' AND `status` = 'confirmado';

-- -------------------------------------------------------------
-- Semillas: turnos, motivos, bodegas, transportes, configuracion
-- -------------------------------------------------------------
INSERT INTO `payment_methods` (`country_id`, `code`, `name`, `is_active`, `sort_order`)
SELECT c.`id`, t.`code`, t.`name`, 1, t.`sort_order`
FROM `countries` c
JOIN (
    SELECT 'efectivo' AS `code`, 'Efectivo' AS `name`, 1 AS `sort_order`
    UNION ALL SELECT 'transferencia_sinpe', 'Transferencia / SINPE Móvil', 2
    UNION ALL SELECT 'tarjeta', 'Tarjeta', 3
    UNION ALL SELECT 'pendiente_credito', 'Pendiente de pago / Crédito', 4
) t
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_methods` d
    WHERE d.`country_id` = c.`id` AND d.`code` = t.`code`
);

INSERT INTO `route_shifts` (`country_id`, `code`, `name`, `start_time`, `end_time`, `is_active`, `sort_order`)
SELECT c.`id`, t.`code`, t.`name`, t.`start_time`, t.`end_time`, 1, t.`sort_order`
FROM `countries` c
JOIN (
    SELECT 'manana' AS `code`, 'Mañana' AS `name`, '06:00:00' AS `start_time`, '12:00:00' AS `end_time`, 1 AS `sort_order`
    UNION ALL SELECT 'tarde', 'Tarde', '12:00:00', '20:00:00', 2
) t
WHERE NOT EXISTS (
    SELECT 1 FROM `route_shifts` d
    WHERE d.`country_id` = c.`id` AND d.`code` = t.`code`
);

INSERT INTO `delivery_failure_reasons` (`country_id`, `code`, `name`, `requires_description`, `is_active`, `sort_order`)
SELECT c.`id`, t.`code`, t.`name`, t.`requires_description`, 1, t.`sort_order`
FROM `countries` c
JOIN (
    SELECT 'cliente_ausente' AS `code`, 'Cliente ausente' AS `name`, 0 AS `requires_description`, 1 AS `sort_order`
    UNION ALL SELECT 'direccion_incorrecta', 'Dirección incorrecta o no encontrada', 0, 2
    UNION ALL SELECT 'cliente_rechazo', 'Cliente rechazó el pedido', 0, 3
    UNION ALL SELECT 'producto_danado', 'Producto dañado o incompleto', 0, 4
    UNION ALL SELECT 'zona_peligrosa', 'Zona peligrosa o acceso restringido', 0, 5
    UNION ALL SELECT 'vehiculo_averiado', 'Vehículo averiado', 0, 6
    UNION ALL SELECT 'otro', 'Otro', 1, 7
) t
WHERE NOT EXISTS (
    SELECT 1 FROM `delivery_failure_reasons` d
    WHERE d.`country_id` = c.`id` AND d.`code` = t.`code`
);

INSERT INTO `transports` (`country_id`, `code`, `name`, `is_active`, `sort_order`)
SELECT c.`id`, t.`code`, t.`name`, 1, t.`sort_order`
FROM `countries` c
JOIN (
    SELECT 'taxi' AS `code`, 'Taxi' AS `name`, 10 AS `sort_order`
    UNION ALL SELECT 'moto', 'Motocicleta', 20
    UNION ALL SELECT 'bus', 'Bus intermunicipal', 30
) t
WHERE NOT EXISTS (
    SELECT 1 FROM `transports` d
    WHERE d.`country_id` = c.`id` AND d.`code` = t.`code`
);

INSERT INTO `warehouses` (`country_id`, `code`, `name`, `address`, `phone`, `latitude`, `longitude`, `directions`, `is_active`, `is_default`)
SELECT c.`id`, 'BOD-001',
    CONCAT('Bodega Central ', c.`name`),
    '', '', NULL, NULL, '', 1, 1
FROM `countries` c
WHERE NOT EXISTS (
    SELECT 1 FROM `warehouses` d
    WHERE d.`country_id` = c.`id` AND d.`code` = 'BOD-001'
);

INSERT INTO `system_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, t.`key`, t.`value`, t.`description`
FROM `countries` c
JOIN (
    SELECT 'company_name' AS `key`, 'SG Tienda' AS `value`, 'Nombre de la empresa mostrado en reportes' AS `description`
    UNION ALL SELECT 'company_phone', '', 'Teléfono de contacto de la empresa'
    UNION ALL SELECT 'company_email', '', 'Correo de contacto de la empresa'
    UNION ALL SELECT 'company_address', '', 'Dirección de la empresa'
    UNION ALL SELECT 'evidence_max_size_mb', '8', 'Tamaño máximo de evidencia en MB'
    UNION ALL SELECT 'orders_display_time', '12', 'Formato de hora visible: 12 o 24'
    UNION ALL SELECT 'low_stock_threshold', '5', 'Existencias a partir de las cuales se alerta stock bajo'
) t
WHERE NOT EXISTS (
    SELECT 1 FROM `system_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = t.`key`
);

INSERT INTO `system_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, 'country_code', c.`phone_code`, 'Código internacional telefónico por defecto'
FROM `countries` c
WHERE NOT EXISTS (
    SELECT 1 FROM `system_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = 'country_code'
);

INSERT INTO `system_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, 'timezone', c.`timezone`, 'Zona horaria del sistema'
FROM `countries` c
WHERE NOT EXISTS (
    SELECT 1 FROM `system_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = 'timezone'
);

INSERT INTO `system_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, 'currency', c.`currency`, 'Moneda'
FROM `countries` c
WHERE NOT EXISTS (
    SELECT 1 FROM `system_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = 'currency'
);

INSERT INTO `system_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, 'currency_symbol', c.`currency_symbol`, 'Símbolo monetario'
FROM `countries` c
WHERE NOT EXISTS (
    SELECT 1 FROM `system_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = 'currency_symbol'
);

-- -------------------------------------------------------------
-- Administrador de El Salvador (misma contraseña temporal que el
-- admin base de Costa Rica). La tienda opera por defecto en SV.
-- -------------------------------------------------------------
INSERT INTO `users` (`country_id`, `ip_address`, `username`, `password`, `email`, `created_on`, `active`, `first_name`, `last_name`, `company`, `phone`)
SELECT c.`id`, '127.0.0.1', 'admin_sv', src.`password`, 'admin@elsalvador.local', UNIX_TIMESTAMP(), 1, 'Admin', 'El Salvador', 'SG Tienda', '00000000'
FROM `countries` c
CROSS JOIN (
    SELECT u.`password`
    FROM `users` u
    JOIN `countries` cu ON cu.`id` = u.`country_id`
    WHERE cu.`code` = 'CR'
    ORDER BY u.`id` ASC
    LIMIT 1
) src
WHERE c.`code` = 'SV'
  AND NOT EXISTS (SELECT 1 FROM `users` x WHERE x.`email` = 'admin@elsalvador.local');

INSERT INTO `users_groups` (`user_id`, `group_id`)
SELECT u.`id`, g.`id`
FROM `users` u
JOIN `groups` g ON g.`name` = 'admin'
WHERE u.`email` = 'admin@elsalvador.local'
  AND NOT EXISTS (
    SELECT 1 FROM `users_groups` x
    WHERE x.`user_id` = u.`id` AND x.`group_id` = g.`id`
  );

SET FOREIGN_KEY_CHECKS = 1;
