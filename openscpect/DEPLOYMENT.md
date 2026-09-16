# DEPLOYMENT · Publicacion en hosting (HestiaCP / nginx)

SG Tienda es una aplicacion **CodeIgniter 3** clasica: HTML servido por el servidor
(nginx/PHP-FPM), sin build step ni Node. Todo el front se sirve desde `assets/`.

> Estado verificado: 2026-09-15 en HestiaCP (nginx + PHP-FPM). Este documento refleja los
> archivos reales del repo (`database/suplementosgym.sql`, `application/config/*`).

## Requisitos del hosting

- **PHP 8.2+**. `composer.json` declara `>=8.1 <8.6`, pero `vendor/composer/platform_check.php`
  exige `PHP_VERSION_ID >= 80200`, asi que el host debe ofrecer **PHP 8.2 o superior**.
- Extensiones PHP: `mysqli`, `mbstring`, `gd`, `zip`, `curl`, `openssl`, `dom`, `xml`,
  `fileinfo` (PhpSpreadsheet/Dompdf/PHPMailer). `intl` es opcional.
- MySQL/MariaDB.
- **Servidor web**: **nginx** (HestiaCP, nginx + PHP-FPM). El `.htaccess` **no** se usa.
  Ver la seccion [HestiaCP](#hestiacp-caso-real-de-este-proyecto).
- Composer solo si se usan dependencias (PHPMailer/REST/Dompdf/PhpSpreadsheet). Si no se
  puede correr Composer en el host, subir `vendor/` ya instalado (no esta versionado).

## Pasos

1. **Subir el proyecto** al doc root del dominio. En Hestia:
   `/home/USUARIO/web/TUDOMINIO/public_html/`.
2. **Crear la base** (Hestia → DB) e importar el script real:
   - `database/suplementosgym.sql` (esquema completo + datos iniciales).
   - Para reiniciar una base existente: `database/limpiar_suplementosgym.sql` y volver a importar.
3. **Configurar credenciales** en `application/config/database.php` (host, usuario, password,
   base). No hay `.env`; los valores van directos. Mantiene placeholders de desarrollo
   (`root` / vacio) a proposito: **no** commitear credenciales reales.
4. **Configurar correo** en `application/config/email.php` (SMTP del host). Ademas, IonAuth
   solo envia correo si `use_ci_email = TRUE` en `application/config/ion_auth.php`.
5. **URL base**: `application/config/config.php` detecta protocolo/host y admite override por
   `BASE_URL` (entorno y `$_SERVER`). Valido para dominio raiz o subcarpeta.
6. **Permisos de escritura** (usuario del servidor web):
   - `application/cache/`
   - `application/logs/` (si se activa `log_threshold`)
   - `uploads/` y **todas** sus subcarpetas (`products/`, `delivery_evidence/`,
     `payment_receipts/`, `deposit_receipts/`) — el backoffice **si** sube archivos.
7. **Cargar catalogo**: el instalador deja `products` vacia. Cargar productos desde el
   backoffice (`/products` → importar Excel / alta manual) o por SQL.
8. **Plantilla nginx** (clave): ver [HestiaCP](#hestiacp-caso-real-de-este-proyecto).
9. **Verificar** `/`, `/productos`, `/carrito`, `/cuenta`, `/ingresar`, `/auth/login`,
   `/dashboard`, `/robots.txt`, `/sitemap.xml` y `/api/ping`.

## Que subir al hosting

**Generar el paquete** (recomendado) para no subir `.git`, docs ni datos de prueba:

```powershell
powershell -ExecutionPolicy Bypass -File deploy\package.ps1
```

Genera `build\upload\` (contenido a subir) y `build\suplementosgym-upload.zip`
(para el File Manager de Hestia; usa separador `/` para extraer bien en Linux).
Opciones: `-NoVendor` (si el host corre Composer) y `-NoZip`.

**Obligatorio (la app no funciona sin esto):**

```
index.php
robots.txt
application/          (completa)
system/               (completa)
vendor/               (completa; no esta en git -> subir si el host no corre Composer)
assets/               (completa: css, js, vendor, fonts, img, video)
uploads/              (todas las subcarpetas, aunque esten vacias)
```

**Opcional / no necesario en el web root:**

- `composer.json` (+ `composer.lock`): solo si vas a correr Composer en el host.
- `database/`: importa `suplementosgym.sql` por phpMyAdmin; el archivo no necesita estar publicado.
- `openscpect/`, `deploy/`, `AGENTS.md`, `README.md`, `license.txt`, `.editorconfig`,
  `.gitattributes`: documentacion/desarrollo.
- `.htaccess`: nginx lo ignora (inofensivo si lo subes).

**Nunca subir:** `.git/`.

**Permisos de escritura** (usuario de PHP-FPM):
`application/cache/`, `application/logs/` y `uploads/` (con sus subcarpetas) -> `755`/`775`.

## Entorno de produccion

`index.php` define el entorno asi:

- Si existe `CI_ENV` (variable de entorno), se respeta.
- Si **no**, detecta por host: `localhost`/`127.0.0.1` → `development`; cualquier otro host →
  **`production`**. Asi el hosting real queda en produccion **sin** necesitar `fastcgi_param`.

Efectos de `ENVIRONMENT = production`:
- `display_errors = 0` (`index.php`).
- `db_debug = FALSE` y `save_queries = FALSE` (`database.php`).
- Recomendado subir `log_threshold` (p. ej. `1`) en `config.php` para registrar solo errores.

## HTTPS

Hestia gestiona el certificado (Let's Encrypt). Con nginx + PHP-FPM:

1. Habilita **SSL** en el dominio (Hestia → dominio → SSL) y marca forzar HTTPS/redireccion.
2. `config.php` detecta HTTPS por `$_SERVER['HTTPS']`, `X-Forwarded-Proto` o puerto 443, asi que
   `base_url` sale en `https://` sin configuracion extra.
3. Opcional: poner `$config['cookie_secure'] = TRUE;` en `config.php` (cookies solo por HTTPS).

## Assets autocontenidos

- Bootstrap y Bootstrap Icons: `assets/vendor/` (locales).
- Fuentes del storefront: `assets/fonts/` + `assets/css/fonts.css` (locales, sin Google Fonts).
- Imagenes de producto: `assets/img/products/` (`admin/Products.php`).
- Backoffice: los layouts `sgadmin/layouts/*` y las plantillas de auth aun cargan Google Fonts
  (y jQuery/SweetAlert2 por CDN en auth). Ver `INTEGRATIONS.md`.

## URLs amigables

Las **rutas de la aplicacion** (`application/config/routes.php`) son **agnosticas al servidor**;
lo unico necesario es que nginx reescriba a `index.php`:

```nginx
location / { try_files $uri $uri/ /index.php; }
```

`index_page` esta vacio (`config.php`). En nginx no se cambia ninguna ruta.

## HestiaCP (caso real de este proyecto)

En **HestiaCP con nginx + PHP-FPM** la solucion **sin SSH** es cambiar la plantilla web:

**Web → (clic en el dominio) → Web Template (NGINX) = `codeigniter` → Save.**

- La plantilla `default` de Hestia **no** trae `try_files` (causa 404 en toda ruta limpia).
- La plantilla `codeigniter` agrega `location / { try_files $uri $uri/ /index.php; }` y bloquea
  `/application` y `/system`.
- No requiere tocar codigo. El `Web Template` tambien acepta variantes como `wordpress`, que
  traen el mismo `try_files` (y `laravel` **no** sirve: apunta el root a `%docroot%/public`).
- **`/robots.txt`**: la plantilla tiene `location = /robots.txt` que sirve archivo fisico, por eso
  el `robots.txt` de la raiz se sube **estatico** (no usa la ruta dinamica de `Sitemap`).
  `/sitemap.xml` si funciona dinamico (pasa por `location /` → `index.php`).

## nginx (otros hostings)

Si el hosting no es Hestia y tienes acceso a la config de nginx, el minimo necesario es:

1. **Rewrite** (front controller): `location / { try_files $uri $uri/ /index.php?$query_string; }`
2. **Bloquear** `application/`, `system/`, `vendor/`, `database/`, `openscpect/`, archivos ocultos
   y `composer.*`, y **negar PHP dentro de `uploads/`**.
3. **Entorno** (no existe `SetEnv`): `fastcgi_param CI_ENV production;` — aunque la app ya cae en
   `production` por deteccion de host en `index.php`, definirlo es explicito.
4. **PHP-FPM**: `root` debe ser la carpeta donde vive `index.php`; ajustar `fastcgi_pass`.

## SEO: robots.txt y sitemap.xml

- `/sitemap.xml` → **dinamico** (`Sitemap::index`, vista `store/sitemap.php`). Usa `base_url()`, asi
  que `Sitemap:` y los `<loc>` son absolutos y correctos.
- `/robots.txt` → **archivo estatico** en la raiz (`robots.txt`), incluido en el paquete.

> No son requeridos para que la app funcione: son para indexacion (SEO).

## Subidas (uploads)

Carpetas activas del backoffice. En Hestia/nginx la plantilla `codeigniter` bloquea `/application`
y `/system`; la ejecucion de PHP en `uploads/` la bloquea la variante `wordpress` (o el `.htaccess`
si hubiera Apache):

- `uploads/delivery_evidence/` — evidencias de entrega (`Delivery_service.php`).
- `uploads/payment_receipts/` — comprobantes de pago (`Order_service.php`).
- `uploads/deposit_receipts/` — comprobantes de deposito (`admin/Deposits.php`).
- `uploads/products/` — reservada/legacy; hoy las imagenes van a `assets/img/products/`.

Ademas aplican `upload_max_filesize` / `post_max_size` del hosting. `Delivery_service` ya devuelve
mensajes de error por `UPLOAD_ERR_INI_SIZE` / `UPLOAD_ERR_FORM_SIZE`.

## Hardening opcional: app fuera de public_html

`index.php` soporta mover `system/`, `application/` y `vendor/` fuera del webroot con rutas
absolutas en `$system_path` y `$application_folder`. Requiere ajustar tambien
`composer_autoload` en `config.php`. No esta configurado asi por defecto.

## Checklist post-deploy

- [ ] Plantilla **Web Template (NGINX) = `codeigniter`** y rutas limpias responden.
- [ ] Base importada desde `database/suplementosgym.sql` y credenciales reales en `database.php`.
- [ ] `base_url` correcto (https) y assets cargando.
- [ ] HTTPS activo y sin contenido mixto.
- [ ] Catalogo cargado (el instalador no siembra productos).
- [ ] `/sitemap.xml` responde XML y `/robots.txt` responde el archivo estatico.
- [ ] Home, catalogo, producto, carrito, checkout y cuenta funcionan.
- [ ] Login de cliente `/ingresar` y backoffice `/auth/login` funcionan.
- [ ] Permisos del backoffice: cada grupo ve solo sus modulos; admin ve todo.
- [ ] Formularios envian CSRF correctamente.
- [ ] Subidas del backoffice escriben en `uploads/` (permisos correctos).
- [ ] Correo SMTP probado (si `use_ci_email = TRUE`).
- [ ] Crear pedido de prueba y verificar descuento de inventario.
