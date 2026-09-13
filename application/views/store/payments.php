<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$icons = array(
	'efectivo'               => 'bi-cash-coin',
	'transferencia_sinpe'    => 'bi-phone',
	'transferencia_bancaria' => 'bi-bank',
	'tarjeta'                => 'bi-credit-card',
	'pendiente_credito'      => 'bi-journal-text',
);
?>
<section class="section">
  <div class="container-x">
    <h1 class="section-title">Formas de pago</h1>
    <p class="section-subtitle">Metodos disponibles en <?php echo html_escape($current_country->name); ?>.</p>

    <?php if (empty($methods)): ?>
      <div class="empty-state mt-4">
        <i class="bi bi-credit-card" aria-hidden="true"></i>
        <h3>No hay metodos de pago configurados para este pais.</h3>
        <p>Contactanos para coordinar tu forma de pago.</p>
      </div>
    <?php else: ?>
      <div class="pay-grid mt-4">
        <?php foreach ($methods as $method): ?>
          <div class="pay-card">
            <i class="bi <?php echo isset($icons[$method->code]) ? $icons[$method->code] : 'bi-wallet2'; ?>" aria-hidden="true"></i>
            <h3><?php echo html_escape($method->name); ?></h3>
            <p>Disponible para tus pedidos.</p>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="muted mt-3">Los datos bancarios o de contacto para cada metodo se confirman al momento de coordinar tu pedido. No solicitamos datos de tarjetas en el sitio.</p>
    <?php endif; ?>
  </div>
</section>
