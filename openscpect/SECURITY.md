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

## Nivel de entorno

- `display_errors = 0` en produccion (`index.php` ya conmuta por `ENVIRONMENT`).
- Credenciales nunca en el repositorio; `database.php` mantiene placeholders.

## Checklist CSRF

- [ ] El token esta en todos los formularios POST (incluido auth AJAX).
- [ ] Se mantiene la capa de nonce de sesion de `Auth.php` (`_get_csrf_nonce` / `_valid_csrf_nonce`).
- [ ] Las peticiones AJAX de la tienda envian el token.

## Checklist de autorizacion

- [ ] Mis pedidos filtra por `user_id` de la sesion.
- [ ] El checkout exige sesion.
- [ ] Ningun endpoint permite leer/editar pedidos de otro usuario.
- [ ] El pais del carrito se valida contra el stock/producto real.
