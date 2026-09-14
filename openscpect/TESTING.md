# TESTING · Plan y checklist de pruebas

Fecha de ejecucion: 2026-09-12. Entorno: XAMPP / PHP 8.2 / MariaDB 10.4,
servidor integrado `php -S`.

## SGMensajeria (proteccion)

- [x] Continua funcionando (no se toco ningun archivo).
- [x] No se modificaron estructuras criticas innecesariamente.
- [x] Ningun cambio de codigo/datos dentro de `C:/xampp/htdocs/SGMensajeria`.

## SuplementosGym

- [x] Productos cargados (54, todos `country_id=2`).
- [x] Datos correctos (SKU, nombre, laboratorio, precio, stock).
- [x] Inventario independiente.
- [x] Pedidos independientes.

## Paises

- [x] Costa Rica (catalogo vacio con mensaje).
- [x] El Salvador (catalogo con 54 productos).
- [x] Cambio de pais (con y sin confirmacion de carrito).
- [x] Productos correctos por pais.
- [x] Inventario correcto por pais.

## Catalogo

- [x] Categorias (mapeo desde `product_type`).
- [x] Busqueda.
- [x] Filtros (categoria, laboratorio, precio, disponibilidad, orden).
- [x] Producto individual + relacionados.
- [x] Producto agotado (probado con stock 0).
- [x] Categoria vacia.
- [x] Busqueda sin resultados.
- [x] Catalogo vacio (pais sin productos).

## Usuarios

- [x] Registro (campos obligatorios y opcional).
- [x] Login.
- [x] Logout.
- [x] Perfil.
- [x] Editar perfil.
- [x] Cambiar contrasena.
- [x] Mis pedidos.

## Carrito

- [x] Agregar.
- [x] Eliminar.
- [x] Aumentar.
- [x] Disminuir.
- [x] Vaciar.
- [x] Totales (subtotal, envio, total).
- [x] No permite producto agotado.

## Checkout

- [x] Requiere autenticacion.
- [x] Conserva carrito (carrito en sesion).
- [x] Datos precargados.
- [x] Edita datos.
- [x] Metodo de pago por pais.
- [x] Crea pedido.

## Inventario

- [x] Agregar al carrito NO descuenta.
- [x] Compra confirmada descuenta (26 -> 24 en la prueba E2E).
- [x] Stock insuficiente (409 "La cantidad solicitada supera la disponibilidad actual.").
- [x] No stock negativo.
- [x] No doble descuento (`inventory_applied` = 1 e idempotencia).
- [x] Transaccion (`trans_begin` / `trans_commit` / `trans_rollback`).

## Aislamiento (obligatorio)

- [x] Modificar SuplementosGym -> SGMensajeria NO cambia.
- [x] Compra de prueba -> inventario SuplementosGym cambia y SGMensajeria NO.

## Seguridad

- [x] CSRF en POST normales y AJAX (token en meta y en formularios).
- [x] Precio recalculo server-side (no se confia en el frontend).
- [x] Cantidad validada y rechazada si supera stock.
- [x] Producto validado (activo y del pais).
- [x] Pais validado en el metodo de pago y en el carrito.
- [x] Pedido ajeno: `find_for_user` filtra por `user_id` (404 si no es propio).

## Responsive

- [x] 320px, 375px, 390px, 414px (grid 2 columnas y navbar colapsable).
- [x] 768px (tablet).
- [x] 1024px, 1366px, 1920px (desktop).

## Lint

- [x] `php -l` en 81 archivos de `application/` (0 errores) + 18 vistas.

## Evidencia E2E (resumen)

```
REGISTER final=http://.../ingresar
LOGIN    final=http://.../cuenta isProfile=True
CART ADD {"success":true,"count":2,"subtotal":"$144.00"}
ORDER    final=http://.../cuenta/pedido/2 hasNumber=True
inventory_applied=1 ; stock 26 -> 24 ; inventory_movements: salida x2
```

## Pendiente / futuro

- [ ] Configurar correo real (SMTP) y habilitar `use_ci_email` para recuperacion.
- [ ] Cargar WhatsApp, correo y horario reales en `store_settings`.
- [x] Cargar imagenes de producto en `assets/img/products/` (54/54, con placeholders).
- [ ] Definir precios/productos para Costa Rica.
