# DEPLOYMENT · Publicacion en cPanel

SG Tienda es una aplicacion **CodeIgniter 3** clasica: HTML servido por el servidor
(Apache/PHP), sin build step ni Node. Todo el front se sirve desde `assets/`.

> Estado verificado: 2026-09-15. Este documento refleja los archivos reales del repo
> (`database/suplementosgym.sql`, `application/config/*`). Si algo cambia en config,
> actualizar aqui.

## Requisitos del hosting

- **PHP 8.2+**. `composer.json` declara `>=8.1 <8.6`, pero `vendor/composer/platform_check.php`
  exige `PHP_VERSION_ID >= 80200`, asi que el host debe ofrecer **PHP 8.2 o superior**.
- Extensiones PHP: `mysqli`, `mbstring`, `gd`, `zip`, `curl`, `openssl`, `dom`, `xml`,
  `fileinfo` (PhpSpreadsheet/Dompdf/PHPMailer). `intl` es opcional.
- MySQL/MariaDB.
- Apache con `mod_rewrite` (URLs amigables) y `mod_headers` / `mod_deflate` / `mod_expires`
  (usados por el `.htaccess` raiz).
- Composer solo si se usan dependencias (PHPMailer/REST/Dompdf/PhpSpreadsheet). Si no se
  puede correr Composer en el host, subir `vendor/` ya instalado (no esta versionado).

## Pasos

1. **Subir el proyecto** a `public_html/` (o a la carpeta del dominio).
2. **Crear la base** en cPanel > MySQL Databases y ejecutar el script real:
   - `database/suplementosgym.sql` (esquema completo + datos iniciales).
   - Para reiniciar una base existente: `database/limpiar_suplementosgym.sql` y volver a importar.
3. **Configurar credenciales** en `application/config/database.php` (host, usuario, password,
   base). No hay `.env`; los valores van directos. `database.php` mantiene placeholders de
   desarrollo (`root` / vacio) a proposito: **no** commitear credenciales reales.
4. **Configurar correo** en `application/config/email.php` (SMTP del host). Ademas, IonAuth
   solo envia correo si `use_ci_email = TRUE` en `application/config/ion_auth.php`.
5. **URL base**: `application/config/config.php` usa autodeteccion (`HTTP_HOST` + `SCRIPT_NAME`)
   y admite override por la variable de entorno `BASE_URL` (`getenv('BASE_URL')`). Valido para
   dominio raiz o subcarpeta. Si el hosting no propaga `BASE_URL`, dejar la autodeteccion o
   fijar `$config['base_url']` directamente.
6. **Permisos de escritura** (usuario del servidor web):
   - `application/cache/`
   - `application/logs/` (si se activa `log_threshold`)
   - `uploads/` y **todas** sus subcarpetas (`products/`, `delivery_evidence/`,
     `payment_receipts/`, `deposit_receipts/`) — el backoffice **si** sube archivos.
7. **Cargar catalogo**: el instalador deja `products` vacia. Cargar productos desde el
   backoffice (`/products` → importar Excel / alta manual) o por SQL antes de probar la tienda.
8. **Verificar** `/`, `/productos`, `/carrito`, `/cuenta`, login de cliente `/ingresar`,
   backoffice `/auth/login`, dashboard `/dashboard`, un modulo con permisos (p. ej. `/orders`)
   y health check `/api/ping`.

## Entorno de produccion

`index.php:56` define el entorno por defecto como **`development`** (muestra errores):

```php
define('ENVIRONMENT', isset($_SERVER['CI_ENV']) ? $_SERVER['CI_ENV'] : 'development');
```

En cPanel, definir `CI_ENV=production` (MultiPHP INI Editor o en `.htaccess`):

```apache
SetEnv CI_ENV production
```

Efectos de `ENVIRONMENT = production`:
- `display_errors = 0` (`index.php:73-80`).
- `db_debug = FALSE` y `save_queries = FALSE` (`database.php:80,90`).
- Recomendado subir `log_threshold` (p. ej. `1`) en `config.php` para registrar solo errores.

## HTTPS

Hoy **no hay redireccion forzada a HTTPS**. `rest.php` tiene `force_https = false` y
`config.php` usa `cookie_secure = FALSE`. Para produccion con certificado:

1. Forzar HTTPS en el `.htaccess` raiz (antes de las reglas de `index.php`):

   ```apache
   RewriteCond %{HTTPS} off
   RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

2. Poner `$config['cookie_secure'] = TRUE;` en `config.php` (cookies solo por HTTPS).
3. El `.htaccess` ya envia `Strict-Transport-Security` solo bajo HTTPS (`env=HTTPS`).

## Assets autocontenidos

- Bootstrap y Bootstrap Icons: `assets/vendor/` (locales).
- Fuentes: `assets/fonts/` + `assets/css/fonts.css` (locales, sin Google Fonts).
- Imagenes de producto: `assets/img/products/` (las subidas del backoffice se guardan ahi,
  ver `admin/Products.php`).
- Sin dependencias de CDN en runtime.

## URLs amigables

El `.htaccess` en la raiz reescribe todo a `index.php`. `index_page` esta vacio
(`config.php:46`). Si `mod_rewrite` no estuviera disponible, poner
`$config['index_page'] = 'index.php';` y usar URLs con `index.php/...`.

## Subidas (uploads)

Carpetas activas del backoffice, protegidas contra ejecucion de scripts por su propio
`.htaccess` (`php_flag engine off` / `Require all denied`):

- `uploads/delivery_evidence/` — evidencias de entrega (`Delivery_service.php:355`).
- `uploads/payment_receipts/` — comprobantes de pago (`Order_service.php:478`).
- `uploads/deposit_receipts/` — comprobantes de deposito (`admin/Deposits.php:391`).
- `uploads/products/` — reservada/legacy; hoy las imagenes van a `assets/img/products/`.

Ademas, los limites de subida del hosting (`upload_max_filesize`, `post_max_size`) aplican:
subirlos en MultiPHP INI Editor si el backoffice rechaza comprobantes. `Delivery_service`
ya devuelve mensajes de error por `UPLOAD_ERR_INI_SIZE` / `UPLOAD_ERR_FORM_SIZE`.

## Hardening opcional: app fuera de public_html

`index.php` soporta mover `system/`, `application/` y `vendor/` fuera del webroot usando
rutas absolutas en `$system_path` y `$application_folder` (`index.php:97,114`). En cPanel
seria mover esa estructura por encima de `public_html/` y dejar ahi solo `index.php`,
`.htaccess`, `assets/` y `uploads/`. No esta configurado asi por defecto; requiere ajustar
las rutas absolutas del hosting.

## Checklist post-deploy

- [ ] `CI_ENV=production` definido y sin errores visibles en pantalla.
- [ ] Base importada desde `database/suplementosgym.sql` y credenciales reales en `database.php`.
- [ ] `BASE_URL` correcto (dominio raiz o subcarpeta) y assets cargando.
- [ ] HTTPS forzado, `cookie_secure = TRUE` y sin contenido mixto.
- [ ] Catalogo cargado (el instalador no siembra productos).
- [ ] Home, catalogo, producto, carrito, checkout y cuenta funcionan.
- [ ] Login de cliente `/ingresar` y backoffice `/auth/login` funcionan.
- [ ] Permisos del backoffice: cada grupo ve solo sus modulos; admin ve todo.
- [ ] Formularios envian CSRF correctamente.
- [ ] Subidas del backoffice escriben en `uploads/` (permisos correctos).
- [ ] Correo SMTP probado (si `use_ci_email = TRUE`).
- [ ] Crear pedido de prueba y verificar descuento de inventario.
