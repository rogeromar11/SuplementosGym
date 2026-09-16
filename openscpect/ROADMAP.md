# ROADMAP · Fases del proyecto

Estados: `[x]` completado · `[~]` en progreso · `[ ]` pendiente.
No marcar como completado algo que no haya sido probado.

## Storefront (SG Tienda)

```
[x] Fase 1  — Auditoria
[x] Fase 2  — Creacion SuplementosGym
[x] Fase 3  — Migracion inicial
[x] Fase 4  — openscpect
[x] Fase 5  — Arquitectura
[x] Fase 6  — Frontend
[x] Fase 7  — Catalogo
[x] Fase 8  — Multipais
[x] Fase 9  — Usuarios
[x] Fase 10 — Carrito
[x] Fase 11 — Checkout
[x] Fase 12 — Pedidos
[x] Fase 13 — Inventario
[x] Fase 14 — Pagos
[x] Fase 15 — Perfil
[x] Fase 16 — Pruebas
[x] Fase 17 — Optimizacion
```

## Backoffice (staff)

```
[x] Fase 18 — Base del backoffice (SG_Controller, roles, permisos, layouts, auditoria)
[x] Fase 19 — Usuarios y roles (Users, Roles, matriz de permisos)
[x] Fase 20 — Clientes y catalogo (Clients, Products + imagenes, Warehouses, Catalogs)
[x] Fase 21 — Pedidos manuales y preparacion de bodega
[x] Fase 22 — Rutas y mensajeria movil (entregas, evidencias, reprogramacion)
[x] Fase 23 — Pagos y depositos de efectivo
[x] Fase 24 — Reportes (PDF/Excel), auditoria y configuracion
[~] Fase 25 — Endurecimiento y despliegue en cPanel
```

## Detalle

### Fase 1 — Auditoria  `[x]`
- [x] Auditar SGMensajeria (modulos, MVC, assets).
- [x] Auditar base de datos real.
- [x] Auditar productos, inventario, usuarios, pedidos, pagos, paises.
- [x] Registrar decisiones (ubicacion, DB origen/destino, paises, contacto).
- [x] Crear `openscpect/` con la documentacion inicial.

### Fase 2 — Creacion SuplementosGym  `[x]`
- [x] Crear base `suplementosgym`.
- [x] Esquema completo en `database/suplementosgym.sql` (antes `database.sql` + `upgrade_*`).
- [x] Configurar `application/config/database.php`.
- [x] Agregar campos de tienda y tablas propias.
- [x] Verificar importacion y conexion de la app.

### Fase 3 — Migracion inicial  `[x]`
- [x] Sembrar `payment_methods` (8) y catalogo base.
- [x] Copia inicial de productos de `sgmensajeria` (historica, solo SV).
- [x] Sembrar `store_settings` (hoy 11 claves por pais).
- [x] Verificar conteos e integridad.

> Nota actual: el instalador **no** deja productos; el catalogo se carga desde el backoffice.

### Fase 4 — openscpect  `[x]`
- [x] Crear los documentos base.
- [x] Mantener actualizado (ROADMAP, CHANGELOG, DATABASE, ADMIN, STORE, TESTING).

### Fase 5 — Arquitectura  `[x]`
- [x] `MY_Controller` base de tienda y render de plantilla publica.
- [x] Modelos: `Country_model`, `Product_model`, `Store_order_model`, `Inventory_model`.
- [x] Libreria `Store_cart` (carrito en sesion).
- [x] Helper `store_helper` (pais, categorias, precios, WhatsApp, settings).
- [x] Rutas amigables en `application/config/routes.php`.

### Fase 6 — Frontend  `[x]`
- [x] Bootstrap 5 + Bootstrap Icons locales en `assets/vendor`.
- [x] Identidad visual (`assets/css/design.css` + `store.css`) — libre/evolutiva.
- [x] Tipografias autocontenidas (Anton / Manrope en `assets/fonts/`).
- [x] Navbar sticky, hero, footer, responsive mobile-first.
- [x] `assets/js/store.js` en JavaScript vanilla + `landing.js` (GSAP).

### Fase 7 — Catalogo  `[x]`
- [x] Listado, categorias (mapeo desde `product_type`), busqueda y filtros.
- [x] Producto individual con relacionados y variantes por sabor.
- [x] Estados: agotado, categoria vacia, busqueda sin resultados, catalogo vacio.

### Fase 8 — Multipais  `[x]`
- [x] Selector de pais en navbar y persistencia en sesion.
- [x] Aislamiento por `country_id` en catalogo, pedidos e inventario.
- [x] Confirmacion al cambiar de pais con carrito activo.

