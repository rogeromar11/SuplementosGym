# INTEGRATIONS · Integraciones y assets

## WhatsApp

- Numero centralizado por pais en `store_settings`:
  - `whatsapp_number` (CR y SV).
- Mensajes:
  - Producto: `Hola, estoy interesado en el producto: [PRODUCTO].`
  - Pedido: `Hola, acabo de realizar el pedido #[NUMERO].`
- **Carrito**: "Finalizar por WhatsApp" envia el detalle escrito del pedido
  (productos, cantidades, subtotal, envio, total) via `store_cart_whatsapp_url()`.
- **Checkout**: "Pedir por WhatsApp" envia productos + datos de entrega del
  formulario (`waCheckoutData`).
- **Boton flotante** `.fab-wa` en la esquina inferior derecha (global).
- El numero se adapta segun el pais activo.
- **Placeholders actuales** (reemplazar): CR `50688888888`, SV `50377777777`.

## Correo

- PHPMailer 6.9 via `application/libraries/MY_Email.php`.
- Configuracion SMTP en `application/config/email.php`.
- Usar para recuperacion de contrasena y notificaciones de pedido.
- No inventar direccion de correo: configurable (`store_settings.contact_email`).

## Assets locales

| Recurso | Ruta |
|---|---|
| Bootstrap 5 | `assets/vendor/bootstrap` |
| Bootstrap Icons | `assets/vendor/bootstrap-icons` |
| Logo | `assets/img/logo.png` |
| Imagenes de producto | `assets/img/products/` |

> Copiar `bootstrap` y `bootstrap-icons` desde SGMensajeria (`assets/vendor/`) o
> usar los disponibles. No instalar frameworks innecesarios.

## Google Fonts

> **Unificado:** las fuentes se sirven **localmente** desde `assets/fonts/`
> con `@font-face` en `assets/css/fonts.css`. Actualmente **Anton** (display) y
> **Manrope** (texto). No se usa CDN en runtime. Ver `DESIGN.md`.

## Diseno y animaciones

| Recurso | Ruta |
|---|---|
| Tema base (carpeta `Diseño`) | `assets/css/design.css` |
| Capa funcional SG Tienda | `assets/css/store.css` |
| Animaciones (GSAP/ScrollTrigger) | `assets/vendor/gsap/` + `assets/js/landing.js` |
| Hero video + poster | `assets/video/hero-web.mp4`, `assets/img/hero-poster.jpg` |

## REST server

- `chriskacerguis/codeigniter-restserver` ya presente.
- Endpoints de tienda (si se requieren) como metodos `<resource>_<verb>`.

## Env / configuracion

- No hay carga nativa de variables de entorno en el esqueleto base; usar
  `application/config/*` y `store_settings` para datos de contacto.
- Nunca poner credenciales reales en archivos versionados.

## Contacto publico (placeholders)

| Dato | Fuente |
|---|---|
| WhatsApp | `store_settings.whatsapp_number` |
| Correo | `store_settings.contact_email` |
| Horario | `store_settings.business_hours` |
| Envio | `store_settings.shipping_cost`, `free_shipping_from` |

> Estos valores se cargan con placeholders y se editan cuando el negocio los confirme.
