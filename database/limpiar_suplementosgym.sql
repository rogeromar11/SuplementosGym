-- =============================================================
-- SuplementosGym — Limpiar base de datos
-- -------------------------------------------------------------
-- Borra TODAS las tablas de la aplicación y deja la base vacía.
-- Úsalo solo si quieres reiniciar desde cero; después ejecuta
-- suplementosgym.sql para volver a crear el esquema y los datos.
--
-- Uso:
--   mysql -u root suplementosgym < limpiar_suplementosgym.sql
--
-- Si además quieres eliminar la base de datos completa, descomenta
-- la siguiente línea (y ejecuta sin especificar la base):
--   DROP DATABASE IF EXISTS `suplementosgym`;
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `cash_deposit_routes`;
DROP TABLE IF EXISTS `cash_deposits`;
DROP TABLE IF EXISTS `product_images`;
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
