# ROADMAP · Fases del proyecto

Estados: `[x]` completado · `[~]` en progreso · `[ ]` pendiente.
No marcar como completado algo que no haya sido probado.

```
[~] Fase 1  — Auditoria
[ ] Fase 2  — Creacion SuplementosGym
[ ] Fase 3  — Migracion inicial
[ ] Fase 4  — OpenSpec (openscpect)
[ ] Fase 5  — Arquitectura
[ ] Fase 6  — Frontend
[ ] Fase 7  — Catalogo
[ ] Fase 8  — Multipais
[ ] Fase 9  — Usuarios
[ ] Fase 10 — Carrito
[ ] Fase 11 — Checkout
[ ] Fase 12 — Pedidos
[ ] Fase 13 — Inventario
[ ] Fase 14 — Pagos
[ ] Fase 15 — Perfil
[ ] Fase 16 — Pruebas
[ ] Fase 17 — Optimizacion
```

## Detalle

### Fase 1 — Auditoria  `[~]`
- [x] Auditar SGMensajeria (modulos, MVC, assets).
- [x] Auditar base de datos real.
- [x] Auditar productos, inventario, usuarios, pedidos, pagos, paises.
- [x] Auditar assets/CSS/JS reutilizables.
- [x] Registrar decisiones (ubicacion, DB origen/destino, paises, contacto).
- [x] Crear `openscpect/` con la documentacion inicial.
- [ ] Cerrar y entregar el diagnostico.

### Fase 2 — Creacion SuplementosGym  `[ ]`
- [ ] Crear base `suplementosgym`.
- [ ] Cargar esquema (`database.sql` + `upgrade_*.sql`).
- [ ] Configurar `application/config/database.php`.
- [ ] Agregar campos de tienda y tablas propias.

### Fase 3 — Migracion inicial  `[ ]`
- [ ] Copiar `countries` (si falta).
- [ ] Copiar `payment_methods`.
- [ ] Copiar `products` de `sgmensajeria` (solo `country_id=2`, activos).
- [ ] Verificar conteos e integridad.

### Fase 4 — openscpect  `[~]`
- [x] Crear los 16 documentos base.
- [ ] Mantener actualizado en cada fase.

### Fase 5 — Arquitectura  `[ ]`
- [ ] Controladores, modelos, librerias y vistas de la tienda.
- [ ] Plantilla publica y pipeline de assets.

### Fase 6 — Frontend  `[ ]`
- [ ] Identidad visual, tipografias, navbar, hero, footer, responsive.

### Fase 7 — Catalogo  `[ ]`
- [ ] Listado, categorias, busqueda, filtros, producto individual, estados vacios.

### Fase 8 — Multipais  `[ ]`
- [ ] Selector, persistencia, aislamiento, carrito y cambio de pais.

### Fase 9 — Usuarios  `[ ]`
- [ ] Registro, login, perfil, editar perfil, cambiar contrasena.

### Fase 10 — Carrito  `[ ]`
- [ ] Agregar/eliminar/aumentar/disminuir/vaciar, totales, validacion server-side.

### Fase 11 — Checkout  `[ ]`
- [ ] Requiere auth, conserva carrito, precarga/edita datos, metodo de pago.

### Fase 12 — Pedidos  `[ ]`
- [ ] Creacion, numeracion, mis pedidos, estados.

### Fase 13 — Inventario  `[ ]`
- [ ] Descuento transaccional, sin stock negativo, sin doble descuento.

### Fase 14 — Pagos  `[ ]`
- [ ] Metodos por pais, estados, pagina de formas de pago.

### Fase 15 — Perfil  `[ ]`
- [ ] Mi perfil y mis pedidos completos.

### Fase 16 — Pruebas  `[ ]`
- [ ] Ejecutar checklist de `TESTING.md` y prueba de aislamiento.

### Fase 17 — Optimizacion  `[ ]`
- [ ] SEO, rendimiento, accesibilidad, responsive fino.
