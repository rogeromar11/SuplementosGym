-- =============================================================
-- Migracion: sabor del item en el pedido
-- Fecha: 2026-09-12
-- -------------------------------------------------------------
-- Guarda el sabor seleccionado en store_order_items para mostrarlo
-- en el carrito, checkout y "Mis pedidos".
-- =============================================================

ALTER TABLE `store_order_items`
    ADD COLUMN IF NOT EXISTS `item_flavor` varchar(120) DEFAULT NULL AFTER `item_name`;
