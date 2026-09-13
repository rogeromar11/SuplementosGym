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

### Added — Contenido y profesionalizacion (2026-09-12)
- Logo de la tienda igual al de SGMensajeria (`assets/img/logo.png`).
- Guia de suplementos en el home y pagina `/guia` (contenido por categoria:
  proteina, creatina, preentreno, quemador, ganador y otros; beneficio y uso).
- Seccion "¿Cual es tu objetivo?" con recomendaciones segun meta.
- Preguntas frecuentes (acordeon) en home y contacto.
- Paginas `nosotros`, `formas-de-pago` y `contacto` redisenadas con tono
  profesional (proceso, valores, descripcion por metodo de pago).
- Funciones de contenido en `store_helper`: `store_supplement_guides()`,
  `store_goals()`, `store_faqs()`.

### Changed — Rediseño total del sistema de diseño (v2)
- `assets/css/store.css` reescrito por completo: estetica deportiva/premium.
- Hero renovado (fondo con rejilla y glow rojo, eyebrow animado, visual con
  badges flotantes) y marquee/ticker de categorias.
- Botones pill con gradiente rojo, sombras y hover con elevacion.
- Navbar con blur, subrayado animado y contador de carrito redisenado.
- Tarjetas de producto, categorias (tiles con icono), objetivos (dark cards),
  guia, beneficios, proceso, FAQ y footer redisenados.
- Marcado de vistas actualizado para el nuevo sistema (bicon, section-eyebrow,
  add-btn/wa-btn, category-tile, account-pill).

### Changed — Rediseño corporativo (v3, estilo tipo BAC Credomatic)
- `assets/css/store.css` reescrito a un tema limpio/corporativo: header blanco,
  fondo claro, tarjetas planas con bordes sutiles, boton rojo solido y
  tipografia sobria.
- Header reestructurado con **barra utilitaria superior** (contacto + selector
  de pais con banderas) y **navbar blanco**.
- Hero claro (se elimino el fondo oscuro, el glow y el marquee).
- Secciones con fondo alterno, tarjetas blancas y sombras suaves.
- Se mantienen todos los `data-*` del JS (carrito, pais, busqueda, acordeon).

### Changed — Hero oscuro restaurado
- La seccion "Entrena duro. Recupera mejor." vuelve a fondo negro, manteniendo
  el resto del estilo corporativo.

### Added — Carga de imagenes de producto (2026-09-12)
- 54/54 productos con imagen. 20 combinaciones (marca+producto) descargadas
  desde Open Food Facts; 2 (creatina y CLA de Nutrex) con placeholder SVG.
- Imagenes guardadas en `uploads/products/` y referenciadas en `products.image`.
- **Nota:** imagenes solo para maqueta/pruebas; validar licencia o sustituir por
  fotos propias antes de produccion.

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
- Deprecaciones PHP 8.1 por pasar `null` a funciones que esperan string:
  `nl2br()` en `store/product.php` y `store/account/order.php`, y
  `strcasecmp()` en el filtro de laboratorio (`store/products.php`).
  Se fuerzan tipos string en `Store::products()` y `Product_model::catalog()`.
- `inventory_applied` se marca a `1` al aplicar inventario; `apply_for_order`
  es idempotente (evita doble descuento).
- `Store_cart::add()` rechaza cantidades que superan el stock (no recorta).

## Notas
- No se modifico SGMensajeria.
- La base quedo en estado limpio: 54 productos (SV), 0 pedidos, 0 movimientos.
- Sin datos reales de contacto: se usan placeholders configurables.
