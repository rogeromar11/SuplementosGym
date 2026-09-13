# DATABASE · Base de datos

Documenta de forma clara **que pertenece a SGMensajeria** y **que pertenece a
SuplementosGym**, y como se realiza la copia inicial.

## A. SGMensajeria (referencia, solo lectura)

Base de referencia: **`sgmensajeria`** (localhost). 54 productos, todos `country_id=2`.

### Tablas originales usadas como referencia

| Tabla | Uso para SG Tienda |
|---|---|
| `countries` | Catalogo de paises (CR/SV). Se copia. |
| `products` | Catalogo de suplementos. Se copia (solo SV). |
| `payment_methods` | Formas de pago por pais. Se copia. |
| `users`, `groups`, `users_groups`, `login_attempts` | Esquema IonAuth. Se recrea equivalente. |
| `orders`, `order_items` | Referencia de patron (no se copian datos). |

### Relaciones relevantes

```
countries (1)──< products.country_id
countries (1)──< payment_methods.country_id
countries (1)──< users.country_id
products  (1)──< order_items.product_id      (solo referencia)
```

### Campos relevantes de `products`

```
id, country_id, sku, product_type, laboratory, name,
weight, servings, flavor, description,
cost_price, unit_price, is_active, stock_enabled, stock_qty,
created_at, updated_at, created_by, updated_by
```

## B. SuplementosGym (destino, independiente)

Base destino: **`suplementosgym`** (localhost, usuario `root`, sin password).
Configurada en `application/config/database.php`.

> **Estado (Fases 2-3, 2026-09-12):** base creada, esquema importado y datos migrados.
> Tablas presentes: `countries`, `groups`, `users`, `users_groups`, `login_attempts`,
> `products` (54 de El Salvador), `payment_methods` (8), `store_orders`,
> `store_order_items`, `inventory_movements`, `store_settings` (10).
> Base en estado limpio: 0 pedidos, 0 movimientos, 1 usuario (admin de desarrollo).
>
> Scripts: `database/database.sql` (esquema base),
> `database/upgrade_add_store_schema_20260912.sql` (esquema incremental),
> `database/upgrade_migrate_initial_data_20260912.sql` (copia inicial de datos).

### Tablas IonAuth (base existente de `database/database.sql`)

`groups`, `users`, `users_groups`, `login_attempts`.

Adicion para la tienda:
- Grupo `customer` en `groups`.
- `users.country_id` para separar clientes por pais (si no existe en el esqueleto base).

### Tablas de catalogo

**`countries`** (copiada de SGMensajeria)

```
id, code, name, currency, currency_symbol, phone_code, timezone, is_active, sort_order
```

**`products`** (copiada + campos de tienda)

Columnas de negocio originales (referencia):

```
id, country_id, sku, product_type, laboratory, name,
weight, servings, flavor, description,
cost_price, unit_price, is_active, stock_enabled, stock_qty,
created_at, updated_at, created_by, updated_by
```

Campos adicionales de tienda (a agregar en SuplementosGym):

```
store_description   TEXT NULL
image               VARCHAR(255) NULL
featured            TINYINT(1) NOT NULL DEFAULT 0
sort_order          INT NOT NULL DEFAULT 0
seo_title           VARCHAR(150) NULL
seo_description     VARCHAR(255) NULL
```

**`payment_methods`** (copiada de SGMensajeria)

```
id, country_id, code, name, is_active, sort_order
UNIQUE (country_id, code)
```

### Tablas propias de SG Tienda (a crear)

**`store_orders`** — pedido e-commerce

```
id, country_id, user_id, order_number,
customer_name, customer_email, customer_phone, customer_phone2,
delivery_zone, delivery_address,
subtotal, shipping, total,
payment_method_id, payment_status, status,
inventory_applied TINYINT(1) DEFAULT 0,   -- anti doble descuento
notes, created_at, updated_at
UNIQUE (country_id, order_number)
```

**`store_order_items`** — lineas del pedido (copian datos para historico)

```
id, order_id, product_id,
item_sku, item_name, unit_price, quantity, line_total, created_at
```

**`inventory_movements`** — movimientos de inventario

```
id, product_id, order_id NULL, type, quantity, previous_qty, new_qty,
reason, created_by, created_at
```

**`store_settings`** — configuracion por pais (contacto, WhatsApp, envio)

```
id, country_id, key, value, description, updated_at
UNIQUE (country_id, key)
```

Ejemplos de `key`: `whatsapp_number`, `contact_email`, `business_hours`,
`shipping_cost`, `free_shipping_from`.

## C. Copia inicial (INSERT SELECT)

Despues de auditar, la copia se realiza con **columnas explicitas** (nunca `SELECT *`):

```sql
INSERT INTO suplementosgym.products (
    country_id, sku, product_type, laboratory, name,
    weight, servings, flavor, description,
    cost_price, unit_price, is_active, stock_enabled, stock_qty,
    created_at, updated_at
)
SELECT
    country_id, sku, product_type, laboratory, name,
    weight, servings, flavor, description,
    cost_price, unit_price, is_active, stock_enabled, stock_qty,
    created_at, updated_at
FROM sgmensajeria.products
WHERE country_id = 2
  AND is_active = 1;
```

Reglas de la copia:
- Evitar duplicados (destino vacio o `sku` unico por pais).
- Respetar tipos y `DECIMAL(15,2)`.
- Conservar SKU y precios.
- No copiar pedidos, clientes, rutas ni inventario de mensajeria.
- Documentar cuantas filas se copiaron.

Idempotencia sugerida (opcion):

```sql
INSERT INTO suplementosgym.products (...)
SELECT ... FROM sgmensajeria.products p
WHERE p.country_id = 2 AND p.is_active = 1
  AND NOT EXISTS (
    SELECT 1 FROM suplementosgym.products d
    WHERE d.country_id = p.country_id AND d.sku = p.sku
  );
```

## D. Reglas de datos

- Montos: `DECIMAL(15,2)`.
- Baja logica con `is_active`.
- Inventario solo en SuplementosGym.
- Nunca stock negativo.
- Toda migracion se guarda en `database/` (`database.sql` + `upgrade_*.sql`).
