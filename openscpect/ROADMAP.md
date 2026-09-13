# ROADMAP · Fases del proyecto

Estados: `[x]` completado · `[~]` en progreso · `[ ]` pendiente.
No marcar como completado algo que no haya sido probado.

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

## Detalle

### Fase 1 — Auditoria  `[x]`
- [x] Auditar SGMensajeria (modulos, MVC, assets).
- [x] Auditar base de datos real.
- [x] Auditar productos, inventario, usuarios, pedidos, pagos, paises.
- [x] Auditar assets/CSS/JS reutilizables.
- [x] Registrar decisiones (ubicacion, DB origen/destino, paises, contacto).
- [x] Crear `openscpect/` con la documentacion inicial.
- [x] Cerrar y entregar el diagnostico.

### Fase 2 — Creacion SuplementosGym  `[x]`
- [x] Crear base `suplementosgym`.
- [x] Cargar esquema (`database/database.sql` + `upgrade_add_store_schema_20260912.sql`).
- [x] Configurar `application/config/database.php`.
- [x] Agregar campos de tienda y tablas propias.
- [x] Verificar importacion y conexion de la app.

### Fase 3 — Migracion inicial  `[x]`
- [x] Copiar `payment_methods` (8).
- [x] Copiar `products` de `sgmensajeria` (solo `country_id=2`, activos): 54.
- [x] Sembrar `store_settings` (10).
- [x] Verificar conteos e integridad.

### Fase 4 — openscpect  `[x]`
- [x] Crear los 17 documentos base.
- [x] Mantener actualizado (ROADMAP, CHANGELOG, DATABASE, STORE, TESTING).

### Fase 5 — Arquitectura  `[x]`
- [x] `MY_Controller` base de tienda y render de plantilla publica.
- [x] Modelos: `Country_model`, `Product_model`, `Store_order_model`, `Inventory_model`.
- [x] Libreria `Store_cart` (carrito en sesion).
- [x] Helper `store_helper` (pais, categorias, precios, WhatsApp, settings).
- [x] Rutas amigables en `application/config/routes.php`.

### Fase 6 — Frontend  `[x]`
- [x] Bootstrap 5 + Bootstrap Icons locales en `assets/vendor`.
- [x] Identidad visual (`assets/css/store.css`) con la paleta de marca.
- [x] Tipografias Fira Code / Fira Sans.
- [x] Navbar sticky, hero, footer, responsive mobile-first.
- [x] `assets/js/store.js` en JavaScript vanilla.

### Fase 7 — Catalogo  `[x]`
- [x] Listado, categorias (mapeo desde `product_type`), busqueda y filtros.
- [x] Producto individual con relacionados.
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

### Fase 13 — Inventario  `[x]`
- [x] Agregar al carrito no descuenta.
- [x] Compra confirmada descuenta con transaccion y movimiento.
- [x] Sin stock negativo y sin doble descuento (`inventory_applied`).

### Fase 14 — Pagos  `[x]`
- [x] Metodos por pais copiados de SGMensajeria.
- [x] Estados pendiente / en verificacion / pagado / cancelado.
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
