# PAYMENTS · Pagos

## Fuente

Metodos por pais en la tabla `payment_methods` (8 filas: 4 por pais), gestionables en el
backoffice (`/catalogs` → metodos de pago). No inventar informacion bancaria.

## Metodos por pais (segun la semilla)

### Costa Rica (id=1)
| code | nombre |
|---|---|
| `efectivo` | Efectivo |
| `transferencia_sinpe` | Transferencia / SINPE Movil |
| `tarjeta` | Tarjeta |
| `pendiente_credito` | Pendiente de pago / Credito |

### El Salvador (id=2)
| code | nombre |
|---|---|
| `efectivo` | Efectivo |
| `transferencia_sinpe` | Transferencia / SINPE Movil |
| `tarjeta` | Tarjeta |
| `pendiente_credito` | Pendiente de pago / Credito |

> Mostrar solo metodos **activos** y del **pais activo**.

## Estados de pago (reales)

| Estado | Significado |
|---|---|
| `pendiente` | Pedido creado, sin pago confirmado. |
| `parcial` | Pago parcial registrado; queda saldo. |
| `pagado` | Saldo cubierto en su totalidad. |

**Regla clave:** nunca marcar como pagado solo porque el usuario presiono "Comprar".

## Registro de pagos

`Payment_service::register_payment()` (transaccional e idempotente por referencia):

1. Inserta en `payments`.
2. Actualiza `store_orders.paid_amount`, `balance_amount` y `payment_status`.
3. Escribe `payment_history`.

Los comprobantes se guardan en `uploads/payment_receipts/` (`Order_service::store_payment_receipt`)
y se registran como adjuntos del pedido.

## Formas de pago (pagina publica)

- Tarjetas visuales con **Bootstrap Icons** en `/formas-de-pago`.
- Muestra los metodos reales disponibles por pais.
- Sin datos bancarios inventados.

## Depositos de efectivo (backoffice)

El efectivo cobrado por los mensajeros se controla en `/deposits`
(`cash_deposits` + `cash_deposit_routes`), con comprobantes en `uploads/deposit_receipts/`.
Estados: `pendiente → recibido → entregado → aprobado`. Ver `ADMIN.md`.

## Seguridad

- Nunca almacenar tarjetas.
- Nunca exponer tokens.
- Pagos transaccionales e idempotentes (patron con `reference`).
- No inventar informacion bancaria en el repositorio.
