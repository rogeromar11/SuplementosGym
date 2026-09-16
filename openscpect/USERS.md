# USERS · Cuentas de usuario

## Principio

La tienda es **publica para navegar**: ver productos, buscar, navegar y cambiar de pais
no requiere registrarse. **Para finalizar una compra si se requiere cuenta.**

## Registro

Campos:

### Obligatorios
- Nombre.
- Correo.
- Numero celular.
- Zona de entrega.
- Direccion aproximada.
- Contrasena.

### Opcional
- Telefono secundario.

No solicitar informacion innecesaria.

## Login

Campos: correo y contrasena.
Botones: **Iniciar sesion**, **Crear cuenta**, **¿Olvidaste tu contrasena?**
(disponible via IonAuth si la configuracion de correo lo permite).

## Mi perfil

Permite modificar:
- Nombre.
- Correo.
- Numero celular.
- Telefono secundario.
- Zona de entrega.
- Direccion aproximada.

Tambien: **Cambiar contrasena**.

## Mis pedidos

- Numero de pedido, fecha, pais, productos, total, estado, estado del pago y metodo de pago.
- Un usuario solo puede consultar **sus propios** pedidos (autorizacion + filtro por
  `user_id`, sin posibilidad de IDOR).

## Checkout y autenticacion

Requiere sesion. Si no hay sesion:

**"Para completar tu compra necesitas iniciar sesion o crear una cuenta."**

Botones: **Iniciar sesion** / **Crear cuenta**. El carrito se conserva tras
iniciar sesion o registrarse.

## Implementacion tecnica

- Motor: **IonAuth**.
- Grupo de tienda: `customer`.
- `country_id` en `users` para separar clientes por pais.
- Password hashing: bcrypt (IonAuth, cost 12). Nunca hashing propio.
- Flujos publicos: `application/controllers/Store_auth.php`
  (`login`, `register`, `logout`, `forgot_password`, `reset_password`) con vistas en
  `application/views/store/auth/`.
- Perfil y pedidos: `application/controllers/Account.php` + `application/models/Store_order_model.php`.
- **Homologacion con el backoffice**: al registrarse o en el primer pedido, el usuario se
  vincula (o crea) en `clients` (`Client_model::find_or_create_for_user`). Los clientes manuales
  creados en el backoffice no tienen cuenta (`clients.user_id` es opcional). Ver `ADMIN.md`.

## Reglas

- Nunca guardar contrasenas en texto plano.
- Nunca exponer tokens.
- Validar todo server-side con `form_validation`.
- Escapar salida con `html_escape()`.
