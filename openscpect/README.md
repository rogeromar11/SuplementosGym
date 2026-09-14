# openscpect · Documentacion viva de SG Tienda

Esta carpeta es la **documentacion viva** del proyecto **SG Tienda**, la tienda online
de suplementos deportivos construida sobre el proyecto `SuplementosGym`.

> Ruta del proyecto: `C:/xampp/htdocs/SuplementosGym`
> Proyecto de referencia (solo lectura): `C:/xampp/htdocs/SGMensajeria`

## Que es cada cosa

| Nombre | Que es |
|---|---|
| **SGMensajeria** | Sistema de gestion de mensajeria y rutas de entrega. Proyecto de referencia. **Intocable.** |
| **SuplementosGym** | Proyecto CodeIgniter 3 + IonAuth independiente donde vive SG Tienda, con su propia base de datos. |
| **SG Tienda** | El modulo publico de e-commerce de suplementos que construimos dentro de SuplementosGym. |
| **openscpect** | Esta documentacion viva (nombre exacto solicitado del proyecto). |

## Indice de documentos

| Documento | Contenido |
|---|---|
| [AI_CONTEXT.md](AI_CONTEXT.md) | Contexto maestro + protocolo obligatorio para futuras IAs. |
| [AUDIT.md](AUDIT.md) | Auditoria de SGMensajeria realizada en Fase 1. |
| [PROJECT.md](PROJECT.md) | Vision, objetivos y alcance de SG Tienda. |
| [DESIGN.md](DESIGN.md) | Sistema de diseno (tipografia, color, movimiento). |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Publicacion en cPanel (assets autocontenidos). |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Arquitectura e independencia de datos. |
| [DATABASE.md](DATABASE.md) | Esquema de SGMensajeria (referencia) y de SuplementosGym (destino). |
| [STORE.md](STORE.md) | Catalogo, categorias, filtros, busqueda, carrito, checkout, WhatsApp. |
| [USERS.md](USERS.md) | Registro, login, perfil, mis pedidos. |
| [ORDERS.md](ORDERS.md) | Pedidos, estados, flujo de inventario y anti doble descuento. |
| [COUNTRIES.md](COUNTRIES.md) | Multipais Costa Rica / El Salvador. |
| [PAYMENTS.md](PAYMENTS.md) | Metodos y estados de pago. |
| [ROADMAP.md](ROADMAP.md) | Fases del proyecto con estados. |
| [CHANGELOG.md](CHANGELOG.md) | Registro de cambios. |
| [CONVENTIONS.md](CONVENTIONS.md) | Convenciones de codigo, nombres y estilos. |
| [INTEGRATIONS.md](INTEGRATIONS.md) | WhatsApp, correo, Google Fonts, assets. |
| [SECURITY.md](SECURITY.md) | Reglas de seguridad obligatorias. |
| [TESTING.md](TESTING.md) | Plan y checklist de pruebas. |

## Regla de oro

**SGMensajeria no se modifica.** SuplementosGym es el entorno independiente de datos y
codigo para SG Tienda. No existe sincronizacion automatica entre ambos en esta fase.
