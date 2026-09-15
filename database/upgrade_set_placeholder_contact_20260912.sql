-- =============================================================
-- Migracion: numeros de WhatsApp (placeholders) por pais
-- Fecha: 2026-09-12
-- -------------------------------------------------------------
-- IMPORTANTE: son numeros de EJEMPLO para que el boton flotante y
-- "Finalizar compra por WhatsApp" funcionen en desarrollo.
-- Reemplazar por los numeros reales de la tienda antes de publicar.
-- =============================================================

UPDATE `store_settings`
SET `value` = '50688888888'
WHERE `country_id` = 1 AND `key` = 'whatsapp_number' AND (`value` IS NULL OR `value` = '');

UPDATE `store_settings`
SET `value` = '50377777777'
WHERE `country_id` = 2 AND `key` = 'whatsapp_number' AND (`value` IS NULL OR `value` = '');

UPDATE `store_settings`
SET `value` = 'Lun a Sab · 9:00 - 19:00'
WHERE `key` = 'business_hours' AND (`value` IS NULL OR `value` = '');
