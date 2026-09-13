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

- Motor: **IonAuth** (ya presente en SuplementosGym).
- Grupo de tienda: `customer`.
- `country_id` en `users` para separar clientes por pais.
- Password hashing: bcrypt (IonAuth). Nunca hashing propio.
- Registro publico existente en `application/controllers/Auth.php`.
- Campos extra (zona de entrega, direccion, telefono secundario) se agregan a `users`
  o a una tabla `user_profiles`.

## Reglas

- Nunca guardar contrasenas en texto plano.
- Nunca exponer tokens.
- Validar todo server-side con `form_validation`.
- Escapar salida con `html_escape()`.
