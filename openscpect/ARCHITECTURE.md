# ARCHITECTURE · Arquitectura

## Diagrama general

```
SGMensajeria  (referencia historica, intocable)
      │
      │  auditoria + copia inicial controlada (fase ya cerrada)
      ▼
SuplementosGym  (proyecto + base de datos independiente)
      │
      ├── SG Tienda     (storefront publico)
      └── Backoffice    (staff: pedidos, rutas, mensajeria, depositos, reportes)
```

## Principio de independencia

Una vez realizada la copia inicial:

- Modificar `suplementosgym` **NO** modifica `sgmensajeria`.
- Modificar `sgmensajeria` **NO** modifica `suplementosgym`.
- **No existe sincronizacion automatica.** El proyecto hoy es autonómo.

## Patron de aplicacion

- **CodeIgniter 3 MVC**: controladores delgados; logica en modelos y librerias.
- **Query Builder** con placeholders. Nunca concatenar entrada de usuario.
- **Dos entry points**:
  - `MY_Controller` → base del **storefront** (tambien carga `SG_Controller` al final).
  - `SG_Controller` → base del **backoffice**; de el heredan `Authenticated_Controller`,
    `Admin_Controller` (grupo `admin`) y `Courier_Controller` (grupo `mensajero`).
- **Permisos** granulares via `Permission_service`, `has_permission()` y `require_permission()`.
- **Auditoria** transversal via `Audit_service` (`audit_logs`).

```
application/
  controllers/       Store, Cart, Checkout, Account, Store_auth  (storefront)
                     admin/  Auth, Dashboard, Users, Roles, Clients, Products,
                             Warehouses, Catalogs, Orders, Preparation, Routes,
                             Courier, Reports, Settings, Audit, Deposits
  models/            tienda (Product, Store_order, Inventory, Country, Settings)
                     + backoffice (Order, Route, Client, Catalog_product,
                       Warehouse, Deposit, Report, User)
  libraries/         Store_cart, MY_Email y servicios (Order, Route, Delivery,
                     Payment, Permission, Audit, Pdf, Excel, Map_link_parser,
                     Client_import, Product_import, Sgms_phar_zip_archive)
  core/              MY_Controller, MY_Model, SG_Controller + role bases
  helpers/           app_helper, permission_helper, store_helper
  config/            database.php, email.php, rest.php, routes.php, config.php
  views/store/       layouts parciales (partials/header, partials/footer) + paginas
  third_party/       ion_auth; sgadmin/views (backoffice via package path)
assets/              css/ js/ js/pages/ vendor/ fonts/ img/ video/  (todo local)
database/            suplementosgym.sql + limpiar_suplementosgym.sql
openscpect/          documentacion viva
```

## Storefront

- Vistas en `application/views/store/` con partials `partials/header.php` y
  `partials/footer.php` (no una unica `store_template.php`).
- Catalogo, carrito e inventario filtran siempre por el `country_id` en sesion.
- El carrito (`Store_cart`) vive en sesion y **no** descuenta inventario; el descuento ocurre al
  confirmar la compra, con transaccion.

## Backoffice

- Vistas en `application/third_party/sgadmin/views/` (cargadas por *package path* desde
  `SG_Controller` / admin `Auth`).
- `store_orders` es la **unica fuente de verdad** de pedidos (web + manuales).
- Rutas: cada modulo se monta en la raiz (`/orders`, `/routes`, `/dashboard`, ...) mediante el bucle
  de `routes.php`; `/admin/<modulo>` tambien resuelve por convencion de carpetas de CI3.
- Detalle de modulos, roles y flujo en `ADMIN.md`.

## Aislamiento de datos

- Toda la aplicacion consulta unicamente la base `suplementosgym` en runtime.
- No hay dependencia de tablas de SGMensajeria.
- El pais seleccionado se guarda en sesion; cada consulta se filtra por `country_id`.

## Copia inicial (historica)

```
sgmensajeria.products (country_id = 2)
        │  INSERT ... SELECT (columnas explicitas)
        ▼
suplementosgym.products (country_id = 2)
```

Despues de la copia, **no se sincroniza automaticamente**. Hoy el catalogo se gestiona desde el
backoffice. Ver `DATABASE.md`.

## Extension futura

La sincronizacion automatica con SGMensajeria queda fuera de alcance; podra evaluarse como una
fase independiente de integracion. La pasarela de pago en linea tambien esta fuera de alcance
(los pagos se registran manualmente).
