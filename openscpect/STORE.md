# STORE · Catalogo y experiencia de tienda

## Implementacion

| Pieza | Ubicacion |
|---|---|
| Controladores | `application/controllers/Store.php`, `Cart.php`, `Checkout.php`, `Account.php`, `Store_auth.php` |
| Modelos | `Country_model.php`, `Product_model.php`, `Store_order_model.php`, `Inventory_model.php` |
| Libreria | `application/libraries/Store_cart.php` |
| Helper | `application/helpers/store_helper.php` |
| Vistas | `application/views/store/` (+ `partials/header.php`, `partials/footer.php`) |
| Assets | `assets/css/design.css`, `assets/css/store.css`, `assets/js/store.js`, `assets/js/landing.js` |
| Rutas | `productos`, `producto/{id}`, `carrito/*`, `checkout`, `cuenta/*`, `ingresar`, `registro`, `pais` |

## Secciones

Inicio · Productos (catalogo) · Producto individual · Carrito · Checkout · Registro · Login ·
Mi perfil · Mis pedidos · Sobre nosotros · Guia · Calculadora de macros · Formas de pago ·
Contacto · Footer.

## Navbar (sticky, oscuro)

- Logo, Inicio, **Productos** (dropdown con categorias + "Todos los productos"),
  Nosotros, Guia, Formas de Pago, Contacto, **Calculadora**.
- Selector de pais (CR / SV) con banderas.
- Carrito con contador.
- Cuenta: invitado ve **Iniciar sesion**; autenticado ve un dropdown con
  **Mi perfil / Mis pedidos / Cerrar sesion**.
- Boton flotante de WhatsApp (`.fab-wa`) global.

## Hero

Fondo oscuro con video (`assets/video/hero-web.mp4`), mensaje orientado a fuerza, rendimiento,
recuperacion y objetivos. Incluye estadisticas animadas y bloque de redes sociales.
Animaciones con GSAP + ScrollTrigger (`assets/js/landing.js`).

## Catalogo

- Fuente: tabla `products` de `suplementosgym`.
- Filtros: `country_id = pais_actual` y `is_active = 1`.
- Filtros de UI: categoria, laboratorio, precio (min/max), disponibilidad y orden.
- Paginacion: 10 / 25 / 50 / 100 por pagina (default 25), preservando filtros.

## Categorias

Mapeo desde `product_type` real:

| Categoria de tienda | Valores reales de `product_type` |
|---|---|
| Proteinas | `Proteina`, `Whey`, `ISO 100`, `Caseina` |
| Creatinas | `Creatina` |
| Preentrenos | `Pre - entreno`, `Pre workout` |
| Aminos | `BCAA`, `EAA`, `Amino` |
| Quemadores de Grasa | `Quemador`, `CLA`, `Lipo` |
| Ganadores de Peso | `Mass Gainer` |
| Multivitaminicos | `Multivitaminico`, `Vitamina` |
| Otros | cualquier otro valor no reconocido |

> Todo valor no reconocido cae en **Otros**. No se elimina ningun producto.

## Variantes por sabor

Los productos que comparten tipo, laboratorio, nombre, peso y porciones (considerando campos
vacios) se agrupan como un mismo producto con varios **sabores**
(`Product_model::variant_key()`, `group()`, `group_for()`):

- Catalogo y destacados muestran **una sola tarjeta por producto**.
- Al agregar un producto con varios sabores se abre un **modal** para elegir el sabor (precio por
  sabor; agotados deshabilitados).
- El sabor elegido se muestra en carrito, checkout y "Mis pedidos"
  (`store_order_items.item_flavor`).

## Tarjeta de producto

Imagen (WebP + fallback JPG con `<picture>`, `loading="lazy"`), laboratorio, nombre, categoria,
peso, porciones, sabor, descripcion corta, precio y disponibilidad. Boton: **Agregar al carrito**
(WhatsApp queda en la ficha, el boton flotante y el checkout). Si esta agotado: **Agotado**.

## Producto individual

Imagen, nombre, laboratorio, categoria, precio, peso, porciones, sabor, descripcion, stock y
cantidad. Chips de sabor que llevan a la variante. Botones: **Agregar al carrito**, **Comprar
ahora**, **WhatsApp**. Incluye productos relacionados (por grupo).

## Busqueda y filtros

- Busqueda por nombre, laboratorio y categoria (con resultados server-side; realce en cliente).
- Filtros y orden siempre respetan el pais activo.

## Carrito

Operaciones: agregar, eliminar, aumentar, disminuir, vaciar. Muestra productos, cantidad, precio,
subtotal, envio y total. Incluye "Finalizar por WhatsApp".

Validacion server-side **antes** de crear pedido:
1. Consultar producto real. 2. Validar existencia. 3. Validar activo. 4. Validar pais.
5. Obtener precio real. 6. Validar stock. 7. Validar cantidad. 8. Recalcular subtotal.
9. Calcular envio. 10. Recalcular total.

## Mensajes de estado vacio

- Categoria sin productos: **"No hay productos disponibles en esta categoria."**
- Busqueda sin resultados: **"No encontramos productos que coincidan con tu busqueda."**
- Pais sin productos: **"Actualmente no hay productos disponibles para este pais."**

## Stock

- `stock_enabled = 0` → disponible sin control de inventario.
- `stock_enabled = 1` y `stock_qty <= 0` → **Agotado** (deshabilitar agregar).
- Nunca permitir `stock < 0`.

## WhatsApp

- Producto, pedido y checkout armados por `store_helper` (`store_whatsapp_url`,
  `store_cart_whatsapp_url`, `waCheckoutData`).
- Numero centralizado por pais en `store_settings` (`whatsapp_number`).
- Placeholders actuales: CR `50688888888`, SV `50377777777` (reemplazar).

## Carrito y cambio de pais

- No se mezclan productos de distintos paises.
- Al cambiar de pais con carrito no vacio, confirmar y (si acepta) vaciar el carrito.
