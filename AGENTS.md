# AGENTS.md

Project rules for AI agents and developers.

## Stack

- **Framework**: CodeIgniter 3.4.2 (`pocketarc/codeigniter` fork) — never edit `system/`.
- **Auth**: IonAuth in `application/third_party/ion_auth` (registered as a package in `autoload.php`).
- **PHP**: >= 8.1 (dev on 8.2). The backoffice uses PHP 8.1+ features.
- **Email**: PHPMailer 6.9 via `application/libraries/MY_Email.php` (extends `CI_Email`, same API, falls back to native transport if vendor is missing).
- **API**: `chriskacerguis/codeigniter-restserver` — config in `application/config/rest.php`, example controller `application/controllers/Api.php`.
- **Reports**: Dompdf (`Pdf_service`) and PhpSpreadsheet (`Excel_service`) for PDF/Excel exports.
- **Composer**: autoloads root `vendor/autoload.php`. `composer.lock` is intentionally gitignored — pin versions in `composer.json` deliberately.

## Quick start

```bash
composer install
php -S localhost:8000
```

1. Set real values in `application/config/database.php` (committed placeholders: `root`/empty/`testdb`).
2. Import `database/suplementosgym.sql` (full schema + seed admins `admin@admin.com` and `admin@elsalvador.local`).
3. Configure `application/config/email.php` (SMTP) for real email delivery.

Useful URLs: storefront `/productos`, `/carrito`, `/cuenta`, customer login `/ingresar`;
backoffice login `/auth/login` (staff), dashboard `/dashboard`; health check `/api/ping`.

## Architecture

```
application/
  controllers/   Store/Auth/Cart/Checkout/Account (storefront) + admin/ (backoffice)
  models/        Country, Product, Store_order, Inventory, plus admin models
  libraries/     Store_cart, MY_Email, and admin services (Order, Route, Payment,
                 Delivery, Permission, Audit, Pdf, Excel, Map_link_parser...)
  core/          MY_Controller (storefront base), SG_Controller + role bases, MY_Model
  config/        database.php, email.php, rest.php, routes.php, config.php (subclass_prefix)
  views/         store/ (storefront), auth/ (IonAuth), errors/
  language/      english/ and spanish/ packs for auth, ion_auth, rest (default language: english)
  third_party/   ion_auth, sgadmin/views (backoffice views via package path)
database/        suplementosgym.sql (full schema + data) + limpiar_suplementosgym.sql
```

### Backoffice (`/admin`)

The storefront and the backoffice share one CodeIgniter app and one database.
`store_orders` is the **single source of truth** for orders (web + manual).

- **Controllers**: `application/controllers/admin/` (Auth, Dashboard, Users, Roles,
  Clients, Products, Warehouses, Catalogs, Orders, Preparation, Routes, Courier,
  Reports, Settings, Audit, Deposits). Routed by prefix in `routes.php`.
- **Views**: `application/third_party/sgadmin/views/` — loaded through a package path
  added in `SG_Controller` / admin `Auth`. Admin login views live in `admin_auth/`.
- **Core base classes**: `SG_Controller` (shared base: country session, settings,
  permissions, audit, JSON + layouts), `Authenticated_Controller`, `Admin_Controller`
  (group `admin`), `Courier_Controller` (group `mensajero`), plus `MY_Model`.
- **Permissions**: granular `permissions` + `group_permissions` tables with
  `Permission_service` / `has_permission()` / `require_permission()`. Groups:
  `admin`, `customer`, `vendedor`, `bodeguero`, `mensajero`, `auxiliar_admin`.
- **Order flow**: `pendiente_preparacion` → `en_preparacion` → `preparado` →
  `asignado_ruta` → `en_ruta` → `entregado`/`no_entregado` → `reprogramado`/`cancelado`.
  `order_status_history` drives customer tracking in `cuenta/pedidos`.
- **Clientes**: `clients.user_id` is a nullable FK to `users`; registered store users
  are homologated on registration/first order, manual clients have no account.
- **Uploads**: `uploads/delivery_evidence/`, `uploads/payment_receipts/`,
  `uploads/deposit_receipts/` (all block execution via `.htaccess`).

Conventions: controllers fill `$this->data` (title, message, per-field input arrays) and render through
`_render_page()`. REST endpoints are controller methods named `<resource>_<verb>` (e.g. `ping_get`).

## Git workflow

### Branches

- **`main`**: stable production. Merges only via PR. Note: it does not exist on origin yet — the
  current default branch is `develop`.
