# PAYMENTS · Pagos

## Fuente

Metodos copiados de SGMensajeria en la tabla `payment_methods` (por pais).
No inventar informacion bancaria.

## Metodos por pais (segun SGMensajeria)

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
| `transferencia_bancaria` | Transferencia bancaria |
| `tarjeta` | Tarjeta |
| `pendiente_credito` | Pendiente de pago / Credito |

> Mostrar solo metodos **activos**, **disponibles** y del **pais activo**.

## Estados de pago

| Estado | Significado |
|---|---|
| `pendiente` | Pedido creado, sin pago confirmado. |
| `en_verificacion` | Comprobante recibido, en revision. |
| `pagado` | Pago validado. |
| `cancelado` | Pedido/pago anulado. |

**Regla clave:** nunca marcar como pagado solo porque el usuario presiono "Comprar".

## Formas de pago (pagina publica)

- Tarjetas visuales con **Bootstrap Icons**.
- Mostrar los metodos reales disponibles por pais.
- Sin datos bancarios inventados.

## Seguridad

- Nunca almacenar tarjetas.
- Nunca exponer tokens.
- Pagos transaccionales e idempotentes (patron de SGMensajeria con `reference`).
