# ORDERS · Pedidos e inventario

## Alcance

SG Tienda usa tablas **propias** de pedidos (`store_orders`, `store_order_items`) y de
inventario (`inventory_movements`). No usa las tablas de reparto de SGMensajeria.

## Flujo de compra confirmada

```
Validar stock
   ↓
Crear pedido
   ↓
Crear detalle
   ↓
Descontar inventario SuplementosGym
   ↓
Registrar movimiento
   ↓
Confirmar transaccion
```

Se usan **transacciones** de base de datos cuando corresponde.

## Regla del carrito

- **Agregar al carrito NO descuenta inventario.**
- El descuento ocurre solo al confirmar la compra.

## Proteccion contra stock negativo

Si `stock = 2` y se solicitan `3`:

- Rechazar la operacion.
- Mensaje: **"La cantidad solicitada supera la disponibilidad actual."**
- Nunca permitir `stock < 0`.

## Proteccion contra doble descuento

- `store_orders.inventory_applied` (`0/1`) marca si el pedido ya desconto inventario.
- Antes de descontar, comprobar que `inventory_applied = 0`.
- Actualizar el flag dentro de la misma transaccion.

## Productos agotados

- `stock_enabled = 0` → disponible (sin control).
- `stock_enabled = 1` y `stock_qty <= 0` → **Agotado / Sin existencias**.
- Deshabilitar **Agregar al carrito** y no permitir compra.

## Validacion server-side del carrito (obligatoria)

Antes de crear el pedido:
1. Consultar producto real.
2. Validar existencia.
3. Validar activo.
4. Validar pais.
5. Obtener precio real (nunca confiar en el frontend).
6. Validar stock.
7. Validar cantidad.
8. Recalcular subtotal.
9. Calcular envio.
10. Recalcular total.

## Datos del pedido

`store_orders`: usuario, pais, productos (via items), cantidades, precio, subtotal,
envio, total, metodo de pago, estado, estado del pago, fecha e informacion de entrega.

## Estados del pedido (propuesta)

```
pendiente → confirmado → preparando → enviado → entregado
                                   ↘ cancelado
```

## Estados del pago (propuesta)

```
pendiente → en_verificacion → pagado
                           ↘ cancelado
```

> No marcar un pedido como **pagado** solo porque el usuario presiono "Comprar".

## Numeracion

- `order_number` unico por pais: `UNIQUE (country_id, order_number)`.
- Formato sugerido: `SG-{PAIS}-{AAAAMM}-{secuencial}`.

## Mis pedidos

- Solo el propietario ve sus pedidos (`user_id` de la sesion).
- Nunca permitir modificar pedidos ajenos.