### Fase 9 — Usuarios  `[x]`
- [x] Registro con los campos solicitados.
- [x] Login, logout, recuperacion y restablecimiento.
- [x] Perfil y cambio de contrasena.

### Fase 10 — Carrito  `[x]`
- [x] Agregar, eliminar, aumentar, disminuir, vaciar.
- [x] Totales con subtotal, envio y total.
- [x] Validacion server-side y rechazo por stock/agotado.

### Fase 11 — Checkout  `[x]`
- [x] Requiere autenticacion y conserva el carrito.
- [x] Datos precargados y editables.
- [x] Metodo de pago por pais y creacion de pedido.

### Fase 12 — Pedidos  `[x]`
- [x] `store_orders` + `store_order_items` con numeracion por pais.
- [x] Mis pedidos y detalle (solo propietario).
- [x] `order_status_history` para el seguimiento.

### Fase 13 — Inventario  `[x]`
- [x] Agregar al carrito no descuenta.
- [x] Compra confirmada descuenta con transaccion y movimiento.
- [x] Sin stock negativo y sin doble descuento (`inventory_applied`).

### Fase 14 — Pagos  `[x]`
- [x] Metodos por pais en `payment_methods`.
- [x] Estados `pendiente` / `parcial` / `pagado` (`Payment_service`).
- [x] Pagina de formas de pago.

### Fase 15 — Perfil  `[x]`
- [x] Mi perfil, editar datos y cambiar contrasena.
- [x] Mis pedidos con estado y estado de pago.

### Fase 16 — Pruebas  `[x]`
- [x] Smoke test de rutas, E2E de compra, casos borde y flujos de cuenta.
- [x] Evidencia en `TESTING.md`.

### Fase 17 — Optimizacion  `[x]`
- [x] SEO (title, description, canonical, Open Graph, H1/H2, ALT).
- [x] Accesibilidad (HTML semantico, labels, ARIA, focus-visible, skip link).
- [x] Responsive (grid mobile-first; breakpoints 320-1920).
- [x] `base_url` robusto para Apache en subcarpeta y `php -S`.

### Fase 18 — Base del backoffice  `[x]`
- [x] `SG_Controller` + `Authenticated_Controller` / `Admin_Controller` / `Courier_Controller`.
- [x] Vistas por *package path* en `application/third_party/sgadmin/views/`.
- [x] `Permission_service` + `Audit_service` + helper `permission_helper`.
- [x] Layouts admin, courier y print.

### Fase 19 — Usuarios y roles  `[x]`
- [x] `/users` (staff) y `/roles` (grupos + matriz `group_permissions`).
- [x] Grupos: admin, customer, vendedor, bodeguero, mensajero, auxiliar_admin.

### Fase 20 — Clientes y catalogo  `[x]`
- [x] `/clients` con importacion Excel (`Client_import_service`).
- [x] `/products` con galeria (`product_images`) e importacion (`Product_import_service`).
- [x] `/warehouses` y `/catalogs` (pagos, fallos, transportes, turnos).

### Fase 21 — Pedidos y preparacion  `[x]`
- [x] `/orders` (alta manual, edicion, detalle, impresion, anulacion).
- [x] `/preparation` (iniciar, marcar preparado, devolver, observaciones).

### Fase 22 — Rutas y mensajeria  `[x]`
- [x] `/routes` (crear, agregar/quitar/reordenar, transferir, iniciar).
- [x] `/courier` movil (entregar/no entregar, evidencias, deep links).

### Fase 23 — Pagos y depositos  `[x]`
- [x] `Payment_service` (pagos parciales, historial, idempotencia).
- [x] `/deposits` (mensajero + oficina: confirmar, entregar, aprobar).

### Fase 24 — Reportes y auditoria  `[x]`
- [x] `/reports` con exportacion PDF/Excel.
- [x] `/audit` y `/settings`.

### Fase 25 — Endurecimiento y despliegue  `[~]`
- [x] Assets autocontenidos y `.htaccess` de seguridad.
- [x] Guia de despliegue (ver `DEPLOYMENT.md`).
- [ ] Forzar HTTPS y `cookie_secure = TRUE` (pendiente de certificado/decision).
- [ ] Definir `CI_ENV=production` y `log_threshold` en el hosting.
- [ ] Cargar WhatsApp, correo y horario reales en `store_settings`.
- [ ] Habilitar SMTP (`use_ci_email`) para correos de auth.
- [ ] Definir productos/precios para Costa Rica.
