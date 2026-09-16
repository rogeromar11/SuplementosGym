# ORDERS · Pedidos e inventario

## Alcance

La tienda usa tablas **propias**: `store_orders`, `store_order_items` e `inventory_movements`.
No usa las tablas de reparto de SGMensajeria. `store_orders` es la **unica fuente de verdad** de
pedidos (tanto los de la web como los cargados a mano en el backoffice).

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

Se usan **transacciones** de base de datos (`trans_begin` / `trans_commit` / `trans_rollback`).

## Regla del carrito

- **Agregar al carrito NO descuenta inventario.**
- El descuento ocurre solo al confirmar la compra.

## Proteccion contra stock negativo

Si `stock = 2` y se solicitan `3`:

- Rechazar la operacion con **HTTP 409**.
- Mensaje: **"La cantidad solicitada supera la disponibilidad actual."**
- Nunca permitir `stock < 0`.

## Proteccion contra doble descuento

- `store_orders.inventory_applied` (`0/1`) marca si el pedido ya desconto inventario.
- Antes de descontar, comprobar que `inventory_applied = 0`; actualizar el flag en la misma
  transaccion (`Inventory_model::apply_for_order` es idempotente).
- Al anular un pedido con inventario aplicado, `Order_service` repone el stock.

## Productos agotados

- `stock_enabled = 0` → disponible (sin control).
- `stock_enabled = 1` y `stock_qty <= 0` → **Agotado / Sin existencias**.
- Deshabilitar **Agregar al carrito** y no permitir compra.

## Validacion server-side del carrito (obligatoria)

Antes de crear el pedido:
1. Consultar producto real. 2. Validar existencia. 3. Validar activo. 4. Validar pais.
5. Obtener precio real (nunca confiar en el frontend). 6. Validar stock. 7. Validar cantidad.
8. Recalcular subtotal. 9. Calcular envio. 10. Recalcular total.

## Datos del pedido

`store_orders`: usuario (`user_id`), pais, cliente (`customer_*`), productos (via items),
cantidades, precio, subtotal, envio, total, metodo de pago, estado, estado del pago,
`paid_amount` / `balance_amount`, `inventory_applied`, datos de entrega y fechas.

## Estados del pedido (reales)

```
registrado → pendiente_preparacion → en_preparacion → preparado
           → asignado_ruta → en_ruta → entregado | no_entregado
                                          ↘ reprogramado
           ↘ cancelado
```

- Etiquetas en `order_status_label()` (`app_helper`).
- Cada transicion se registra en `order_status_history` (`Order_service::change_status`),
  que alimenta el seguimiento del cliente en `cuenta/pedidos`.
- `Order_service::create()` crea los pedidos con estado `registrado`.

## Estados de pago (reales)

`pendiente` → `parcial` → `pagado` (via `Payment_service::register_payment`, con historial en
`payment_history`).

> No marcar un pedido como **pagado** solo porque el usuario presiono "Comprar".

## Numeracion

- Web (carrito): `SG-<PAIS>-<AAAAMMDD>-<secuencial>` (`Store_order_model::next_number`).
- Backoffice (manual): `PED-<AAAAMMDD>-<secuencial>` (`Order_service`).
- `UNIQUE (country_id, order_number)`.

## Mis pedidos

- Solo el propietario ve sus pedidos (`user_id` de la sesion).
- `Store_order_model::find_for_user` filtra por `user_id` (404 si no es propio; sin IDOR).

## Backoffice

Ver `ADMIN.md` para el flujo completo: pedidos manuales (`/orders`), preparacion (`/preparation`),
rutas (`/routes`), entregas (`/courier`) y depositos (`/deposits`).
