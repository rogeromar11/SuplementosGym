# STORE · Catalogo y experiencia de tienda

## Implementacion (2026-09-12)

| Pieza | Ubicacion |
|---|---|
| Controladores | `application/controllers/Store.php`, `Cart.php`, `Checkout.php`, `Account.php`, `Store_auth.php` |
| Modelos | `application/models/Country_model.php`, `Product_model.php`, `Store_order_model.php`, `Inventory_model.php` |
| Libreria | `application/libraries/Store_cart.php` |
| Helper | `application/helpers/store_helper.php` |
| Vistas | `application/views/store/` (+ `partials/`) |
| Assets | `assets/css/store.css`, `assets/js/store.js` |
| Rutas | `productos`, `producto/{id}`, `carrito/*`, `checkout`, `cuenta/*`, `ingresar`, `registro`, `pais` |

## Secciones

1. Inicio
2. Productos (catalogo)
3. Producto individual
4. Carrito
5. Checkout
6. Registro
7. Login
8. Mi perfil
9. Mis pedidos
10. Sobre nosotros
11. Formas de pago
12. Contacto
13. Footer

## Navbar (sticky, fondo `#0B0B0F`)

- Logo, Inicio, Productos, Nosotros, Formas de Pago, Contacto.
- Selector de pais (CR / SV).
- Carrito con contador.
- Cuenta:
  - Invitado: **Iniciar sesion** / **Registrarse**.
  - Autenticado: **Mi perfil** / **Mis pedidos** / **Cerrar sesion**.
- CTA **Haz tu pedido** (WhatsApp).

## Hero

Fondo negro, mensaje orientado a fuerza, rendimiento, recuperacion y objetivos.
Botones: **Ver productos** y **Escríbenos**. Animacion suave.

## Catalogo

- Fuente: tabla `products` de `suplementosgym`.
- Filtros: `country_id = pais_actual` y `is_active = 1`.
- Orden por `product_type`, `name` (o la mejor alternativa real).

## Categorias

Mapeo desde `product_type` real:

| Categoria de tienda | Valores reales de `product_type` |
|---|---|
| Proteinas | `Proteina`, `Mass Gainer` |
| Creatinas | `Creatina` |
| Quemadores de Grasa | `Quemador`, `CLA` |
| Preentrenos | `Pre - entreno` |
| Ganadores de Peso | `Mass Gainer` |
| Otros | cualquier otro valor no reconocido |

> Todo valor no reconocido cae en **Otros**. No se elimina ningun producto.

## Tarjeta de producto

Imagen, laboratorio, nombre, categoria, peso, porciones, sabor, descripcion corta,
precio y disponibilidad. Botones: **Agregar al carrito**, **Comprar ahora**,
**Pedir por WhatsApp**. Si esta agotado: **Agotado**.

## Producto individual

Imagen, nombre, laboratorio, categoria, precio, peso, porciones, sabor, descripcion,
stock y cantidad. Botones: **Agregar al carrito**, **Comprar ahora**, **WhatsApp**.

## Busqueda (JavaScript Vanilla)

- Por nombre, laboratorio y categoria.
- Filtrado en tiempo real.

## Filtros

- Categoria, laboratorio, precio, disponibilidad.
- Orden: relevancia, nombre, precio menor→mayor, precio mayor→menor.
- Siempre respetan el pais activo.

## Carrito

Operaciones: agregar, eliminar, aumentar, disminuir, vaciar.
Muestra: productos, cantidad, precio, subtotal, envio y total.

Validacion server-side **antes** de crear pedido:
1. Consultar producto real.
2. Validar existencia.
3. Validar activo.
4. Validar pais.
5. Obtener precio real.
6. Validar stock.
7. Validar cantidad.
8. Recalcular subtotal.
9. Calcular envio.
10. Recalcular total.

## Mensajes de estado vacio

- Categoria sin productos: **"No hay productos disponibles en esta categoria."**
- Busqueda sin resultados: **"No encontramos productos que coincidan con tu busqueda."**
- Pais sin productos: **"Actualmente no hay productos disponibles para este pais."**

## Stock

- `stock_enabled = 0` → disponible sin control de inventario.
- `stock_enabled = 1` y `stock_qty <= 0` → **Agotado** (deshabilitar agregar).
- Nunca permitir `stock < 0`.

## WhatsApp

- Producto: `Hola, estoy interesado en el producto: [PRODUCTO].`
- Pedido: `Hola, acabo de realizar el pedido #[NUMERO].`
- Numero centralizado por pais en `store_settings` (`whatsapp_number`).

## Carrito y cambio de pais

- No se mezclan productos de distintos paises.
- Al cambiar de pais con carrito no vacio, confirmar:
  **"Al cambiar de pais, los productos actuales del carrito podrian dejar de estar
  disponibles. ¿Deseas continuar?"**
- Si se confirma, vaciar carrito.
