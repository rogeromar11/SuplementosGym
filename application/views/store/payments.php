<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'efectivo'               => 'bi-cash-coin',
	'transferencia_sinpe'    => 'bi-phone',
	'transferencia_bancaria' => 'bi-bank',
	'tarjeta'                => 'bi-credit-card',
	'pendiente_credito'      => 'bi-journal-text',
);
$descriptions = array(
	'efectivo'               => 'Paga al recibir tu pedido. El cobro se realiza al momento de la entrega.',
	'transferencia_sinpe'    => 'Realiza tu pago por SINPE Movil. Te compartimos el numero al confirmar tu pedido.',
	'transferencia_bancaria' => 'Transfiere a la cuenta indicada. El pedido avanza al validarse el comprobante.',
	'tarjeta'                => 'Pago con tarjeta disponible segun coordinacion. No almacenamos datos de tarjetas.',
	'pendiente_credito'      => 'Pago diferido para clientes autorizados, sujeto a condiciones previas.',
);
?>
<section class="page-band">
  <div class="container-x">
    <h1>Formas de pago</h1>
    <p>Metodos disponibles en <?php echo html_escape($current_country->name); ?>. Elige el que mejor se adapte a ti.</p>
  </div>
</section>

<section class="section">
  <div class="container-x">
    <?php if (empty($methods)): ?>
      <div class="empty-state mt-4">
        <i class="bi bi-credit-card" aria-hidden="true"></i>
        <h3>No hay metodos de pago configurados para este pais.</h3>
        <p>Contactanos para coordinar tu forma de pago.</p>
      </div>
    <?php else: ?>
      <div class="pay-grid">
        <?php foreach ($methods as $method): ?>
          <div class="pay-card reveal">
            <div class="bicon"><i class="bi <?php echo isset($icons[$method->code]) ? $icons[$method->code] : 'bi-wallet2'; ?>" aria-hidden="true"></i></div>
            <h3><?php echo html_escape($method->name); ?></h3>
            <p><?php echo html_escape(isset($descriptions[$method->code]) ? $descriptions[$method->code] : 'Disponible para tus pedidos.'); ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="form-card mt-4">
      <span class="section-eyebrow">Seguridad</span>
      <h2 style="font-size:1.15rem; margin-top:0;">Informacion importante</h2>
      <ul class="value-list">
        <li><i class="bi bi-shield-lock" aria-hidden="true"></i><div><strong>No almacenamos datos de tarjetas.</strong><p>Ningun dato de pago sensible se guarda en el sitio.</p></div></li>
        <li><i class="bi bi-hourglass-split" aria-hidden="true"></i><div><strong>Pago pendiente hasta verificacion.</strong><p>Tu pedido queda como "pendiente" hasta que el pago sea validado.</p></div></li>
        <li><i class="bi bi-chat-dots" aria-hidden="true"></i><div><strong>Coordinamos por WhatsApp.</strong><p>Los datos bancarios o de contacto se confirman al coordinar tu pedido.</p></div></li>
      </ul>
    </div>
  </div>
</section>
