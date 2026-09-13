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
    <nav aria-label="Ruta" class="muted" style="font-size:.85rem;">
      <a href="<?php echo base_url('cuenta/pedidos'); ?>">Mis pedidos</a> / <span><?php echo html_escape($order->order_number); ?></span>
    </nav>
    <h1 class="section-title mt-2">Pedido <?php echo html_escape($order->order_number); ?></h1>
    <div style="display:flex; gap:.4rem; flex-wrap:wrap; margin:.5rem 0 1.5rem;">
      <span class="status status-<?php echo html_escape($order->status); ?>"><?php echo html_escape(isset($status_labels[$order->status]) ? $status_labels[$order->status] : $order->status); ?></span>
      <span class="status status-<?php echo html_escape($order->payment_status); ?>">Pago: <?php echo html_escape(isset($status_labels[$order->payment_status]) ? $status_labels[$order->payment_status] : $order->payment_status); ?></span>
    </div>

    <div class="checkout-layout">
      <div class="form-card">
        <h2 style="font-size:1.1rem; margin-top:0;">Productos</h2>
        <div class="order-lines">
          <?php foreach ($order->items as $item): ?>
            <div class="order-line">
              <span><?php echo (int) $item->quantity; ?>× <?php echo html_escape($item->item_name); ?><br><small class="muted"><?php echo store_order_price($order, $item->unit_price); ?> c/u</small></span>
              <strong><?php echo store_order_price($order, $item->line_total); ?></strong>
            </div>
          <?php endforeach; ?>
        </div>

        <h2 style="font-size:1.1rem;" class="mt-3">Entrega</h2>
        <p class="mb-0">
          <strong><?php echo html_escape($order->customer_name); ?></strong><br>
          <?php echo html_escape($order->customer_phone); ?><?php echo ! empty($order->customer_phone2) ? ' / ' . html_escape($order->customer_phone2) : ''; ?><br>
          <?php echo html_escape($order->delivery_zone); ?> — <?php echo nl2br(html_escape($order->delivery_address)); ?>
        </p>
      </div>

      <aside class="summary">
        <h3>Resumen</h3>
        <div class="summary-line"><span>Fecha</span><strong><?php echo date('d/m/Y H:i', strtotime($order->created_at)); ?></strong></div>
        <div class="summary-line"><span>Pais</span><strong><?php echo html_escape($order->country_code); ?></strong></div>
        <div class="summary-line"><span>Metodo de pago</span><strong><?php echo html_escape($order->payment_method_name ?: 'Por definir'); ?></strong></div>
        <div class="summary-line"><span>Subtotal</span><strong><?php echo store_order_price($order, $order->subtotal); ?></strong></div>
        <div class="summary-line"><span>Envio</span><strong><?php echo store_order_price($order, $order->shipping); ?></strong></div>
        <div class="summary-total"><span>Total</span><span><?php echo store_order_price($order, $order->total); ?></span></div>
        <?php $wa = store_whatsapp_url('Hola, acabo de realizar el pedido #' . $order->order_number . '.'); ?>
        <?php if ( ! empty($wa)): ?>
          <a class="btn-wa btn-block mt-3" href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Consultar por WhatsApp</a>
        <?php endif; ?>
        <a class="btn-ghost btn-block mt-2" href="<?php echo base_url('productos'); ?>">Seguir comprando</a>
      </aside>
    </div>
  </div>
</section>
