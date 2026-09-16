# CHANGELOG

Todos los cambios relevantes de SG Tienda / SuplementosGym.

Formato: `Added`, `Changed`, `Fixed`, `Security`.

## 2026-09-15

### Added — Backoffice (staff)
- Base comun `SG_Controller` + `Authenticated_Controller` / `Admin_Controller` /
  `Courier_Controller`, con vistas por *package path* en `application/third_party/sgadmin/views/`
  (layouts `admin`, `courier`, `print`).
- Permisos granulares: `permissions` (45) + `group_permissions`; `Permission_service`,
  `has_permission()` / `require_permission()` y helper `permission_helper`.
- Grupos: `admin`, `customer`, `vendedor`, `bodeguero`, `mensajero`, `auxiliar_admin`.
- Modulos y controladores `admin/`: Auth, Dashboard, Users, Roles, Clients, Products,
  Warehouses, Catalogs, Orders, Preparation, Routes, Courier, Reports, Settings, Audit, Deposits.
- Servicios: `Order_service`, `Route_service`, `Delivery_service`, `Payment_service`,
  `Audit_service`, `Pdf_service`, `Excel_service`, `Map_link_parser`, `Client_import_service`,
  `Product_import_service`, `Sgms_phar_zip_archive`.
- Modelos de backoffice: `Order_model`, `Route_model`, `Client_model`, `Catalog_product_model`,
  `Warehouse_model`, `Deposit_model`, `Report_model`, `User_model`, `Settings_model`.
- Flujo de pedidos con `order_status_history`; preparacion, rutas, mensajeria movil y depositos.
- Reportes PDF/Excel, auditoria (`audit_logs`) y configuracion (`system_settings`/`store_settings`).
- `clients` con homologacion de usuarios registrados (`clients.user_id`).

### Changed — Base de datos
- `database/suplementosgym.sql` queda como **instalador unico** (33 tablas + datos iniciales).
  El instalador **no** siembra productos.
- `database/limpiar_suplementosgym.sql` reinicia la base (borra las 33 tablas).

### Changed — Documentacion
- Actualizados todos los documentos de `openscpect/` al estado real de la aplicacion.
- Nuevo `openscpect/ADMIN.md` (backoffice).
- `DEPLOYMENT.md` reescrito: PHP 8.2+, SQL real, entorno `CI_ENV`, HTTPS, uploads y hardening.
- `README.md` raiz reescrito (ya no es la plantilla IonAuth).

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
- Imagenes guardadas en `assets/img/products/` y referenciadas en `products.image`.
- **Nota:** imagenes solo para maqueta/pruebas; validar licencia o sustituir por
  fotos propias antes de produccion.

### Changed — Rediseno moderno v4 "Kinetic" + cPanel ready (2026-09-12)
- **Identidad visual liberada**: SGMensajeria ya no impone colores; ver `DESIGN.md`.
- `assets/css/store.css` reescrito por completo: tipografia Space Grotesk + Inter,
  paleta redisenada, radios 20px, sombras en capas y gradientes de marca.
- **Animaciones**: reveal con stagger al hacer scroll, contadores animados,
  navbar con blur que reacciona al scroll, parallax del hero, marquee de marcas,
  hovers con elevacion/zoom y modal/toast animados.
- **Fuentes autocontenidas**: descargadas a `assets/fonts/` + `assets/css/fonts.css`;
  se elimino Google Fonts del runtime (sin CDN).
- Imagenes de producto movidas a `assets/img/products/` (parte del tema).
- Header vuelve a navbar negro con selector de pais (banderas).
- Nuevos documentos `DESIGN.md` y `DEPLOYMENT.md` (publicacion en cPanel).

### Changed — Adaptacion del diseno "Suplementos Gym" (carpeta `Diseño`)
- Se adopta el storefront de `C:/xampp/htdocs/Diseño` manteniendo la
  funcionalidad actual (carrito, checkout, pedidos, inventario, cuentas).
- Tema base `assets/css/design.css` (copiado de `Diseño/css/style.css`) +
  capa funcional `assets/css/store.css`.
- Tipografia **Anton + Manrope** autocontenida en `assets/fonts/`.
- Hero con video (`assets/video/hero-web.mp4`), marquee, catalogo, promo,
  beneficios con contadores, tiendas, resenas y CTA final.
- Animaciones con **GSAP + ScrollTrigger** locales (`assets/vendor/gsap/`) y
  `assets/js/landing.js`; `store.js` conserva carrito/pais/busqueda.
- Selector de pais CR/SV en el header conectado a la sesion y a la base
  (`/pais`), con confirmacion si hay carrito.

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

### Added / Changed — Mejoras de catalogo (2026-09-12)
- **Dropdowns legibles**: `color-scheme: dark`, color de `option`, flecha
  personalizada y hover en los selects de categoria, laboratorio, orden y
  "mostrar" (`store.css`).
- **Carga de imagenes mas rapida**: miniaturas **WebP** (20 imagenes, ~31%
  mas livianas) servidas con `<picture>` + fallback JPG, `loading="lazy"`,
  `decoding="async"`, dimensiones explicitas y efecto *shimmer* con fade-in
  (`store_helper` `store_product_webp`, `product_card`, `store.js`).
- **Paginacion en productos**: 10 / 25 / 50 / 100 por pagina (default 25),
  con navegacion numerada, "mostrando X–Y de Z" y preservacion de filtros
  (`Store::products`, `store_products_url`, `products.php`).

