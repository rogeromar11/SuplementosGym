# AUDIT · Auditoria de SGMensajeria (Fase 1)

Fecha: 2026-09-12
Auditor: IA (opencode)
Alcance: `C:/xampp/htdocs/SGMensajeria` (referencia) y `C:/xampp/htdocs/SuplementosGym` (destino).

## 1. Arquitectura de SGMensajeria

- **CodeIgniter 3.x** + **IonAuth 3**, PHP `>=8.1 <8.6`, MySQL/MariaDB.
- Frontend: Bootstrap 5, jQuery, DataTables, Select2, SweetAlert2, Chart.js, Leaflet.
- MVC + `MY_Controller`/`MY_Model`; logica de negocio en `application/libraries/`
  (`Order_service`, `Payment_service`, `Route_service`, `Product_import_service`,
  `Permission_service`, `Audit_service`, `Delivery_service`, etc.).
- Multi-pais mediante `countries` + helper `current_country_id()`.
- Autenticacion y permisos: IonAuth + matriz granular (`permissions`, `group_permissions`).

## 2. Base de datos (29 tablas)

InnoDB, `utf8mb4_unicode_ci`, montos `DECIMAL(15,2)`, baja logica con `is_active`.

Raiz multi-pais: `users`, `orders`, `routes`, `warehouses`, `products`,
`payment_methods`, `delivery_failure_reasons`, `route_shifts`, `system_settings`,
`audit_logs`.

IonAuth: `groups`, `users`, `users_groups`, `login_attempts`.

Operacion: `clients`, `orders`, `order_items`, `order_status_history`,
`warehouse_preparation_history`, `routes`, `route_orders`, `route_transfer_history`,
`delivery_attempts`, `payments`, `payment_history`, `attachments`, `system_settings`.

Depositos: `cash_deposits`, `cash_deposit_routes`.

## 3. Bases de datos existentes en el servidor

| Base | Productos | Pedidos | Usuarios | Notas |
|---|---|---|---|---|
| `sgmensajeria` | 54 | 42 | 7 | **Fuente elegida**. Todos los productos son `country_id=2`. |
| `sgmensajeria2` | 62 | 35 | 10 | Incluye 8 productos demo (regalos) + 54 suplementos. Documentada en README. |
| `sg_doc` | 0 | 0 | 2 | Sin datos de negocio. |

> No existe archivo `.env` en SGMensajeria; el runtime por defecto usa `sgmensajeria`.

## 4. Estructura real de productos

```
products(
  id, country_id, sku, product_type, laboratory, name,
  weight, servings, flavor, description,
  cost_price, unit_price, is_active, stock_enabled, stock_qty,
  created_at, updated_at, created_by, updated_by
)
UNIQUE (country_id, sku)
FK country_id -> countries(id)
```

Hallazgos:
- **No existe campo de imagen** (`image`/`foto`). Hay que crearlo en SuplementosGym.
- `product_type` es texto libre; valores reales: `Proteina`, `BCAA`, `Pre - entreno`,
  `Creatina`, `Quemador`, `Mass Gainer`, `Multivitamínicos`, `CLA`, `OMEGA 3`,
  `MAGNESIUM GLYCINATE`, `Precursor`, `ASHWAGANDHA` (requieren mapeo a categorias).
- Laboratorios reales: Dymatize, ON, Nutrex, Mutant, Angry, Cellucor, Terror Lab,
  Rencon, Rule one, Animal pack, Now, Source Naturals, Naturewise, Muscle tech.

## 5. Inventario real

- Vive en `products.stock_enabled` (`1/0`) + `products.stock_qty`.
- No hay tabla de movimientos de inventario.
- Los pedidos de mensajeria no descuentan stock automaticamente.
- **Conclusion:** SG Tienda necesita inventario y movimientos propios.

## 6. Usuarios

- IonAuth con `country_id`, `email` unico por pais, `phone`, `avatar`, `active`.
- Registro publico ya implementado en `Auth::register()`.
- Grupos de mensajeria (admin, vendedor, bodeguero, mensajero) **no aplican** a la tienda;
  SG Tienda usara un grupo `customer`.

## 7. Pedidos

- `orders` es de logistica de reparto (cliente, direccion, coordenadas, bodega,
  vendedor, estados de ruta). **No encaja** con pedido e-commerce.
- `order_items` copia nombre/precio (util como referencia de patron).
- **Conclusion:** crear tablas propias `store_orders` / `store_order_items`.

## 8. Pagos

- `payment_methods` por pais: CR (efectivo, SINPE, tarjeta, credito),
  SV (efectivo, transferencia, tarjeta, credito).
- `payments` / `payment_history` con idempotencia por `reference`.
- No hay datos bancarios reales en el repositorio.

## 9. Paises

| id | code | nombre | moneda | simbolo | phone_code | timezone |
|---|---|---|---|---|---|---|
| 1 | CR | Costa Rica | CRC | ₡ | 506 | America/Costa_Rica |
| 2 | SV | El Salvador | USD | $ | 503 | America/El_Salvador |

## 10. Hallazgo critico multipais

El catalogo de suplementos existe **solo para El Salvador (`country_id=2`)**.
Costa Rica no tiene suplementos. **Decision:** copiar tal cual (solo SV); Costa Rica
mostrara el mensaje de catalogo vacio hasta cargar productos.

## 11. Recursos reutilizables

- `assets/vendor/bootstrap`, `assets/vendor/bootstrap-icons` (copiar a SuplementosGym).
- Patrones de controlador/vista de `Auth.php` y `views/auth/`.
- Esquema multi-pais (`countries`, `payment_methods`) y helper de pais.
- Documentacion existente: `docs/DICCIONARIO_DE_DATOS.md`, `md/*.md`.

## 12. Riesgos

| Riesgo | Mitigacion |
|---|---|
| Modificar SGMensajeria por accidente | Trabajar solo en SuplementosGym; SGMensajeria en solo lectura. |
| Confundir bases (`sgmensajeria` / `sgmensajeria2`) | Fijar `suplementosgym` en `database.php` y documentar. |
| Productos de CR ausentes | Mensaje de catalogo vacio + carga posterior controlada. |
| Falta de imagenes de producto | Agregar campos de imagen y un placeholder local. |
| Duplicar IDs/constraints al copiar | Copiar con columnas explicitas; validar `sku` unico por pais. |

## 13. Plan de implementacion

1. Crear base `suplementosgym` y esquema (IonAuth + countries + products + payment_methods).
2. Copiar catalogo de `sgmensajeria` (solo `country_id=2`, activos) con `INSERT ... SELECT`.
3. Agregar campos de tienda a `products` y crear tablas de pedidos/inventario.
4. Implementar la tienda publica (frontend, catalogo, carrito, checkout, cuenta).
5. Probar aislamiento y flujos.
6. Actualizar `openscpect/`.

Ver detalle en `DATABASE.md`, `ROADMAP.md` y `TESTING.md`.
