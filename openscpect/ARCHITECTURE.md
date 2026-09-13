# ARCHITECTURE · Arquitectura de SG Tienda

## Diagrama general

```
SGMensajeria  (referencia, intocable)
      │
      │  auditoria + copia inicial controlada
      ▼
SuplementosGym  (proyecto + base de datos independiente)
      │
      ▼
SG Tienda  (modulo publico)
```

## Principio de independencia

Una vez realizada la copia inicial:

- Modificar `suplementosgym` **NO** modifica `sgmensajeria`.
- Modificar `sgmensajeria` **NO** modifica `suplementosgym`.
- **No existe sincronizacion automatica** durante esta fase.

La independencia se garantiza por dos ejes:

1. **Codigo**: repositorios distintos.
   - `C:/xampp/htdocs/SGMensajeria`  → referencia.
   - `C:/xampp/htdocs/SuplementosGym` → SG Tienda.
2. **Datos**: bases de datos distintas.
   - `sgmensajeria` → origen (solo lectura).
   - `suplementosgym` → destino de SG Tienda.

## Patron de aplicacion

- **CodeIgniter 3 MVC**: controladores delgados, logica en modelos/librerias.
- **Base de datos**: Query Builder con placeholders. Nunca concatenar entrada de usuario.
- **Vistas**: `application/views/store/` con una plantilla publica `store_template.php`.
- **Assets**: `assets/css/store.css` y `assets/js/store.js` (propios de la tienda).

```
application/
  controllers/   Store, Cart, Checkout, Account, Store_auth, ...
  models/        Product_model, Store_order_model, Inventory_model, ...
  libraries/     Store_cart, Store_contact, ...
  core/          MY_Controller (compartido) - extender si hace falta
  views/store/   layouts + paginas publicas
assets/
  css/store.css
  js/store.js
  vendor/bootstrap, vendor/bootstrap-icons
openscpect/      documentacion viva
```

## Aislamiento de la tienda

- SG Tienda **no** consulta tablas de SGMensajeria en runtime: solo su propia base.
- El catalogo, pedidos e inventario de la tienda viven en `suplementosgym`.
- El pais seleccionado se guarda en sesion; cada consulta se filtra por `country_id`.

## Copia inicial (una sola vez)

```
sgmensajeria.products (country_id = 2)
        │  INSERT ... SELECT (columnas explicitas)
        ▼
suplementosgym.products (country_id = 2)
```

Despues de la copia, **no sincronizar automaticamente**. Cualquier actualizacion sera
un proceso explicito y controlado (ver `DATABASE.md`).

## Extension futura

La sincronizacion automatica entre SGMensajeria y SuplementosGym queda fuera de esta fase.
Podra evaluarse como una fase independiente de integracion.
