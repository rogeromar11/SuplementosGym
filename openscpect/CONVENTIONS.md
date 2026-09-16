# CONVENTIONS · Convenciones de desarrollo

## Codigo

- **CodeIgniter 3 MVC**: controladores delgados; logica en modelos/librerias.
- **Nunca editar `system/`**. Extender via `application/core/MY_*` o
  `application/libraries/MY_*` (`subclass_prefix = 'MY_'`).
- Clases `Studly_Case`, metodos `snake_case`, constantes `UPPER_CASE`.
- No agregar comentarios innecesarios; seguir el estilo de archivos vecinos.
- Bases: `MY_Controller` (storefront) y `SG_Controller` + role bases (backoffice).

## Vistas

- **Storefront**: vistas en `application/views/store/` con partials
  `partials/header.php` y `partials/footer.php` (no existe una unica `store_template.php`).
- **Backoffice**: vistas en `application/third_party/sgadmin/views/` (cargadas por *package path*),
  con layouts en `layouts/admin`, `layouts/courier` y `layouts/print`.
- Sin consultas SQL en vistas.
- Escapar toda salida (`html_escape()`).

## Nomenclatura

| Recurso | Ruta / nombre |
|---|---|
| Controladores storefront | `Store`, `Cart`, `Checkout`, `Account`, `Store_auth` |
| Controladores backoffice | `application/controllers/admin/` (`Orders`, `Routes`, `Products`, ...) |
| Modelos tienda | `Product_model`, `Store_order_model`, `Inventory_model`, `Country_model`, `Settings_model` |
| Modelos backoffice | `Order_model`, `Route_model`, `Client_model`, `Catalog_product_model`, `Warehouse_model`, `Deposit_model`, `Report_model`, `User_model` |
| Librerias | `Store_cart`, `MY_Email`, y servicios (`Order_service`, `Route_service`, `Delivery_service`, `Payment_service`, `Permission_service`, `Audit_service`, `Pdf_service`, `Excel_service`, `Map_link_parser`, importadores) |
| CSS | `assets/css/design.css`, `store.css` (tienda); `app.css`, `courier.css`, `print.css` (admin) |
| JS | `assets/js/store.js`, `landing.js`, `calculator.js`; paginas admin en `assets/js/pages/` |
| Vistas | `application/views/store/`, `application/third_party/sgadmin/views/` |

## JavaScript

- **Storefront**: Vanilla (sin frameworks). Responsabilidades: busqueda, filtros, carrito,
  contador, menu movil, selector de pais, animaciones, mensajes.
- **Backoffice**: jQuery + DataTables + Select2 + SweetAlert2 + Chart.js + Leaflet (locales en
  `assets/vendor/`), con un script por pagina en `assets/js/pages/`.
- Nunca confiar en JS para validaciones de negocio (siempre revalidar server-side).

## CSS

- Storefront: `assets/css/design.css` (tema base) + `assets/css/store.css` (capa funcional);
  fuentes en `assets/css/fonts.css`.
- Backoffice: `assets/css/app.css` (+ `courier.css`, `print.css`).
- **La identidad visual es libre**: la paleta/tipografias pueden evolucionar (ver `DESIGN.md`).
- Storefront autocontenido (sin CDN). Mobile-first; grid Bootstrap.

## Composer

- Dependencias: PHPMailer, REST server, Dompdf, PhpSpreadsheet.
- Agregar con `composer require` y documentar. Nunca editar `vendor/` a mano.

## Base de datos

- Todo SQL vive en `database/`.
- `database/suplementosgym.sql` = instalador unico (esquema completo + datos iniciales).
- `database/limpiar_suplementosgym.sql` = borra todas las tablas para reiniciar.
- Todo cambio de esquema debe reflejarse en `suplementosgym.sql` y enviarse en el mismo PR.

## Git

- Ramas: `main` (produccion), `develop` (integracion), `feature/<nombre>`, `hotfix/<nombre>`.
- Nunca push/merge directo a `main`.
- Todo cambio se entrega como Pull Request.
- Commits en ingles, imperativo: `Add`, `Fix`, `Update`, `Remove`, `Refactor`, `Migrate`.

## Documentacion

- Mantener `openscpect/` actualizado en cada fase.
- Registrar cambios en `CHANGELOG.md`.
