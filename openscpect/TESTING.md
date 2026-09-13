# TESTING · Plan y checklist de pruebas

Registrar evidencia (capturas/notas) en el PR correspondiente.
No marcar un item como completado sin haberlo probado.

## SGMensajeria (proteccion)

- [ ] Continua funcionando.
- [ ] No se modificaron estructuras criticas innecesariamente.
- [ ] Ningun cambio de codigo/datos dentro de `C:/xampp/htdocs/SGMensajeria`.

## SuplementosGym

- [ ] Productos cargados.
- [ ] Datos correctos (SKU, nombre, laboratorio, precio, stock).
- [ ] Inventario independiente.
- [ ] Pedidos independientes.

## Paises

- [ ] Costa Rica.
- [ ] El Salvador.
- [ ] Cambio de pais.
- [ ] Productos correctos por pais.
- [ ] Inventario correcto por pais.

## Catalogo

- [ ] Categorias.
- [ ] Busqueda.
- [ ] Filtros.
- [ ] Producto individual.
- [ ] Producto agotado.
- [ ] Categoria vacia.
- [ ] Busqueda sin resultados.
- [ ] Catalogo vacio (pais sin productos).

## Usuarios

- [ ] Registro.
- [ ] Login.
- [ ] Logout.
- [ ] Perfil.
- [ ] Editar perfil.
- [ ] Cambiar contrasena.
- [ ] Mis pedidos.

## Carrito

- [ ] Agregar.
- [ ] Eliminar.
- [ ] Aumentar.
- [ ] Disminuir.
- [ ] Vaciar.
- [ ] Totales.
- [ ] No permite producto agotado.

## Checkout

- [ ] Requiere autenticacion.
- [ ] Conserva carrito.
- [ ] Datos precargados.
- [ ] Edita datos.
- [ ] Metodo de pago.
- [ ] Crea pedido.

## Inventario

- [ ] Agregar al carrito NO descuenta.
- [ ] Compra confirmada descuenta.
- [ ] Stock insuficiente.
- [ ] No stock negativo.
- [ ] No doble descuento.
- [ ] Transaccion.

## Aislamiento (obligatorio)

- [ ] Modificar SuplementosGym → SGMensajeria NO cambia.
- [ ] Compra de prueba → inventario SuplementosGym cambia y SGMensajeria NO.

## Seguridad

- [ ] Precio manipulado.
- [ ] Cantidad manipulada.
- [ ] Producto manipulado.
- [ ] Pais manipulado.
- [ ] Pedido ajeno.

## Responsive

- [ ] 320px.
- [ ] 375px.
- [ ] 390px.
- [ ] 414px.
- [ ] 768px.
- [ ] 1024px.
- [ ] 1366px.
- [ ] 1920px.

## Lint

- [ ] `php -l` en cada archivo PHP modificado.
