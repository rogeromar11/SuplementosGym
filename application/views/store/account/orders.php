<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$status_labels = array(
	'pendiente'       => 'Pendiente',
	'confirmado'      => 'Confirmado',
	'preparando'      => 'Preparando',
	'enviado'         => 'Enviado',
	'entregado'       => 'Entregado',
	'cancelado'       => 'Cancelado',
	'pagado'          => 'Pagado',
	'en_verificacion' => 'En verificacion',
);
?>
<section class="section">
  <div class="container-x">
    <h1 class="section-title">Mis pedidos</h1>
    <p class="section-subtitle">Historial de tus compras.</p>

    <div class="account-nav mt-3">
      <a class="account-pill" href="<?php echo base_url('cuenta'); ?>">Mi perfil</a>
      <a class="account-pill active" href="<?php echo base_url('cuenta/pedidos'); ?>">Mis pedidos</a>
    </div>

    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <i class="bi bi-receipt" aria-hidden="true"></i>
        <h3>Aun no tienes pedidos.</h3>
        <p>Cuando realices una compra aparecera aqui.</p>
        <a class="btn-brand mt-3" href="<?php echo base_url('productos'); ?>">Ver productos</a>
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($orders as $order): ?>
          <div class="order-card">
            <div class="order-card-head">
              <div>
                <strong><?php echo html_escape($order->order_number); ?></strong>
                <div class="muted" style="font-size:.82rem;">
                  <?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?> · <?php echo html_escape($order->country_code); ?>
                </div>
              </div>
              <div style="display:flex; gap:.4rem; align-items:center; flex-wrap:wrap;">
                <span class="status status-<?php echo html_escape(store_order_status_class($order->status)); ?>"><?php echo html_escape(store_order_status_label($order->status)); ?></span>
                <span class="status status-<?php echo html_escape(store_order_status_class($order->payment_status)); ?>">Pago: <?php echo html_escape(store_order_status_label($order->payment_status)); ?></span>
              </div>
            </div>
            <div class="summary-line mt-2"><span>Metodo de pago</span><strong><?php echo html_escape($order->payment_method_name ?: 'Por definir'); ?></strong></div>
            <div class="summary-line"><span>Total</span><strong><?php echo store_order_price($order, $order->total); ?></strong></div>
            <a class="btn-ghost btn-sm mt-2" href="<?php echo base_url('cuenta/pedido/' . (int) $order->id); ?>">Ver detalle</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
