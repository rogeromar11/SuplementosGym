# CHANGELOG

Todos los cambios relevantes de SG Tienda / SuplementosGym.

Formato: `Added`, `Changed`, `Fixed`, `Security`.

## 2026-09-12

### Added — Fase 1 (Auditoria)
- Auditoria completa de `C:/xampp/htdocs/SGMensajeria`.
- Documentacion viva `openscpect/` (17 documentos).
- Decisiones: codigo en SuplementosGym, base origen `sgmensajeria`, copia solo SV,
  base destino `suplementosgym`, contacto como placeholders.

### Added — Fase 2 (Estructura)
- Base `suplementosgym` (MariaDB 10.4, `utf8mb4_unicode_ci`).
- `database/database.sql` reescrito como esquema base completo.
- `database/upgrade_add_store_schema_20260912.sql` (migracion idempotente).
- Tablas: `countries`, `products`, `payment_methods`, `store_orders`,
  `store_order_items`, `inventory_movements`, `store_settings` (+ IonAuth).
- Campos de tienda en `products` y en `users`.

### Added — Fase 3 (Migracion)
- `database/upgrade_migrate_initial_data_20260912.sql`.
- Copiados 8 metodos de pago, 54 productos (El Salvador) y 10 `store_settings`.

### Added — Fases 5-15 (Aplicacion SG Tienda)
- Nucleo: `application/core/MY_Controller.php`.
- Helper `application/helpers/store_helper.php`.
- Modelos: `Country_model`, `Product_model`, `Store_order_model`, `Inventory_model`.
- Libreria `Store_cart`.
- Controladores: `Store`, `Cart`, `Checkout`, `Account`, `Store_auth`.
- Vistas en `application/views/store/` (home, catalogo, producto, carrito,
  checkout, about, pagos, contacto, cuenta y auth).
- Assets: `assets/css/store.css`, `assets/js/store.js`, placeholder SVG,
  Bootstrap 5 + Bootstrap Icons locales.
- Rutas amigables de la tienda.

### Added — Fase 17
- SEO (title/description/canonical/Open Graph), accesibilidad y responsive.

### Changed
- `application/config/database.php` apunta a `suplementosgym`.
- `ion_auth` config: `site_title` = "SG Tienda", `default_group` = `customer`.
- `application/config/config.php`: `base_url` robusto (host + puerto + subcarpeta)
  y `index_page` vacio para URLs amigables.

### Security
- CSRF en todos los POST, incluidos los AJAX del carrito.
- Validacion server-side de producto, pais, precio, cantidad y stock.
- Escapado de salida con `html_escape()`.
- Carpetas `uploads/products/` protegidas contra ejecucion.

### Fixed
- `inventory_applied` se marca a `1` al aplicar inventario; `apply_for_order`
  es idempotente (evita doble descuento).
- `Store_cart::add()` rechaza cantidades que superan el stock (no recorta).

## Notas
- No se modifico SGMensajeria.
- La base quedo en estado limpio: 54 productos (SV), 0 pedidos, 0 movimientos.
- Sin datos reales de contacto: se usan placeholders configurables.
