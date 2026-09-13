-- =============================================================
-- Migracion inicial de datos · SG Tienda (Fase 3)
-- Fecha: 2026-09-12
-- Destino: suplementosgym
-- Origen (solo lectura): sgmensajeria
-- -------------------------------------------------------------
-- Copia:
--   · payment_methods (todos los paises)
--   · products (solo country_id = 2 / El Salvador, activos)
--   · store_settings (placeholders configurables de contacto)
--
-- Idempotente: evita duplicados por (country_id, sku/code).
-- NO copia pedidos, clientes, rutas ni inventario de mensajeria.
-- =============================================================

-- -------------------------------------------------------------
-- Metodos de pago (por pais)
-- -------------------------------------------------------------
INSERT INTO `payment_methods` (`country_id`, `code`, `name`, `is_active`, `sort_order`)
SELECT s.`country_id`, s.`code`, s.`name`, s.`is_active`, s.`sort_order`
FROM `sgmensajeria`.`payment_methods` s
WHERE NOT EXISTS (
    SELECT 1 FROM `payment_methods` d
    WHERE d.`country_id` = s.`country_id` AND d.`code` = s.`code`
);

-- -------------------------------------------------------------
-- Productos (solo El Salvador, activos)
-- -------------------------------------------------------------
INSERT INTO `products` (
    `country_id`, `sku`, `product_type`, `laboratory`, `name`,
    `weight`, `servings`, `flavor`, `description`,
    `cost_price`, `unit_price`, `is_active`, `stock_enabled`, `stock_qty`,
    `created_at`, `updated_at`
)
SELECT
    p.`country_id`, p.`sku`, p.`product_type`, p.`laboratory`, p.`name`,
    p.`weight`, p.`servings`, p.`flavor`, p.`description`,
    p.`cost_price`, p.`unit_price`, p.`is_active`, p.`stock_enabled`, p.`stock_qty`,
    p.`created_at`, p.`updated_at`
FROM `sgmensajeria`.`products` p
WHERE p.`country_id` = 2
  AND p.`is_active` = 1
  AND NOT EXISTS (
    SELECT 1 FROM `products` d
    WHERE d.`country_id` = p.`country_id` AND d.`sku` = p.`sku`
  );

-- -------------------------------------------------------------
-- Configuracion de tienda por pais (placeholders, editar luego)
-- -------------------------------------------------------------
INSERT INTO `store_settings` (`country_id`, `key`, `value`, `description`)
SELECT c.`id`, t.`key`, t.`value`, t.`description`
FROM `countries` c
JOIN (
    SELECT 'CR' AS country_code, 'whatsapp_number' AS `key`, '' AS `value`, 'Numero de WhatsApp (con codigo de pais)' AS description
    UNION ALL SELECT 'CR', 'contact_email', '', 'Correo de contacto'
    UNION ALL SELECT 'CR', 'business_hours', '', 'Horario de atencion (texto libre)'
    UNION ALL SELECT 'CR', 'shipping_cost', '0', 'Costo de envio'
    UNION ALL SELECT 'CR', 'free_shipping_from', '0', 'Envio gratis a partir de (0 = desactivado)'
    UNION ALL SELECT 'SV', 'whatsapp_number', '', 'Numero de WhatsApp (con codigo de pais)'
    UNION ALL SELECT 'SV', 'contact_email', '', 'Correo de contacto'
    UNION ALL SELECT 'SV', 'business_hours', '', 'Horario de atencion (texto libre)'
    UNION ALL SELECT 'SV', 'shipping_cost', '0', 'Costo de envio'
    UNION ALL SELECT 'SV', 'free_shipping_from', '0', 'Envio gratis a partir de (0 = desactivado)'
) t ON t.country_code = c.`code`
WHERE NOT EXISTS (
    SELECT 1 FROM `store_settings` d
    WHERE d.`country_id` = c.`id` AND d.`key` = t.`key`
);
