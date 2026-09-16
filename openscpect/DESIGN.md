# DESIGN · Sistema de diseno de SG Tienda

> Documento vivo. La identidad visual es **libre y evolutiva**: puede cambiar por
> completo. SGMensajeria solo se toma como referencia funcional, no de colores.

## Principios

1. **Moderno y con personalidad** — nada generico ni plantilla.
2. **Deportivo y energetico** — ritmo visual, contraste alto, tipografia con caracter.
3. **Profesional y confiable** — jerarquia clara, espaciado generoso, consistente.
4. **Autocontenido** — todo el CSS/JS/fuentes/imagenes se sirven localmente (cPanel).
5. **Accesible** — contraste AA, foco visible, HTML semantico, `prefers-reduced-motion`.

## Tipografia (autocontenida)

Las fuentes se sirven localmente desde `assets/fonts/` con `@font-face`
(`assets/css/fonts.css`). **No** se depende de Google Fonts en runtime.

| Rol | Familia | Uso |
|---|---|---|
| Display | **Anton** | Titulos, precios, numeros, botones |
| Texto | **Manrope** | Parrafos, formularios, descripciones |

Variables:

```css
--font-display: 'Anton', 'Arial Narrow', Impact, sans-serif;
--font-body: 'Manrope', system-ui, -apple-system, 'Segoe UI', sans-serif;
```

## Origen del diseno

El tema base proviene de la carpeta `C:/xampp/htdocs/Diseño` (landing
"Suplementos Gym"): storefront oscuro con video en el hero, marquee, catalogo,
promo, beneficios con contadores, tiendas, resenas y CTA final, animado con
**GSAP + ScrollTrigger** (locales).

- Tema base: `assets/css/design.css` (copiado de `Diseño/css/style.css`).
- Capa funcional de SG Tienda: `assets/css/store.css` (carrito, checkout,
  cuenta, filtros, auth, etc.) usando los mismos tokens.
- Animaciones: `assets/js/landing.js` + `store.js`.
- Assets: `assets/video/hero-web.mp4`, `assets/img/hero-poster.jpg`,
  `assets/vendor/gsap/`.

## Color

Paleta del tema (dark + rojo):

```css
--bg:       #0A0A0B;
--surface:  #121317;
--text:     #F6F6F5;
--muted:    #A2A2A8;
--red:      #E62E2E;   /* marca */
--red-2:    #FF3D3D;
--wa:       #1DA851;   /* WhatsApp */
--gold:     #FFC94A;   /* estrellas */
--radius:   20px;
```

## Espaciado y forma

- Contenedor: `min(1240px, 100% - 2.5rem)`.
- Radios: `--radius: 16px`, `--radius-sm: 10px`, pills para chips/botones secundarios.
- Sombras suaves en capas: `--shadow-sm/md/lg`.
- Secciones: `padding` vertical generoso (3.5–5rem) y cabeceras con `eyebrow`.

## Movimiento (animaciones)

- **Reveal on scroll** con `IntersectionObserver` (`.reveal` → `.visible`).
- **Contadores animados** en estadisticas del hero/marcas.
- **Marquee** de categorias/marcas en la home.
- **Hover**: elevacion sutil, zoom de imagen, subrayados animados.
- **Navbar**: reacciona al scroll (compacta / eleva sombra).
- Respetar `prefers-reduced-motion` (se desactivan animaciones).

## Componentes clave

- Navbar oscuro sticky + selector de pais con banderas.
- Hero oscuro con fondo animado y tarjeta de producto flotante.
- Tarjetas: producto, categoria, beneficio, guia, objetivo, paso, pago.
- Acordeon de FAQ, filtros laterales (derecha) y resumen de carrito sticky.
- Estados vacios (catalogo, categoria, busqueda).

## Como cambiar el tema

1. Editar las variables en `:root` (`assets/css/store.css`).
2. Cambiar familias en `assets/css/fonts.css` si se reemplazan las fuentes.
3. Mantener todos los `data-*` que usa `assets/js/store.js`.

## Backoffice

El panel interno tiene una identidad **funcional y sobria**, distinta del storefront:

- CSS: `assets/css/app.css` (+ `courier.css` para la vista movil y `print.css` para impresion).
- UI: Bootstrap 5 + Bootstrap Icons locales, con librerias locales (DataTables, Select2,
  SweetAlert2, Chart.js, Leaflet, SortableJS).
- Tipografia: Fira Sans / Fira Code (**aun por Google Fonts**; ver `INTEGRATIONS.md`).
- Layouts: `application/third_party/sgadmin/views/layouts/{admin,courier,print}/`.
- Scripts por pagina en `assets/js/pages/` (orders, routes, products, deposits, ...).
- Vistas: `application/third_party/sgadmin/views/`.

## Archivos

| Recurso | Ruta |
|---|---|
| Tema base storefront | `assets/css/design.css` |
| Capa funcional storefront | `assets/css/store.css` |
| CSS backoffice | `assets/css/app.css`, `courier.css`, `print.css` |
| Fuentes | `assets/fonts/*` + `assets/css/fonts.css` |
| JS storefront | `assets/js/store.js`, `landing.js`, `calculator.js` |
| JS backoffice | `assets/js/pages/*` |
| GSAP | `assets/vendor/gsap/` |
| Imagenes de producto | `assets/img/products/` |
| Video + poster | `assets/video/hero-web.mp4`, `assets/img/hero-poster.jpg` |
