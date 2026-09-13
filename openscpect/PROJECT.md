# PROJECT · SG Tienda

## Vision

SG Tienda es una tienda online profesional de **suplementos deportivos** orientada a
gimnasio, entrenamiento, rendimiento, recuperacion y objetivos fisicos, con presencia en
**Costa Rica** y **El Salvador**.

## Objetivo general

Desarrollar, dentro del proyecto CodeIgniter `SuplementosGym`, un modulo publico de
e-commerce que replique visual y funcionalmente el estilo del ecosistema SG, pero con
**datos totalmente independientes** de SGMensajeria durante la fase de pruebas.

## Objetivos especificos

1. Catalogar los suplementos provenientes de SGMensajeria en una base propia (`suplementosgym`).
2. Permitir la compra online con cuenta de usuario, carrito y checkout.
3. Gestionar inventario propio (aislado del inventario de SGMensajeria).
4. Soportar multipais (CR / SV) sin mezclar datos.
5. Centralizar el contacto comercial via WhatsApp, correo y redes.
6. Cumplir estandares de seguridad, accesibilidad, SEO y responsive.

## Alcance

### Incluye
- Inicio, Productos, Producto individual, Carrito, Checkout, Registro, Login,
  Mi perfil, Mis pedidos, Sobre nosotros, Formas de pago, Contacto, Footer.
- Busqueda, filtros, categorias, selector de pais.
- Registro/login con IonAuth.
- Pedidos e inventario propios.
- Integracion WhatsApp.

### No incluye (esta fase)
- Sincronizacion automatica con SGMensajeria.
- Pasarela de pago en linea (se registran pagos manuales/verificables).
- Panel administrativo avanzado (se prioriza la tienda publica).
- Facturacion electronica.

## Identidad visual

```css
:root {
  --brand: #DC2626;
  --brand-hover: #B91C1C;
  --brand-soft: #FEF2F2;
  --black: #0B0B0F;
  --black-2: #141419;
  --black-3: #1C1C23;
  --ink: #111114;
  --muted: #52525B;
  --line: #E4E4E7;
  --bg: #F4F4F5;
  --focus: rgba(220, 38, 38, 0.22);
  --radius: 12px;
}
```

Estetica: profesional, premium, deportiva, moderna, minimalista, orientada a conversion.
**Sin** gradientes morados o azules.

Tipografias:
- **Fira Code**: titulos, botones, precios, elementos destacados.
- **Fira Sans**: parrafos, formularios, descripciones, contenido general.

## Prioridades

1. Seguridad.
2. Aislamiento de SGMensajeria.
3. Integridad de `suplementosgym`.
4. Inventario.
5. Pedidos.
6. Pagos.
7. Multipais.
8. Experiencia de usuario.
9. Conversion.
10. Diseno.
11. SEO.
12. Rendimiento.
