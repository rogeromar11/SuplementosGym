<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html_escape($title ?? 'Acceso'); ?> · <?php echo html_escape($appName ?? 'SGMensajeria'); ?></title>
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/img/logo.png'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600;700&family=Fira+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/bootstrap-icons/bootstrap-icons.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.css'); ?>">
    <?php
        $auth_variant = $auth_variant ?? 'login';
        $messageText = trim(strip_tags((string) ($message ?? '')));

        // Datos de empresa para el bloque de ayuda (carga perezosa)
        $_ci =& get_instance();
        if (!isset($_ci->settings) || !is_array($_ci->settings)) {
            $_ci->load->model('Settings_model');
            $_ci->settings = $_ci->Settings_model->all_key_value();
        }
        $companyPhone = $_ci->settings['company_phone'] ?? '';
        $companyEmail = $_ci->settings['company_email'] ?? '';
        $companyName  = $_ci->settings['company_name'] ?? 'SGMensajeria';

        $config = [
            'login' => [
                'eyebrow' => 'Acceso seguro',
                'heading' => 'Iniciar sesión',
                'description' => 'Ingresa tus credenciales para continuar con tus entregas.',
                'form_id' => 'loginForm',
                'endpoint' => base_url('auth/login'),
                'success_title' => 'Sesión iniciada',
                'submit' => 'Iniciar sesión',
                'loading' => 'Ingresando...',
                'ajax' => TRUE,
            ],
            'register' => [
                'eyebrow' => 'Registro',
                'heading' => 'Crear cuenta',
                'description' => 'Completa tus datos para crear un acceso nuevo.',
                'form_id' => 'registerForm',
                'endpoint' => base_url('auth/register'),
                'success_title' => 'Cuenta creada',
                'submit' => 'Crear cuenta',
                'loading' => 'Creando cuenta...',
                'ajax' => TRUE,
            ],
            'forgot_password' => [
                'eyebrow' => 'Recuperación',
                'heading' => 'Recuperar acceso',
                'description' => 'Escribe tu correo y te enviaremos las instrucciones para restablecer tu contraseña.',
                'form_id' => 'forgotForm',
                'endpoint' => base_url('auth/forgot_password'),
                'success_title' => 'Solicitud recibida',
                'submit' => 'Enviar instrucciones',
                'loading' => 'Enviando...',
                'ajax' => TRUE,
            ],
            'reset_password' => [
                'eyebrow' => 'Nueva contraseña',
                'heading' => 'Restablecer acceso',
                'description' => 'Define una contraseña nueva para tu cuenta.',
                'form_id' => 'resetPasswordForm',
                'endpoint' => base_url('auth/reset_password/' . ($code ?? '')),
                'success_title' => 'Contraseña actualizada',
                'submit' => 'Actualizar contraseña',
                'loading' => 'Actualizando...',
                'ajax' => FALSE,
            ],
            'change_password' => [
                'eyebrow' => 'Seguridad',
                'heading' => 'Cambiar contraseña',
                'description' => 'Ingresa tu contraseña actual y define una contraseña nueva.',
                'form_id' => 'changePasswordForm',
                'endpoint' => base_url('auth/change_password'),
                'success_title' => 'Contraseña actualizada',
                'submit' => 'Actualizar contraseña',
                'loading' => 'Actualizando...',
                'ajax' => FALSE,
            ],
            'country' => [
                'eyebrow' => 'Bienvenido',
                'heading' => 'Selecciona tu país',
                'description' => 'Elige el país desde donde operarás para continuar.',
                'form_id' => null,
                'endpoint' => base_url('auth/login'),
                'success_title' => '',
                'submit' => '',
                'loading' => '',
                'ajax' => FALSE,
            ],
        ];

        $view = $config[$auth_variant] ?? $config['login'];
        $identityType = (($type ?? 'email') !== 'email') ? 'text' : 'email';
        $identityLabel = (($type ?? 'email') !== 'email') ? 'Usuario o correo' : 'Correo electrónico';
        $csrfHash = isset($_ci->security) ? $_ci->security->get_csrf_hash() : '';
        $csrfName = isset($_ci->config) ? $_ci->config->item('csrf_token_name') : 'csrf_sgms_token';

        // Banderas SVG para el selector de país
        $flagSvg = function ($code) {
            switch ($code) {
                case 'CR':
                    return '<img src="' . base_url('assets/img/flags/cr.svg') . '" alt="" aria-hidden="true" loading="lazy">';
                case 'SV':
                    return '<img src="' . base_url('assets/img/flags/sv.svg') . '" alt="" aria-hidden="true" loading="lazy">';
                default:
                    return '';
            }
        };
        $country = isset($country) ? $country : null;
        $countries = isset($countries) ? $countries : array();
    ?>
    <style>
        :root {
            --brand: #DC2626;
            --brand-hover: #B91C1C;
            --brand-grad: linear-gradient(135deg, #EF4444 0%, #DC2626 55%, #991B1B 100%);
            --brand-soft: #FEF2F2;
            --brand-soft-2: #FEE2E2;
            --ink: #111114;
            --ink-2: #1A1A1F;
            --muted: #52525B;
            --muted-2: #71717A;
            --line: #E4E4E7;
            --line-strong: #D4D4D8;
            --surface: #FFFFFF;
            --bg: #F4F4F5;
            --black: #0B0B0F;
            --focus: rgba(220, 38, 38, 0.22);
            --shadow-card: 0 24px 60px -20px rgba(11, 11, 15, 0.18), 0 2px 8px rgba(11, 11, 15, 0.06);
            --shadow-btn: 0 10px 22px -8px rgba(220, 38, 38, 0.55);
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }

        body {
            margin: 0;
            min-height: 100svh;
            overflow-x: hidden;
            font-family: 'Fira Sans', Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            -webkit-font-smoothing: antialiased;
        }

        a { color: var(--brand); font-weight: 600; text-decoration: none; transition: color 180ms ease; }
        a:hover { color: var(--brand-hover); }
        a:focus-visible, button:focus-visible, input:focus-visible {
            outline: 3px solid var(--focus);
            outline-offset: 2px;
            border-radius: 4px;
        }

        /* Animaciones suaves de entrada */
        @keyframes sg-fade-up { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes sg-fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes sg-slide-in { from { opacity: 0; transform: translateX(-18px); } to { opacity: 1; transform: translateX(0); } }

        .auth-shell {
            width: 100%;
            min-height: 100svh;
            display: grid;
            grid-template-columns: minmax(340px, 44%) 1fr;
        }

        /* ================= Panel de marca (negro + rojo) ================= */
        .auth-brand {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 52px 56px;
            background:
                radial-gradient(1000px 500px at 85% -10%, rgba(220, 38, 38, 0.28), transparent 60%),
                radial-gradient(700px 420px at -10% 110%, rgba(239, 68, 68, 0.16), transparent 60%),
                linear-gradient(180deg, #0B0B0F 0%, #121218 100%);
            color: #FAFAFA;
            overflow: hidden;
            animation: sg-slide-in 520ms cubic-bezier(.22,.8,.35,1) both;
        }

        .auth-brand::after {
            content: "";
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        .auth-brand .accent-line {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--brand-grad);
        }

        .brand-top, .brand-mid, .brand-foot { position: relative; z-index: 1; }

        .brand-top { display: flex; align-items: center; gap: 16px; }

        .brand-logo-wrap {
            position: relative;
            width: 64px; height: 64px;
            border-radius: 16px;
            padding: 3px;
            background: var(--brand-grad);
            box-shadow: 0 12px 30px -8px rgba(220, 38, 38, 0.6);
        }

        .brand-logo-wrap img {
            width: 100%; height: 100%;
            border-radius: 13px;
            object-fit: cover;
            display: block;
        }

        .brand-name { font-family: 'Fira Code', monospace; font-size: 1.3rem; font-weight: 700; letter-spacing: -0.02em; margin: 0; }
        .brand-name em { font-style: normal; color: #F87171; }
        .brand-tagline { margin: 4px 0 0; color: #9CA3AF; font-size: 0.85rem; }

        .brand-mid { margin: 40px 0 32px; }

        .brand-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 13px;
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 999px;
            background: rgba(220, 38, 38, 0.08);
            color: #FCA5A5;
            font-family: 'Fira Code', monospace;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .brand-title {
            margin: 20px 0 0;
            font-family: 'Fira Code', monospace;
            font-size: clamp(1.7rem, 2.6vw, 2.4rem);
            font-weight: 700;
            line-height: 1.18;
            letter-spacing: -0.03em;
        }

        .brand-title .grad {
            background: linear-gradient(90deg, #F87171, #EF4444);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .brand-features { display: grid; gap: 12px; margin-top: 30px; padding: 0; list-style: none; }

        .brand-features li {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #D1D5DB;
            font-size: 0.92rem;
        }

        .brand-features li .bi {
            width: 30px; height: 30px;
            display: grid; place-items: center;
            border-radius: 9px;
            background: rgba(220, 38, 38, 0.14);
            color: #F87171;
            font-size: 0.95rem;
            flex-shrink: 0;
        }

        .brand-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: #6B7280;
            font-size: 0.78rem;
        }

        .brand-foot a { color: #9CA3AF; }
        .brand-foot a:hover { color: #F87171; }

        /* ================= Panel del formulario ================= */
        .auth-main {
            display: grid;
            place-items: center;
            padding: 48px 24px calc(48px + env(safe-area-inset-bottom));
        }

        .auth-card {
            width: min(100%, 460px);
            animation: sg-fade-up 560ms cubic-bezier(.22,.8,.35,1) 80ms both;
        }

        .auth-mobile-brand {
            display: none;
            align-items: center;
            gap: 12px;
            margin-bottom: 26px;
            animation: sg-fade-in 400ms ease both;
        }

        .auth-mobile-brand img {
            width: 46px; height: 46px;
            border-radius: 13px;
            object-fit: cover;
            box-shadow: 0 8px 20px -6px rgba(220, 38, 38, 0.45);
        }

        .auth-mobile-brand strong { font-family: 'Fira Code', monospace; font-size: 1.05rem; }
        .auth-mobile-brand strong em { font-style: normal; color: var(--brand); }

        .panel-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-height: 26px;
            margin-bottom: 14px;
            padding: 4px 12px;
            border: 1px solid var(--brand-soft-2);
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-family: 'Fira Code', monospace;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .panel-title {
            margin: 0;
            color: var(--ink);
            font-family: 'Fira Code', monospace;
            font-size: clamp(1.65rem, 3vw, 2.05rem);
            font-weight: 700;
            line-height: 1.12;
            letter-spacing: -0.02em;
        }

        .panel-description { margin: 12px 0 0; color: var(--muted); font-size: 0.96rem; line-height: 1.6; }

        .auth-alert {
            margin: 20px 0 0;
            border: 1px solid #FECACA;
            border-left: 4px solid var(--brand);
            border-radius: 12px;
            background: var(--brand-soft);
            color: #991B1B;
            padding: 13px 14px;
            font-size: 0.88rem;
            line-height: 1.55;
        }

        .auth-form { margin-top: 26px; }

        .form-group { margin-bottom: 18px; }
        .form-group:last-child { margin-bottom: 0; }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--ink);
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .input-wrap { position: relative; }

        .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 1rem;
            pointer-events: none;
            transition: color 180ms ease;
        }

        .input-wrap .form-control {
            display: block;
            width: 100%;
            min-height: 50px;
            padding: 13px 46px 13px 42px;
            border: 1px solid var(--line-strong);
            border-radius: 12px;
            background: #FFFFFF;
            color: var(--ink);
            font: inherit;
            font-size: 0.95rem;
            line-height: 1.4;
            transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .input-wrap.no-icon .form-control { padding-left: 15px; }

        .input-wrap .form-control::placeholder { color: #A1A1AA; }
        .input-wrap .form-control:hover { border-color: #A1A1AA; }

        .input-wrap .form-control:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px var(--focus);
        }

        .input-wrap:focus-within .input-icon { color: var(--brand); }

        .pass-toggle {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            width: 38px; height: 38px;
            display: grid; place-items: center;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #9CA3AF;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 180ms ease, background-color 180ms ease;
        }

        .pass-toggle:hover { color: var(--brand); background: var(--brand-soft); }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 2px 0 22px;
            color: var(--muted);
            font-size: 0.88rem;
        }

        .form-check { display: inline-flex; align-items: center; gap: 10px; margin: 0; min-width: 0; cursor: pointer; }

        .form-check-input {
            width: 18px; height: 18px;
            margin: 0;
            border: 1.5px solid var(--line-strong);
            border-radius: 5px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .form-check-label { cursor: pointer; user-select: none; }

        .auth-button {
            position: relative;
            display: inline-flex;
            width: 100%;
            min-height: 52px;
            align-items: center;
            justify-content: center;
            gap: 10px;
            border: 0;
            border-radius: 12px;
            background: var(--brand-grad);
            color: #FFFFFF;
            cursor: pointer;
            font: inherit;
            font-size: 0.96rem;
            font-weight: 600;
            line-height: 1;
            box-shadow: var(--shadow-btn);
            transition: transform 160ms ease, box-shadow 180ms ease, filter 180ms ease;
        }

        .auth-button:hover { filter: brightness(1.05); box-shadow: 0 14px 26px -8px rgba(220, 38, 38, 0.6); }
        .auth-button:active { transform: translateY(1px); }
        .auth-button:disabled { cursor: wait; opacity: 0.85; box-shadow: none; }

        .auth-button .btn-spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.35);
            border-top-color: #FFFFFF;
            border-radius: 50%;
            animation: sg-spin 0.7s linear infinite;
            display: none;
        }

        .auth-button.loading .btn-spinner { display: inline-block; }

        @keyframes sg-spin { to { transform: rotate(360deg); } }

        .auth-links {
            display: grid;
            gap: 10px;
            margin-top: 22px;
            color: var(--muted);
            font-size: 0.9rem;
            text-align: center;
        }

        .auth-links span { color: var(--muted); }

        /* Bloque de ayuda / contacto */
        .auth-help {
            margin-top: 26px;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 14px 16px;
            border: 1px dashed var(--line-strong);
            border-radius: 14px;
            background: #FAFAFA;
            color: var(--muted-2);
            font-size: 0.84rem;
            animation: sg-fade-in 400ms ease 300ms both;
        }

        .auth-help-icon {
            width: 40px; height: 40px;
            flex-shrink: 0;
            display: grid; place-items: center;
            border-radius: 11px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 1.15rem;
        }

        .auth-help strong { color: var(--ink); font-size: 0.86rem; }
        .auth-help a { color: var(--brand); font-weight: 600; }

        /* Selector de país */
        .auth-country-note {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border-radius: 12px;
            background: var(--brand-soft);
            border: 1px solid var(--brand-soft-2);
            color: #991B1B;
            font-size: 0.84rem;
            margin-bottom: 18px;
        }

        .auth-country-note .bi { color: var(--brand); font-size: 1.05rem; }

        .country-list { display: grid; gap: 12px; }

        .country-card {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px;
            border: 1.5px solid var(--line-strong);
            border-radius: 14px;
            background: #FFFFFF;
            color: var(--ink);
            transition: border-color 180ms ease, box-shadow 180ms ease, transform 160ms ease;
            text-decoration: none;
        }

        .country-card:hover {
            border-color: var(--brand);
            box-shadow: 0 10px 24px -12px rgba(220, 38, 38, 0.4);
            transform: translateY(-1px);
            color: var(--ink);
        }

        .country-flag {
            width: 54px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(11, 11, 15, 0.2);
            display: inline-block;
        }

        .country-flag img { width: 100%; height: 100%; display: block; object-fit: cover; }

        .country-info { flex: 1; min-width: 0; }
        .country-info strong { display: block; font-size: 1rem; color: var(--ink); }
        .country-info small { color: var(--muted); font-size: 0.82rem; }

        .country-arrow { color: #A1A1AA; font-size: 1.1rem; transition: transform 160ms ease, color 160ms ease; }
        .country-card:hover .country-arrow { color: var(--brand); transform: translateX(3px); }

        .auth-country-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #FAFAFA;
            margin-bottom: 20px;
        }

        .auth-country-chip .country-flag { width: 40px; height: 27px; border-radius: 6px; }
        .auth-country-chip .country-info { flex: 1; }
        .auth-country-chip .country-info strong { font-size: 0.92rem; }
        .auth-country-chip .country-info small { font-size: 0.78rem; }

        .country-change { font-size: 0.84rem; font-weight: 700; }

        /* ================= Responsive ================= */
        @media (max-width: 960px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-brand { display: none; }
            .auth-main { padding: 40px 20px calc(40px + env(safe-area-inset-bottom)); }
            .auth-mobile-brand { display: flex; }
        }

        @media (max-width: 480px) {
            .auth-main { padding: 26px 16px calc(26px + env(safe-area-inset-bottom)); }
            .form-options { align-items: flex-start; flex-direction: column; gap: 12px; }
            .panel-title { font-size: 1.55rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body class="auth-page auth-<?php echo html_escape($auth_variant); ?>">
    <main class="auth-shell">
        <aside class="auth-brand" aria-hidden="true">
            <span class="accent-line"></span>
            <div class="brand-top">
                <div class="brand-logo-wrap">
                    <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="">
                </div>
                <div>
                    <p class="brand-name">SG<em>Mensajeria</em></p>
                    <p class="brand-tagline"><?php echo html_escape($companyName); ?> · Gestión de entregas</p>
                </div>
            </div>

            <div class="brand-mid">
                <span class="brand-kicker"><i class="bi bi-lightning-charge-fill"></i> Control total</span>
                <h2 class="brand-title">Pedidos, rutas y entregas <span class="grad">en tiempo real</span></h2>
                <ul class="brand-features">
                    <li><i class="bi bi-broadcast"></i> Seguimiento del estado de cada entrega</li>
                    <li><i class="bi bi-map"></i> Rutas diarias con mapa y orden óptimo</li>
                    <li><i class="bi bi-whatsapp"></i> Contacto directo con tus clientes</li>
                </ul>
            </div>

            <div class="brand-foot">
                <span>© <?php echo date('Y'); ?> <?php echo html_escape($companyName); ?> · Costa Rica</span>
                <?php if ($companyPhone !== ''): ?>
                    <a href="tel:<?php echo html_escape($companyPhone); ?>"><i class="bi bi-telephone me-1"></i><?php echo html_escape($companyPhone); ?></a>
                <?php endif; ?>
            </div>
        </aside>

        <section class="auth-main">
            <div class="auth-card" aria-labelledby="auth-title">
                <div class="auth-mobile-brand">
                    <img src="<?php echo base_url('assets/img/logo.png'); ?>" alt="Logo <?php echo html_escape($companyName); ?>">
                    <strong>SG<em>Mensajeria</em></strong>
                </div>

                <header>
                    <div class="panel-eyebrow"><i class="bi bi-shield-lock"></i><?php echo html_escape($view['eyebrow']); ?></div>
                    <h1 class="panel-title" id="auth-title"><?php echo html_escape($view['heading']); ?></h1>
                    <p class="panel-description"><?php echo html_escape($view['description']); ?></p>
                </header>

                <?php if ($messageText !== ''): ?>
                    <div class="auth-alert" role="alert"><?php echo html_escape($messageText); ?></div>
                <?php endif; ?>

                <?php if ($auth_variant === 'country'): ?>
                    <div class="auth-form">
                        <div class="auth-country-note">
                            <i class="bi bi-globe2"></i>
                            <span>Tus datos se almacenan de forma aislada por país.</span>
                        </div>
                        <div class="country-list">
                            <?php foreach ($countries as $c): ?>
                                <a class="country-card" href="<?php echo base_url('auth/login/' . html_escape($c->code)); ?>">
                                    <span class="country-flag"><?php echo $flagSvg($c->code); ?></span>
                                    <span class="country-info">
                                        <strong><?php echo html_escape($c->name); ?></strong>
                                        <small><?php echo html_escape($c->currency); ?> · +<?php echo html_escape($c->phone_code); ?></small>
                                    </span>
                                    <i class="bi bi-chevron-right country-arrow"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($companyPhone !== ''): ?>
                            <div class="auth-help">
                                <div class="auth-help-icon"><i class="bi bi-headset"></i></div>
                                <div>
                                    <strong>¿Necesitas ayuda?</strong>
                                    <div><a href="tel:<?php echo html_escape($companyPhone); ?>"><i class="bi bi-telephone me-1"></i><?php echo html_escape($companyPhone); ?></a></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php elseif ($auth_variant === 'login'): ?>
                    <?php if ($country): ?>
                        <div class="auth-country-chip">
                            <span class="country-flag"><?php echo $flagSvg($country->code); ?></span>
                            <span class="country-info">
                                <strong><?php echo html_escape($country->name); ?></strong>
                                <small><?php echo html_escape($country->currency); ?> · +<?php echo html_escape($country->phone_code); ?></small>
                            </span>
                            <a class="country-change" href="<?php echo base_url('auth/login'); ?>">Cambiar</a>
                        </div>
                    <?php endif; ?>
                    <form class="auth-form" id="loginForm" action="<?php echo base_url('auth/login/' . ($country ? $country->code : '')); ?>" method="post" data-ajax-form="true" novalidate>
                        <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                        <?php if ($country): ?><input type="hidden" name="country_code" value="<?php echo html_escape($country->code); ?>"><?php endif; ?>
                        <div class="form-group">
                            <label class="form-label" for="identity"><?php echo html_escape($identityLabel); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-person input-icon"></i>
                                <input type="<?php echo html_escape($identityType); ?>" class="form-control" id="identity" name="identity" placeholder="usuario o tu@correo.com" value="<?php echo html_escape(set_value('identity')); ?>" autocomplete="username" required aria-required="true">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Contraseña</label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required aria-required="true">
                                <button type="button" class="pass-toggle" data-toggle-pass="password" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-options">
                            <label class="form-check" for="remember">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                                <span class="form-check-label">Recordarme</span>
                            </label>
                            <a href="<?php echo base_url('auth/forgot_password/' . ($country ? $country->code : '')); ?>">Olvidé mi contraseña</a>
                        </div>
                        <button type="submit" class="auth-button" data-submit-button data-loading-label="<?php echo html_escape($view['loading']); ?>">
                            <span class="btn-spinner" aria-hidden="true"></span>
                            <span class="btn-text"><?php echo html_escape($view['submit']); ?></span>
                        </button>
                    </form>

                    <?php if ($companyPhone !== '' || $companyEmail !== ''): ?>
                        <div class="auth-help">
                            <div class="auth-help-icon"><i class="bi bi-headset"></i></div>
                            <div>
                                <strong>¿Necesitas ayuda?</strong>
                                <div>
                                    <?php if ($companyPhone !== ''): ?><a href="tel:<?php echo html_escape($companyPhone); ?>"><i class="bi bi-telephone me-1"></i><?php echo html_escape($companyPhone); ?></a><?php endif; ?>
                                    <?php if ($companyPhone !== '' && $companyEmail !== ''): ?><span class="mx-1">·</span><?php endif; ?>
                                    <?php if ($companyEmail !== ''): ?><a href="mailto:<?php echo html_escape($companyEmail); ?>"><?php echo html_escape($companyEmail); ?></a><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($auth_variant === 'register'): ?>
                    <form class="auth-form" id="registerForm" action="<?php echo html_escape($view['endpoint']); ?>" method="post" data-ajax-form="true" novalidate>
                        <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label" for="first_name">Nombre</label>
                                <div class="input-wrap">
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Nombre" value="<?php echo html_escape(set_value('first_name')); ?>" autocomplete="given-name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="last_name">Apellido</label>
                                <div class="input-wrap">
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Apellido" value="<?php echo html_escape(set_value('last_name')); ?>" autocomplete="family-name" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="email">Correo electrónico</label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="email" class="form-control" id="email" name="email" placeholder="tu@correo.com" value="<?php echo html_escape(set_value('email')); ?>" autocomplete="email" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Contraseña</label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="password" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirm">Confirmar contraseña</label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Repite la contraseña" autocomplete="new-password" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="password_confirm" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <button type="submit" class="auth-button" data-submit-button data-loading-label="<?php echo html_escape($view['loading']); ?>">
                            <span class="btn-spinner" aria-hidden="true"></span>
                            <span class="btn-text"><?php echo html_escape($view['submit']); ?></span>
                        </button>
                    </form>
                    <div class="auth-links">
                        <span>¿Ya tienes cuenta? <a href="<?php echo base_url('auth/login'); ?>">Iniciar sesión</a></span>
                    </div>
                <?php elseif ($auth_variant === 'forgot_password'): ?>
                    <?php if ($country): ?>
                        <div class="auth-country-chip">
                            <span class="country-flag"><?php echo $flagSvg($country->code); ?></span>
                            <span class="country-info">
                                <strong><?php echo html_escape($country->name); ?></strong>
                                <small><?php echo html_escape($country->currency); ?> · +<?php echo html_escape($country->phone_code); ?></small>
                            </span>
                            <a class="country-change" href="<?php echo base_url('auth/login'); ?>">Cambiar</a>
                        </div>
                    <?php endif; ?>
                    <form class="auth-form" id="forgotForm" action="<?php echo base_url('auth/forgot_password/' . ($country ? $country->code : '')); ?>" method="post" data-ajax-form="true" novalidate>
                        <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                        <?php if ($country): ?><input type="hidden" name="country_code" value="<?php echo html_escape($country->code); ?>"><?php endif; ?>
                        <div class="form-group">
                            <label class="form-label" for="identity"><?php echo html_escape($identityLabel); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-envelope input-icon"></i>
                                <input type="<?php echo html_escape($identityType); ?>" class="form-control" id="identity" name="identity" placeholder="tu@correo.com" value="<?php echo html_escape(set_value('identity')); ?>" autocomplete="email" required>
                            </div>
                        </div>
                        <button type="submit" class="auth-button" data-submit-button data-loading-label="<?php echo html_escape($view['loading']); ?>">
                            <span class="btn-spinner" aria-hidden="true"></span>
                            <span class="btn-text"><?php echo html_escape($view['submit']); ?></span>
                        </button>
                    </form>
                    <div class="auth-links">
                        <a href="<?php echo base_url('auth/login/' . ($country ? $country->code : '')); ?>"><i class="bi bi-arrow-left me-1"></i>Volver a iniciar sesión</a>
                    </div>
                    <?php if ($companyPhone !== ''): ?>
                        <div class="auth-help">
                            <div class="auth-help-icon"><i class="bi bi-headset"></i></div>
                            <div>
                                <strong>¿Problemas para recuperar tu cuenta?</strong>
                                <div><a href="tel:<?php echo html_escape($companyPhone); ?>"><i class="bi bi-telephone me-1"></i><?php echo html_escape($companyPhone); ?></a></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php elseif ($auth_variant === 'reset_password'): ?>
                    <form class="auth-form" id="resetPasswordForm" action="<?php echo html_escape($view['endpoint']); ?>" method="post" data-ajax-form="false" novalidate>
                        <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                        <div class="form-group">
                            <label class="form-label" for="new_password"><?php echo sprintf(lang('reset_password_new_password_label'), $min_password_length); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="new_password" name="new" placeholder="Nueva contraseña" autocomplete="new-password" pattern="<?php echo html_escape('^.{' . $min_password_length . '}.*$'); ?>" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="new_password" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new_password_confirm"><?php echo lang('reset_password_new_password_confirm_label'); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="new_password_confirm" name="new_confirm" placeholder="Repite la contraseña" autocomplete="new-password" pattern="<?php echo html_escape('^.{' . $min_password_length . '}.*$'); ?>" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="new_password_confirm" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="<?php echo html_escape($user_id['value'] ?? ''); ?>">
                        <?php if (isset($csrf) && is_array($csrf)): ?>
                            <?php foreach ($csrf as $csrfName2 => $csrfValue2): ?>
                                <input type="hidden" name="<?php echo html_escape($csrfName2); ?>" value="<?php echo html_escape($csrfValue2); ?>">
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <button type="submit" class="auth-button" data-submit-button data-loading-label="<?php echo html_escape($view['loading']); ?>">
                            <span class="btn-spinner" aria-hidden="true"></span>
                            <span class="btn-text"><?php echo html_escape($view['submit']); ?></span>
                        </button>
                    </form>
                    <div class="auth-links">
                        <a href="<?php echo base_url('auth/login'); ?>"><i class="bi bi-arrow-left me-1"></i>Volver a iniciar sesión</a>
                    </div>
                <?php elseif ($auth_variant === 'change_password'): ?>
                    <form class="auth-form" id="changePasswordForm" action="<?php echo base_url('auth/change_password'); ?>" method="post" data-ajax-form="false" novalidate>
                        <input type="hidden" name="<?php echo html_escape($csrfName); ?>" value="<?php echo html_escape($csrfHash); ?>">
                        <div class="form-group">
                            <label class="form-label" for="old">Contraseña actual</label>
                            <div class="input-wrap">
                                <i class="bi bi-shield-lock input-icon"></i>
                                <input type="password" class="form-control" id="old" name="old" placeholder="Tu contraseña actual" autocomplete="current-password" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="old" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new"><?php echo sprintf(lang('change_password_new_password_label'), $min_password_length); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="new" name="new" placeholder="Nueva contraseña" autocomplete="new-password" pattern="<?php echo html_escape('^.{' . $min_password_length . '}.*$'); ?>" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="new" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new_confirm"><?php echo lang('change_password_new_password_confirm_label'); ?></label>
                            <div class="input-wrap">
                                <i class="bi bi-lock input-icon"></i>
                                <input type="password" class="form-control" id="new_confirm" name="new_confirm" placeholder="Repite la contraseña" autocomplete="new-password" pattern="<?php echo html_escape('^.{' . $min_password_length . '}.*$'); ?>" required>
                                <button type="button" class="pass-toggle" data-toggle-pass="new_confirm" aria-label="Mostrar contraseña" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="<?php echo html_escape($user_id['value'] ?? ''); ?>">
                        <button type="submit" class="auth-button" data-submit-button data-loading-label="<?php echo html_escape($view['loading']); ?>">
                            <span class="btn-spinner" aria-hidden="true"></span>
                            <span class="btn-text"><?php echo html_escape($view['submit']); ?></span>
                        </button>
                    </form>
                    <div class="auth-links">
                        <a href="<?php echo default_landing_url(); ?>"><i class="bi bi-arrow-left me-1"></i>Volver al panel</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="<?php echo base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/vendor/sweetalert2/sweetalert2.min.js'); ?>"></script>
    <script>
        function stripHtml(input) {
            return $('<div>').html(input || '').text().trim();
        }

        $(document).ready(function () {
            // Toggle mostrar/ocultar contraseña
            $('.pass-toggle').on('click', function () {
                var $btn = $(this);
                var $input = $('#' + $btn.data('toggle-pass'));
                var show = $input.attr('type') === 'password';
                $input.attr('type', show ? 'text' : 'password');
                $btn.find('.bi')
                    .toggleClass('bi-eye', !show)
                    .toggleClass('bi-eye-slash', show);
                $btn.attr('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
                $input.trigger('focus');
            });

            var form = $('#<?php echo $view['form_id']; ?>');

            if (form.attr('data-ajax-form') !== 'true') {
                return;
            }

            form.on('submit', function (e) {
                e.preventDefault();

                var currentForm = $(this);
                var submitButton = currentForm.find('[data-submit-button]').first();
                var textEl = submitButton.find('.btn-text');
                var originalLabel = textEl.length ? textEl.text() : submitButton.text();
                var loadingLabel = submitButton.data('loading-label') || 'Procesando...';

                submitButton.prop('disabled', true).addClass('loading');
                if (textEl.length) { textEl.text(loadingLabel); } else { submitButton.text(loadingLabel); }

                $.ajax({
                    url: currentForm.attr('action'),
                    type: 'POST',
                    data: currentForm.serialize(),
                    dataType: 'json',
                    complete: function () {
                        submitButton.prop('disabled', false).removeClass('loading');
                        if (textEl.length) { textEl.text(originalLabel); } else { submitButton.text(originalLabel); }
                    },
                    success: function (response) {
                        var responseMessage = stripHtml(response.message) || 'Proceso completado correctamente.';

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '<?php echo html_escape($view['success_title']); ?>',
                                text: responseMessage,
                                confirmButtonColor: '#DC2626',
                                background: '#FFFFFF'
                            }).then(function () {
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'No fue posible continuar',
                                text: responseMessage || 'Revisa la información e inténtalo nuevamente.',
                                confirmButtonColor: '#DC2626'
                            });
                        }
                    },
                    error: function (jqXHR) {
                        var errMessage = 'Ocurrió un problema al procesar la solicitud. Inténtalo otra vez.';
                        if (jqXHR && jqXHR.responseText) {
                            try {
                                var parsed = JSON.parse(jqXHR.responseText);
                                if (parsed && parsed.message) {
                                    errMessage = stripHtml(parsed.message);
                                }
                            } catch (e) {
                                if (jqXHR.status) {
                                    errMessage = 'Error ' + jqXHR.status + ': ' + errMessage;
                                }
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error inesperado',
                            text: errMessage,
                            confirmButtonColor: '#DC2626'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
