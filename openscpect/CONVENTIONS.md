# CONVENTIONS · Convenciones de desarrollo

## Codigo

- **CodeIgniter 3 MVC**: controladores delgados; logica en modelos/librerias.
- **Nunca editar `system/`**. Extender via `application/core/MY_*` o
  `application/libraries/MY_*` (`subclass_prefix = 'MY_'`).
- Clases `Studly_Case`, metodos `snake_case`, constantes `UPPER_CASE`.
- No agregar comentarios innecesarios; seguir el estilo de archivos vecinos.

## Vistas

- Plantilla publica `application/views/store/store_template.php`.
- Contenido modular en `application/views/store/`.
- Sin consultas SQL en vistas.
- Escapar toda salida (`html_escape()`).

## Nomenclatura de la tienda

| Recurso | Ruta / nombre |
|---|---|
| CSS | `assets/css/store.css` |
| JS | `assets/js/store.js` |
| Controladores | `Store`, `Cart`, `Checkout`, `Account`, `Store_orders` |
| Modelos | `Product_model`, `Store_order_model`, `Inventory_model`, `Store_setting_model` |
| Librerias | `Store_cart`, `Store_contact` |
| Vistas | `views/store/` |

## JavaScript

- **Vanilla** (sin frameworks adicionales).
- Responsabilidades: busqueda, filtros, carrito, contador, menu movil, selector de
  pais, animaciones, mensajes y confirmaciones.
- Nunca confiar en JS para validaciones de negocio (siempre revalidar server-side).

## CSS

- Variables de marca en `:root` (ver `PROJECT.md`).
- Mobile-first; grid Bootstrap (`row`, `col-6`, `col-md-4`, `col-lg-3`).
- No modificar CSS global de forma innecesaria.

## Composer

- Dependencias existentes: PHPMailer, REST server.
- Agregar con `composer require` y documentar. Nunca editar `vendor/` a mano.

## Base de datos

- Todo SQL vive en `database/`.
- `database.sql` = esquema completo base.
- Cada cambio genera `database/upgrade_<cambio>_<YYYYMMDD>.sql` idempotente.
- Enviar ambos archivos en el mismo PR.

## Git

- Ramas: `main` (produccion), `develop` (integracion), `feature/<nombre>`, `hotfix/<nombre>`.
- Nunca push/merge directo a `main`.
- Todo cambio se entrega como Pull Request.
- Commits en ingles, imperativo: `Add`, `Fix`, `Update`, `Remove`, `Refactor`, `Migrate`.

## Documentacion

- Mantener `openscpect/` actualizado en cada fase.
- Registrar cambios en `CHANGELOG.md`.
