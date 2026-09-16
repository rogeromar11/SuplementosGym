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
| jQuery | `assets/vendor/jquery` |
| DataTables (+ lang `es-ES.json`) | `assets/vendor/datatables` |
| Select2 | `assets/vendor/select2` |
| SweetAlert2 | `assets/vendor/sweetalert2` |
| Chart.js | `assets/vendor/chartjs` |
| Leaflet (+ marker images) | `assets/vendor/leaflet` |
| GSAP + ScrollTrigger | `assets/vendor/gsap` |
| SortableJS | `assets/vendor/sortablejs` |
| Logo | `assets/img/logo.png` |
| Imagenes de producto | `assets/img/products/` |
| Video del hero | `assets/video/hero-web.mp4` |

> Todo local, sin instalar frameworks innecesarios.

## Fuentes

> **Storefront:** fuentes **locales** en `assets/fonts/` con `@font-face` en
> `assets/css/fonts.css` — **Anton** (display) y **Manrope** (texto). Sin CDN en runtime.

> **Pendiente (backoffice):** los layouts `sgadmin/layouts/{admin,courier,print}/header.php`
> y las plantillas de auth (`views/auth/auth_template.php`,
> `admin_auth/auth_template.php`) todavia cargan **Google Fonts (Fira Sans / Fira Code /
> Plus Jakarta Sans)** y, en las plantillas de auth, **jQuery y SweetAlert2 desde CDN**
> (jsdelivr / code.jquery.com). Conviene migrarlos a los assets locales para un despliegue
> 100% autocontenido.

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
