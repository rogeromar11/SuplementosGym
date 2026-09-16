# AI_CONTEXT · Contexto maestro para IAs

> Lee este documento **antes** de tocar cualquier archivo del proyecto.

## 1. Qué es cada sistema

### SGMensajeria (referencia, intocable)
- Ruta: `C:/xampp/htdocs/SGMensajeria`
- Stack: CodeIgniter 3.x + IonAuth 3, PHP 8.1-8.5, MySQL/MariaDB, Bootstrap 5 + jQuery.
- Es un sistema de **mensajería y rutas de entrega** multi-país (Costa Rica / El Salvador),
  no una tienda. Su tabla `products` es el catálogo de insumos de reparto.
- **NO modificar, NO romper, NO eliminar datos.** Solo se lee para referencia.

### SuplementosGym (destino)
- Ruta: `C:/xampp/htdocs/SuplementosGym` (repo git `rogeromar11/SuplementosGym`, rama `develop`).
- Stack: CodeIgniter 3.4.2 (fork `pocketarc/codeigniter`) + IonAuth, PHPMailer 6.9,
  REST server, Dompdf/PhpSpreadsheet, Bootstrap 5 local. **PHP 8.2+**.
- Base de datos propia: **`suplementosgym`** en `localhost`, usuario `root`.
- Aquí viven **SG Tienda** (storefront) y el **backoffice** (staff). Ver `ADMIN.md`.

### SG Tienda (el producto público)
- Módulo público de e-commerce de suplementos deportivos orientado a gimnasio,
  rendimiento, recuperación y objetivos físicos.
- Multi-país: Costa Rica y El Salvador.

## 2. Decisiones vigentes

| Tema | Decisión |
|---|---|
| Ubicación del código | En `SuplementosGym` (no dentro de SGMensajeria). |
| Documentación | `SuplementosGym/openscpect/` (esta carpeta). |
| Base origen (histórica) | `sgmensajeria`. La copia inicial terminó en la fase de migración; hoy la BD es independiente. |
| Alcance multipaís | CR `id=1`, SV `id=2`; el país activo se guarda en sesión y filtra todo. |
| Base destino | `suplementosgym` (localhost / root / sin password en desarrollo). |
| Contacto/WhatsApp | Placeholders configurables por país en `store_settings`. **No inventar datos reales.** |
| Identidad visual | **Libre y evolutiva.** SGMensajeria es referencia funcional, no de colores. Ver `DESIGN.md`. |
| Entrega | HTML servido por Apache/PHP (cPanel), assets autocontenidos. Ver `DEPLOYMENT.md`. |
| Backoffice | Comparte app y BD con el storefront; `store_orders` es la única fuente de verdad. Ver `ADMIN.md`. |

## 3. Base de datos (resumen)

- Instalador único: `database/suplementosgym.sql` (crea la base, **33 tablas** y datos iniciales).
  Reinicio: `database/limpiar_suplementosgym.sql`.
- **El instalador no carga productos**: `products` arranca vacía. Se cargan desde el backoffice
  (`/products`, importación Excel) o por SQL.
- Todos los montos son `DECIMAL(15,2)`. Nunca `FLOAT`.
- Detalle completo en `DATABASE.md`.

## 4. Productos

- Tabla `products`: `id, country_id, sku, product_type, laboratory, name, weight, servings,
  flavor, description, cost_price, unit_price, is_active, stock_enabled, stock_qty` + campos de
  tienda (`store_description`, `image`, `featured`, `sort_order`, `seo_title`, `seo_description`).
- Galería en `product_images` (una principal por producto). Las imágenes se guardan en
  `assets/img/products/` (`admin/Products`).
- `product_type` es texto libre; se mapea a las categorías de la tienda (ver `STORE.md`).
- Las variantes por sabor se agrupan por tipo+laboratorio+nombre+peso+porciones
  (`Product_model::variant_key/group/group_for`).

## 5. Usuarios

- IonAuth: `users` (+ `country_id`), `groups`, `users_groups`, `login_attempts`.
- Navegar la tienda es público. **Para comprar se requiere cuenta** (grupo `customer`).
- Clientes del backoffice: `clients` (`user_id` opcional). Ver `USERS.md` y `ADMIN.md`.

## 6. Pedidos e inventario

- Tablas propias: `store_orders`, `store_order_items`, `inventory_movements`
  (+ `order_status_history`, `payments`, `payment_history`, `routes`, `route_orders`, ...).
- Agregar al carrito **no** descuenta stock. La compra confirmada descuenta inventario con
  transacción y sin doble descuento (`inventory_applied`). Ver `ORDERS.md`.

## 7. Pagos

- Métodos por país en `payment_methods` (8 en total). Estados de pago:
  `pendiente`, `parcial`, `pagado` (`Payment_service`). Nunca marcar pagado solo por "Comprar".
  Ver `PAYMENTS.md`.

## 8. Países

- CR `id=1` (CRC, ₡, +506), SV `id=2` (USD, $, +503). En `countries`.
- No mezclar productos ni carrito entre países.

## 9. Carrito y checkout

- Carrito por país, validado server-side antes de crear pedido (producto, activo, país,
  precio real, stock, cantidad, subtotal, envío, total).
- Checkout requiere autenticación; el carrito se conserva tras login/registro.

## 10. Archivos importantes

```
application/controllers/Store.php, Cart.php, Checkout.php, Account.php, Store_auth.php   Storefront
application/controllers/admin/                                                          Backoffice
application/core/MY_Controller.php    Base del storefront
application/core/SG_Controller.php    Base del backoffice (+ Authenticated/Admin/Courier)
application/models/                   Modelos de tienda y backoffice
application/libraries/                Servicios (Order, Route, Delivery, Payment, Permission, ...)
application/config/database.php       Conexion (apunta a 'suplementosgym')
application/config/routes.php         Rutas de tienda y backoffice
application/views/store/              Vistas del storefront
application/third_party/sgadmin/views Vistas del backoffice
assets/                               CSS/JS/fuentes/imagenes locales
database/suplementosgym.sql           Instalador (esquema + datos iniciales)
database/limpiar_suplementosgym.sql   Reinicio de la base
openscpect/                           Esta documentacion
```

## 11. PROTOCOLO PARA FUTURAS IAs

1. Leer `AI_CONTEXT.md`
2. Leer `README.md`
3. Leer `ARCHITECTURE.md`
4. Leer `DATABASE.md`
5. Leer el documento relacionado con la tarea (`ADMIN.md`, `STORE.md`, `ORDERS.md`, ...)
6. Revisar el código real
7. Implementar
8. Probar
9. Actualizar la documentación
10. Registrar cambios en `CHANGELOG.md`

**Reglas absolutas:** no asumir, no inventar, no romper. SGMensajeria es intocable.
