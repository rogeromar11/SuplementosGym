# AI_CONTEXT · Contexto maestro para IAs

> Lee este documento **antes** de tocar cualquier archivo de SG Tienda.

## 1. Que es cada sistema

### SGMensajeria (referencia, intocable)
- Ruta: `C:/xampp/htdocs/SGMensajeria`
- Stack: CodeIgniter 3.x + IonAuth 3, PHP 8.1-8.5, MySQL/MariaDB, Bootstrap 5 + jQuery.
- Es un sistema de **mensajeria y rutas de entrega** multi-pais (Costa Rica / El Salvador),
  no una tienda. Su tabla `products` es el catalogo de insumos de reparto.
- **NO modificar, NO romper, NO eliminar datos.** Solo se lee para referencia y copia.

### SuplementosGym (destino)
- Ruta: `C:/xampp/htdocs/SuplementosGym` (repo git: `rogeromar11/SuplementosGym`, rama `develop`).
- Stack: CodeIgniter 3.4.2 (fork `pocketarc/codeigniter`) + IonAuth, PHPMailer 6.9,
  REST server, Bootstrap 5 local.
- Base de datos propia: **`suplementosgym`** en `localhost`, usuario `root`.
- Aqui vive **SG Tienda**.

### SG Tienda (el producto)
- Modulo publico de e-commerce de suplementos deportivos orientado a gimnasio,
  rendimiento, recuperacion y objetivos fisicos.
- Multi-pais: Costa Rica y El Salvador.

## 2. Decisiones tomadas (Fase 1)

| Tema | Decision |
|---|---|
| Ubicacion del codigo | En `SuplementosGym` (no dentro de SGMensajeria). |
| Documentacion | `SuplementosGym/openscpect/` (esta carpeta). |
| Base origen de la copia | `sgmensajeria` (54 productos, todos `country_id=2`). |
| Alcance multipais | Copia **tal cual**: el catalogo queda solo para El Salvador. Costa Rica arranca sin productos (mensaje de catalogo vacio). |
| Base destino | `suplementosgym` (localhost / root / sin password). |
| Contacto/WhatsApp | Placeholders configurables por pais. **No inventar datos reales.** |

## 3. Base de datos (resumen)

- La copia inicial trae: `countries`, `products`, `payment_methods` y el esquema IonAuth.
- SG Tienda agrega tablas propias de pedidos e inventario (ver `DATABASE.md` y `ORDERS.md`).
- Todos los montos son `DECIMAL(15,2)`. Nunca `FLOAT`.

## 4. Productos

- Tabla `products`: `id, country_id, sku, product_type, laboratory, name, weight, servings,
  flavor, description, cost_price, unit_price, is_active, stock_enabled, stock_qty`.
- **No existe campo de imagen.** Se agregaran campos de tienda en SuplementosGym.
- `product_type` es texto libre; se mapea a las 6 categorias de la tienda (ver `STORE.md`).

## 5. Usuarios

- IonAuth: `users` (+ `country_id`), `groups`, `users_groups`, `login_attempts`.
- Navegar la tienda es publico. **Para comprar se requiere cuenta.**

## 6. Pedidos e inventario

- Tablas propias de SG Tienda (no las de mensajeria).
- Agregar al carrito **no** descuenta stock. La compra confirmada descuenta inventario
  de `suplementosgym` con transaccion y sin doble descuento.

## 7. Pagos

- Metodos por pais copiados de SGMensajeria. Estados: pendiente, en verificacion,
  pagado, cancelado. Nunca marcar pagado solo por presionar "Comprar".

## 8. Paises

- CR `id=1` (CRC, ₡, +506), SV `id=2` (USD, $, +503). Ya existen en `countries`.
- No mezclar productos ni carrito entre paises.

## 9. Carrito y checkout

- Carrito por pais, validado server-side antes de crear pedido (producto, activo, pais,
  precio real, stock, cantidad, subtotal, envio, total).
- Checkout requiere autenticacion; el carrito se conserva tras login/registro.

## 10. Archivos importantes

```
application/controllers/Auth.php      Flujos de auth existentes (login/registro/recuperar)
application/config/database.php       Conexion (apunta a 'suplementosgym')
application/config/routes.php         Rutas de la tienda
application/views/auth/               Vistas de autenticacion base
assets/img/logo.png                   Logo
database/database.sql                 Esquema base IonAuth + cambios
database/upgrade_*.sql                Migraciones incrementales
openscpect/                           Esta documentacion
```

## 11. PROTOCOLO PARA FUTURAS IAs

1. Leer `AI_CONTEXT.md`
2. Leer `README.md`
3. Leer `ARCHITECTURE.md`
4. Leer `DATABASE.md`
5. Leer el documento relacionado con la tarea
6. Revisar el codigo real
7. Implementar
8. Probar
9. Actualizar la documentacion
10. Registrar cambios en `CHANGELOG.md`

**Reglas absolutas:** no asumir, no inventar, no romper. SGMensajeria es intocable.
