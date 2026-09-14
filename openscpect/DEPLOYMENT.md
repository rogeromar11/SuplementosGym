# DEPLOYMENT · Publicacion en cPanel

SG Tienda es una aplicacion **CodeIgniter 3** clasica: HTML servido por el servidor
(Apache/PHP), sin build step ni Node. Todo el front se sirve desde `assets/`.

## Requisitos del hosting

- PHP 8.0+ con extensiones `mysqli`, `mbstring`, `gd`, `zip`, `curl`.
- MySQL/MariaDB.
- Apache con `mod_rewrite` (para URLs amigables) y `mod_headers`/`mod_deflate`/`mod_expires`.
- Composer solo si se usan dependencias (PHPMailer/REST). Si no se puede correr
  Composer en el host, subir `vendor/` ya instalado.

## Pasos

1. **Subir el proyecto** a `public_html/` (o a la carpeta del dominio).
2. **Crear la base** en cPanel > MySQL Databases y ejecutar:
   - `database/database.sql`
   - `database/upgrade_migrate_initial_data_20260912.sql` (datos iniciales)
3. **Configurar credenciales** en `application/config/database.php`
   (host, usuario, password, base). No hay `.env`; los valores van directos.
4. **URL base**: `application/config/config.php` usa autodeteccion
   (`HTTP_HOST` + `SCRIPT_NAME`), valida para subcarpeta o dominio raiz.
   Si el host lo requiere, definir `BASE_URL` por variable de entorno.
5. **Permisos de escritura**: `application/cache/`, `application/logs/`,
   `uploads/` (si se usaran subidas).
6. **Verificar** `/` (home), `/productos`, `/ingreso` y el flujo de compra.

## Assets autocontenidos

- Bootstrap y Bootstrap Icons: `assets/vendor/` (locales).
- Fuentes: `assets/fonts/` + `assets/css/fonts.css` (locales, sin Google Fonts).
- Imagenes de producto: `assets/img/products/` (parte del tema, versionables).
- Sin dependencias de CDN en runtime.

## URLs amigables

El `.htaccess` en la raiz reescribe todo a `index.php`. `index_page` esta vacio.
Si `mod_rewrite` no estuviera disponible, poner `$config['index_page'] = 'index.php';`
y usar URLs con `index.php/...`.

## Seguridad

- `display_errors = 0` en produccion (`ENVIRONMENT = production` por defecto).
- Noversionar credenciales reales; cambiarlas por entorno.
- `application/`, `system/` y archivos sensibles bloqueados por `.htaccess`.
- HTTPS: definir `FORCE_HTTPS`/redirect segun el hosting.

## Checklist post-deploy

- [ ] Home, catalogo, producto, carrito, checkout y cuenta funcionan.
- [ ] Imagenes de producto cargan desde `assets/img/products/`.
- [ ] Fuentes cargan desde `assets/fonts/` (sin errores de consola).
- [ ] Formularios envian CSRF correctamente.
- [ ] Crear pedido de prueba y verificar descuento de inventario.