### Added — WhatsApp: boton flotante y checkout por WhatsApp (2026-09-12)
- Boton flotante `.fab-wa` en la esquina inferior derecha, visible en todo el
  sitio (si no hay numero configurado, enlaza a Contacto).
- **Carrito**: boton "Finalizar por WhatsApp" que abre wa.me con el detalle
  escrito de los productos, cantidades, subtotal, envio y total
  (`store_cart_whatsapp_url()`), incluyendo los datos del cliente si hay sesion.
- **Checkout**: boton "Pedir por WhatsApp" que arma el mensaje con los productos
  y los datos de entrega escritos en el formulario (JS con `waCheckoutData`).
- `database/upgrade_set_placeholder_contact_20260912.sql`: numeros de WhatsApp
  **placeholder** (CR/SV) y horario, para activar estos botones. **Reemplazar
  por los numeros reales antes de publicar.**

### Changed — Optimizacion del navbar (2026-09-12)
- Se quito el boton de WhatsApp del navbar (se mantiene el boton flotante
  `.fab-wa` y los botones de producto/carrito/checkout).
- **Cuenta**: el icono de usuario ahora abre un **dropdown** con "Mi perfil",
  "Mis pedidos" y "Cerrar sesion" (invitados ven "Iniciar sesion").
- **Productos**: el enlace del navbar es un **dropdown con las categorias**
  (mas "Todos los productos") que llevan al catalogo filtrado.
- Dropdowns propios (`data-dropdown`, `bindDropdowns`) sin depender de Bootstrap:
  cierre al hacer clic fuera o con Escape.

### Added — Calculadora de macronutrientes (2026-09-12)
- Nueva pagina publica `/calculadora` (`Store::macros`, `store/macros.php`).
- Calcula calorias diarias (Mifflin-St Jeor + nivel de actividad + ajuste por
  objetivo) y el reparto de **proteinas, carbohidratos y grasas** (g y %), con
  barras visuales. Logica en `assets/js/calculator.js` (Vanilla, en el navegador).
- Incluye **explicacion clara para principiantes** (calorias, cada macro, como
  usar el resultado, consejos) y aviso de que es orientativo.
- Enlaces en el navbar, el menu movil y el footer.

### Changed — Tarjetas de producto mas limpias (2026-09-12)
- Se quito el boton de WhatsApp de cada tarjeta de producto; queda solo
  "Agregar al carrito" (el WhatsApp sigue en la ficha del producto, el boton
  flotante y el checkout).
- Se elimino la animacion de "reflejo"/shimmer permanente sobre la imagen del
  producto. Las imagenes se muestran directamente (sin depender de JS).

### Added — Variantes por sabor (2026-09-12)
- Los productos que comparten **tipo, laboratorio, nombre, peso y porciones**
  (considerando campos vacios) se agrupan como un mismo producto con varios
  **sabores** (`Product_model::variant_key()`, `group()`, `group_for()`).
- Catalogo y destacados muestran **una sola tarjeta por producto** con los
  sabores **informativos** (texto) y un solo boton de agregar al carrito.
- Al agregar un producto con varios sabores se abre una **vista previa (modal)**
  para elegir el sabor antes de agregar (muestra precio por sabor y deshabilita
  los agotados). El agregado se hace a la variante elegida.
- Ficha de producto: chips de sabor que llevan a la variante seleccionada.
- "Productos relacionados" y los contadores por categoria ahora usan grupos.

### Added — Sabor en carrito y pedido (2026-09-12)
- El **sabor seleccionado** se muestra en el carrito, el checkout y el detalle
  del pedido ("Mis pedidos").
- Se agrego la columna `store_order_items.item_flavor`
  (`database/upgrade_add_order_item_flavor_20260912.sql` + `database.sql`),
  y se guarda al crear el pedido (`Store_order_model::add_items`).
- El mensaje de **finalizar compra por WhatsApp** incluye el sabor de cada linea.

### Added — Categorias Aminos y Multivitaminicos (2026-09-12)
- Nuevas categorias **Aminos** (BCAA/EAA) y **Multivitaminicos** en
  `store_categories()` y su mapeo en `store_category()`.
- Se agregaron guias de ambas en `store_supplement_guides()` y los iconos en
  las tarjetas del home. Aparecen en el filtro de productos y el footer.

### Added — Redes sociales en el inicio (2026-09-12)
- Bloque de **redes sociales clickeables** (Instagram, Facebook, TikTok) junto a
  las estadisticas del hero (24-48 h, 100%, marcas, CR+SV).
- Enlaces configurables por pais en `store_settings` (`instagram_url`,
  `facebook_url`, `tiktok_url`) via `store_social_links()`; el footer usa los
  mismos. **Placeholders actuales**: se deben reemplazar por los perfiles reales.

### Fixed — Montos del resumen legibles (2026-09-12)
- Los montos de **Subtotal** y **Envio** (carrito y finalizar compra) se veian
  "borrosos" por usar la fuente display **Anton** en tamaño pequeno; ahora usan
  la fuente de texto **Manrope** en negrita y color blanco (mas nitidos).

## Notas
- No se modifico SGMensajeria.
- La base quedo en estado limpio: 54 productos (SV), 0 pedidos, 0 movimientos.
- Sin datos reales de contacto: se usan placeholders configurables.
