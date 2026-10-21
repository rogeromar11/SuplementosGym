-- =============================================================
-- Migracion: gastos adicionales del mensajero en la entrega
-- Fecha: 2026-10-19
-- -------------------------------------------------------------
-- Agrega a delivery_attempts el monto de "Otros gastos" (gastos
-- adicionales de la ruta: combustible, parqueo, transporte, etc.)
-- y el detalle de en que se gasto.
--
-- Uso:
--   mysql -u root sgmensajeria_web < upgrade_add_delivery_expenses_20261019.sql
-- =============================================================

ALTER TABLE `delivery_attempts`
  ADD COLUMN IF NOT EXISTS `extra_expense` decimal(15,2) NOT NULL DEFAULT 0.00 AFTER `longitude`,
  ADD COLUMN IF NOT EXISTS `extra_expense_detail` text DEFAULT NULL AFTER `extra_expense`;
