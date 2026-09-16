# SECURITY · Seguridad

## Implementar

- **CSRF** activo (`csrf_protection = TRUE`), token en todo formulario POST incluyendo AJAX.
- **Escapado XSS** de salida (`html_escape()`).
- **Validacion server-side** de toda entrada con `form_validation`.
- **Query Builder** con placeholders (nunca concatenar entrada en SQL).
- **Password hashing** con IonAuth (bcrypt). Nunca hashing propio.
- **Sesiones seguras**: regeneracion de ID, cookies `HttpOnly` + `SameSite=Lax`.
- **Autorizacion**: cada usuario accede solo a lo suyo.
- Validacion de **pais**, **producto**, **precio**, **cantidad** y **stock**.

## Nunca

- Guardar contrasenas en texto plano.
- Almacenar datos de tarjetas.
- Exponer tokens o API keys.
- Permitir modificar precios desde el frontend.
- Permitir stock negativo.
- Permitir pedidos ajenos (IDOR).
- Loguear datos sensibles.

## Nunca confiar en JavaScript

El carrito en el navegador es solo UX. Antes de crear un pedido, el servidor debe:
consultar el producto real, validar activo/pais/stock/cantidad y recalcular
subtotal, envio y total con el **precio real de la base de datos**.

## Backoffice (staff)

- **Autorizacion por permisos granulares**: `permissions` + `group_permissions`, resueltos con
  `Permission_service` / `has_permission()` / `require_permission()`. El grupo `admin` omite las
  comprobaciones; el resto de grupos (`vendedor`, `bodeguero`, `mensajero`, `auxiliar_admin`)
  solo accede a lo concedido.
- **Bases de rol**: `Authenticated_Controller` (sesion), `Admin_Controller` (grupo `admin`) y
  `Courier_Controller` (grupo `mensajero`).
- **Auditoria**: acciones sensibles se registran en `audit_logs` (`Audit_service::log`).
- **Uploads**: `delivery_evidence/`, `payment_receipts/`, `deposit_receipts/` y `products/`
  bloquean ejecucion de scripts via `.htaccess`. Validar tipo/tamano server-side.
- **Doble capa CSRF** en auth (IonAuth): token de framework + nonce de sesion
  (`_get_csrf_nonce()` / `_valid_csrf_nonce()`).

## Nivel de entorno

- `display_errors = 0` en produccion (`index.php` ya conmuta por `ENVIRONMENT`).
- **Pendiente:** el default de `index.php` es `development`; definir `CI_ENV=production` en el
  hosting (ver `DEPLOYMENT.md`).
- Credenciales nunca en el repositorio; `database.php` y `email.php` mantienen placeholders.
- HTTPS: hoy no hay redireccion forzada ni `cookie_secure = TRUE` (ver `DEPLOYMENT.md`).

## Checklist CSRF

- [ ] El token esta en todos los formularios POST (incluido auth AJAX).
- [ ] Se mantiene la capa de nonce de sesion de `Auth.php` (`_get_csrf_nonce` / `_valid_csrf_nonce`).
- [ ] Las peticiones AJAX de la tienda envian el token.

## Checklist de autorizacion

- [ ] Mis pedidos filtra por `user_id` de la sesion.
- [ ] El checkout exige sesion.
- [ ] Ningun endpoint permite leer/editar pedidos de otro usuario.
- [ ] El pais del carrito se valida contra el stock/producto real.
