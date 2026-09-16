# SuplementosGym · SG Tienda

Tienda online de suplementos deportivos (Costa Rica / El Salvador) construida sobre
**CodeIgniter 3.4.2 + IonAuth**, con **storefront público** y **backoffice** completo
(pedidos, preparación, rutas, mensajería, depósitos, reportes y auditoría).

> Documentación viva del proyecto: [`openscpect/`](openscpect/README.md).

## Stack

- **Framework**: CodeIgniter 3.4.2 (fork `pocketarc/codeigniter`). Nunca editar `system/`.
- **Auth**: IonAuth (`application/third_party/ion_auth`) con grupos y permisos granulares.
- **PHP**: 8.2+ (ver `vendor/composer/platform_check.php`).
- **Email**: PHPMailer 6.9 vía `application/libraries/MY_Email.php`.
- **API**: `chriskacerguis/codeigniter-restserver` (health check `GET /api/ping`).
- **Reportes**: Dompdf (`Pdf_service`) y PhpSpreadsheet (`Excel_service`).
- **Frontend**: Bootstrap 5 + Bootstrap Icons locales, GSAP, assets autocontenidos (sin CDN).

## Requisitos

- PHP 8.2 o superior con extensiones `mysqli`, `mbstring`, `gd`, `zip`, `curl`, `openssl`,
  `dom`, `xml`, `fileinfo`.
- MySQL 8 / MariaDB 10.4+.
- Apache con `mod_rewrite` (o el servidor integrado `php -S`).

## Instalación

1. Instala dependencias:

   ```bash
   composer install
   ```

2. Configura credenciales en `application/config/database.php` (placeholders de desarrollo:
   `root` / vacío / `suplementosgym`).
3. Importa el esquema completo:

   ```bash
   mysql -u root < database/suplementosgym.sql
   ```

4. Configura SMTP en `application/config/email.php` (opcional; requerido para correos reales).
5. Arranca el servidor integrado:

   ```bash
   php -S localhost:8000
   ```

### Reiniciar la base

```bash
mysql -u root suplementosgym < database/limpiar_suplementosgym.sql
mysql -u root < database/suplementosgym.sql
```

## URLs útiles

| Área | URL |
|---|---|
| Storefront | `/`, `/productos`, `/producto/{id}`, `/carrito`, `/checkout`, `/cuenta` |
| Login de cliente | `/ingresar` · Registro `/registro` · Recuperar `/recuperar` |
| Backoffice (staff) | Login `/auth/login` · Dashboard `/dashboard` |
| API | `GET /api/ping` |

**Administradores de desarrollo** (contraseña `password`, cambiar en producción):

- Costa Rica → `admin@admin.com`
- El Salvador → `admin@elsalvador.local`

> El instalador **no carga productos**: la tabla `products` arranca vacía. Cárgalos desde el
> backoffice (`/products` → importar Excel / alta manual). Ver [`openscpect/ADMIN.md`](openscpect/ADMIN.md).

## Documentación

- [`openscpect/`](openscpect/README.md) — documentación viva (arquitectura, BD, tienda, backoffice, seguridad, despliegue).
- [`AGENTS.md`](AGENTS.md) — reglas para agentes y desarrolladores.
- [`openscpect/DEPLOYMENT.md`](openscpect/DEPLOYMENT.md) — publicación en cPanel.
