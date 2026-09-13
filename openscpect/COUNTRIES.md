# COUNTRIES · Multipais

## Paises soportados

| Pais | id | Code | Moneda | Simbolo | Phone code | Timezone |
|---|---|---|---|---|---|---|
| Costa Rica 🇨🇷 | 1 | `CR` | CRC | ₡ | 506 | America/Costa_Rica |
| El Salvador 🇸🇻 | 2 | `SV` | USD | $ | 503 | America/El_Salvador |

Definidos en la tabla `countries` (copiada de SGMensajeria).

## Estado del catalogo por pais (copia inicial)

- **El Salvador (`id=2`)**: 54 productos de suplementos copiados.
- **Costa Rica (`id=1`)**: sin productos en esta fase.

> Decision de Fase 1: copiar tal cual (solo SV). Costa Rica mostrara
> **"Actualmente no hay productos disponibles para este pais."**

## Selector de pais

- Visible en el navbar como `🇨🇷 Costa Rica` / `🇸🇻 El Salvador`.
- Al cambiar de pais se actualizan: catalogo, inventario, precios, moneda,
  metodos de pago e informacion de entrega.

## Persistencia de la seleccion

- Guardar el pais seleccionado en **sesion** (y opcionalmente cookie).
- Default inicial: El Salvador (`id=2`), por ser el pais con catalogo disponible.
- Al cambiar de pais, refrescar el catalogo.

## Aislamiento

- Cada consulta se filtra por `country_id`.
- No mezclar informacion entre paises.
- No permitir carrito con productos de dos paises.

## Carrito y cambio de pais

Si hay productos en el carrito y el usuario cambia de pais:

1. Mostrar confirmacion:
   **"Al cambiar de pais, los productos actuales del carrito podrian dejar de estar
   disponibles. ¿Deseas continuar?"**
2. Si acepta, vaciar el carrito y cambiar el pais.
3. Si cancela, conservar el carrito y el pais.

## Moneda y formato

- CR: `₡` con separador de miles costarricense.
- SV: `$` con formato USD.
- Formato segun pais activo.

## Envio

- Configurable por pais en `store_settings` (`shipping_cost`, `free_shipping_from`).
- No inventar montos: usar placeholders configurables hasta tener valores reales.
