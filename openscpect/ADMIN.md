# ADMIN · Backoffice

Panel interno del proyecto (staff, no clientes). Comparte la misma aplicación CodeIgniter y la
misma base de datos que el storefront. `store_orders` es la **única fuente de verdad** de pedidos
(tanto los de la web como los manuales).

## Acceso y roles

- **Login**: `/auth/login` (admite país opcional `/auth/login/CR`). Logout, recuperar y restablecer
  contraseña en el mismo controlador (`admin/Auth`).
- **Bases** (`application/core/`):
  - `SG_Controller` — base compartida: sesión de país, settings, permisos, auditoría, JSON y layouts.
  - `Authenticated_Controller` — exige sesión de backoffice.
  - `Admin_Controller` — exige grupo IonAuth `admin`.
  - `Courier_Controller` — exige grupo `mensajero`.
- **Grupos** (semilla): `admin`, `customer`, `vendedor`, `bodeguero`, `mensajero`, `auxiliar_admin`.
- **Permisos granulares**: tablas `permissions` (45 claves) y `group_permissions`, resueltos con
  `Permission_service`, `has_permission()` y `require_permission()`. El grupo `admin` omite todas
  las comprobaciones.

## Módulos (rutas en la raíz, no bajo `/admin`)

El prefijo es el **nombre del módulo** (`routes.php` mapea cada módulo y `modulo/(.*)` a
`admin/<Modulo>`); por eso también funcionan `/admin/<modulo>` por resolución de subcarpeta de CI3.

| Módulo | Ruta | Controlador | Permiso |
|---|---|---|---|
| Auth | `/auth` | `admin/Auth` | — |
| Dashboard | `/dashboard` | `admin/Dashboard` | `dashboard.ver` |
| Usuarios | `/users` | `admin/Users` | `usuarios.*` |
| Roles | `/roles` | `admin/Roles` | (admin) |
| Clientes | `/clients` | `admin/Clients` | `clientes.*` |
| Productos | `/products` | `admin/Products` | `productos.*` |
| Bodegas | `/warehouses` | `admin/Warehouses` | `bodegas.*` |
| Catálogos | `/catalogs` | `admin/Catalogs` | (admin) |
| Pedidos | `/orders` | `admin/Orders` | `pedidos.*` |
| Preparación | `/preparation` | `admin/Preparation` | `pedidos.preparar` |
| Rutas | `/routes` | `admin/Routes` | `rutas.*` |
| Mensajería (móvil) | `/courier` | `admin/Courier` | `entregas.*` |
| Reportes | `/reports` | `admin/Reports` | `reportes.*` |
| Auditoría | `/audit` | `admin/Audit` | `auditoria.ver` |
| Configuración | `/settings` | `admin/Settings` | `configuracion.editar` |
| Depósitos | `/deposits` | `admin/Deposits` | `depositos.*` |

Vistas en `application/third_party/sgadmin/views/` (cargadas por *package path*): `admin_auth/`,
`layouts/admin|courier|print`, `orders/`, `preparation/`, `routes/`, `courier/`, `reports/`,
`deposits/`, `products/`, `clients/`, `warehouses/`, `catalogs/`, `roles/`, `settings/`, `audit/`.

## Flujo de pedidos

```
registrado → pendiente_preparacion → en_preparacion → preparado
           → asignado_ruta → en_ruta → entregado | no_entregado
                                          ↘ reprogramado
           ↘ cancelado
```

- `order_status_history` guarda cada transición (la usa `cuenta/pedidos` para el seguimiento del cliente).
- **Preparación** (`Preparation`): iniciar, marcar preparado, devolver a pendiente y observaciones.
- **Rutas** (`Routes`): crear ruta, agregar/quitar/reordenar pedidos, transferir entre rutas e iniciar.
  Numeración `RUT-YYYYMMDD-{MA|TA}-XX` (`Route_service`).
- **Mensajería** (`Courier`): vista móvil por ruta; iniciar/entregar/no entregar con evidencia,
  enlaces profundos de navegación, WhatsApp y teléfono (`Map_link_parser`).
- **Depósitos** (`Deposits`): flujo del mensajero con comprobante y confirmación/entrega/aprobación
  en oficina. Estados: `pendiente → recibido → entregado → aprobado` (`Deposit_model`).

## Pagos

`Payment_service::register_payment()` inserta en `payments`, actualiza `paid_amount`,
`balance_amount` y `payment_status` (`pendiente` / `parcial` / `pagado`), escribe `payment_history`
y es idempotente por referencia. Los comprobantes se guardan en `uploads/payment_receipts/`.

## Importaciones Excel

- **Clientes**: `Clients::import_template` / `import_preview` / `import_commit`
  (`Client_import_service`, PhpSpreadsheet).
- **Productos e inventario**: `Products::import_*` (`Product_import_service`).
- Fallback sin `ext-zip`: `Sgms_phar_zip_archive` (polyfill `ZipArchive` sobre `PharData`).

## Reportes

`Reports` (PDF vía `Pdf_service`/Dompdf y Excel vía `Excel_service`/PhpSpreadsheet):
general, mensajeros, productos, depósitos y vendedores.

## Auditoría

`Audit_service::log()` escribe en `audit_logs` (país, usuario, acción, módulo, tabla, registro,
IP/UA y datos JSON). Consultable en `/audit`.