- **`develop`**: continuous integration. Base for all new work.
- **`feature/<name>`**: branched from `develop` for new features.
  Naming: `feature/login-social`, `feature/sales-reports`.
- **`hotfix/<name>`**: branched from `main` for critical production bugs.
  Naming: `hotfix/sql-injection-login`, `hotfix/pass-reset-500`.

### Merge rules (mandatory)

- **Never** merge/push directly to `main`.
- Every change is delivered as a **Pull Request**.
- Merging a PR into `main` is done **only** by the **repository owner** after review and approval.
- If an agent or collaborator opens the PR, it **stays pending review** and is **never self-merged**.
- Merging into `develop` may happen after review; when the work was done by an agent, record scope
  and tests performed in the PR description.

### Commits

- Clear messages in English, imperative form: `Add`, `Fix`, `Update`, `Remove`, `Refactor`, `Migrate`.
- Atomic commits: one responsibility per commit.
- Never commit secrets, credentials, or `vendor/`.

## Database

- **All** SQL lives in `database/`.
- `database/suplementosgym.sql` is the **complete schema + initial data** (from-scratch setup).
  It creates the database and every table (IonAuth, storefront and backoffice), plus seed data
  (countries, groups, permissions, base catalogs, settings and the dev admins).
  Run with `mysql -u root < database/suplementosgym.sql`.
- `database/limpiar_suplementosgym.sql` drops every application table (empty database reset) so the
  installer can be re-run. Run with `mysql -u root suplementosgym < database/limpiar_suplementosgym.sql`.
- **Mandatory**: every DB change must be reflected in the SQL scripts, not done "by hand" on a server.

### Reflecting a DB change

1. **Update** `database/suplementosgym.sql` with the change applied to the base schema.
2. Document the change (and any data migration) so it reaches existing installations.
3. Ship the updated script in the same PR.

## Coding practices

- **Follow CI3 MVC**: thin controllers, business logic in models or libraries
  (`application/libraries/`), no queries in views.
- **Never edit `system/`**: the CI3 core comes from the upstream fork. Extend via
  `application/core/MY_*` or `application/libraries/MY_*` (`config.php` sets `subclass_prefix = 'MY_'`).
- **Validation**: validate all user input server-side with `form_validation`.
  Never rely on client-side validation alone.
- **Escaping**: use `html_escape()` or `$this->security->xss_clean()` when printing data in views.
  Never print raw input.
- **SQL**: use the CI3 Query Builder with placeholders; never concatenate user input into SQL.
- **Errors**: handle and log with `log_message()`; never swallow exceptions.
- **Naming**: classes `Studly_Case` (CI3), methods `snake_case`, constants UPPER_CASE.
- **Composer**: add new dependencies with `composer require` and document their usage.
  Never edit `vendor/` by hand.

## Security

- Keep CSRF enabled (`csrf_protection = TRUE`) with the token present in every POST form,
  including auth AJAX.
  - Auth views add a second layer: a session-based CSRF nonce
    (`_get_csrf_nonce()` / `_valid_csrf_nonce()` in `Auth.php`). Keep both in place.
- Cookies: `httponly` + `samesite=Lax`; do not weaken without review.
- Passwords: bcrypt cost 12 (IonAuth config). Never invent custom hashing.
- Production: `display_errors = 0` (`index.php` already switches error display by `ENVIRONMENT`).
- Never log sensitive data (passwords, tokens, API keys).
- Never put real credentials in the repo: `database.php` and `email.php` ship dev placeholders on
  purpose. This codebase does not read env vars natively — keep credentials out of committed files
  and change them per environment.
- Root `.htaccess` already blocks `application/`, `system/`, and sensitive files: do not weaken it.

## Testing

- Before opening a PR, lint every changed PHP file: `php -l path/to/file.php`.
- Smoke-test the affected flow (e.g. login / register / forgot password via `php -S localhost:8000`)
  and leave evidence in the PR description.
- When changing security configuration, verify forms still work (CSRF token present, AJAX still
  returns JSON).

## Gotchas

- IonAuth only sends email when `use_ci_email = TRUE` in its config — currently `FALSE`, so
  `forgotten_password()` and registration return the code/data instead of sending mail. Enable it
  (and configure `email.php`) before expecting auth emails.
- `database/suplementosgym.sql` seeds default admin accounts with a well-known hash — rotate credentials in any
  real environment.
- Bilingual UI: language packs live in `application/language/english|spanish`; the default is
  `english`, so new user-facing strings need both packs.
- `models/` and `core/` are empty; do not assume every flow follows "controller → model" today.