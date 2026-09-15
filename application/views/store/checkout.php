<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$u = $store_user;
?>

<section class="section">
  <div class="container-x">
    <span class="section-eyebrow">Checkout</span>
    <h1 class="section-title">Finalizar compra</h1>
    <p class="section-subtitle">Confirma tus datos de entrega y método de pago.</p>

    <form class="checkout-layout mt-4" method="post" action="<?php echo base_url('checkout/confirmar'); ?>">
      <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">

      <div class="form-card">
        <h2 style="font-size:1.1rem; margin-top:0;">Datos de entrega</h2>
        <div class="form-grid-2">
          <div class="form-group">
            <label for="customer_name">Nombre completo</label>
            <input class="form-control" type="text" id="customer_name" name="customer_name" value="<?php echo html_escape(set_value('customer_name', $u->first_name)); ?>" required>
          </div>
          <div class="form-group">
            <label for="customer_phone">Número celular</label>
            <input class="form-control" type="tel" id="customer_phone" name="customer_phone" value="<?php echo html_escape(set_value('customer_phone', $u->phone)); ?>" required>
          </div>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label for="customer_phone2">Teléfono secundario <span class="muted">(opcional)</span></label>
            <input class="form-control" type="tel" id="customer_phone2" name="customer_phone2" value="<?php echo html_escape(set_value('customer_phone2', $u->phone2)); ?>">
          </div>
          <div class="form-group">
            <label for="delivery_zone">Zona de entrega</label>
            <input class="form-control" type="text" id="delivery_zone" name="delivery_zone" value="<?php echo html_escape(set_value('delivery_zone', $u->delivery_zone)); ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label for="delivery_address">Dirección aproximada</label>
          <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo html_escape(set_value('delivery_address', $u->delivery_address)); ?></textarea>
        </div>
        <div class="form-group">
          <label for="notes">Notas <span class="muted">(opcional)</span></label>
          <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo html_escape(set_value('notes')); ?></textarea>
        </div>

        <h2 style="font-size:1.1rem;">Método de pago</h2>
        <div class="pay-options">
          <?php foreach ($methods as $index => $method): ?>
            <label class="pay-option">
              <input type="radio" name="payment_method_id" value="<?php echo (int) $method->id; ?>"<?php echo $index === 0 ? ' checked' : ''; ?> required>
              <span><?php echo html_escape($method->name); ?></span>
            </label>
          <?php endforeach; ?>
          <?php if (empty($methods)): ?>
            <p class="muted">No hay métodos de pago configurados para este país.</p>
          <?php endif; ?>
        </div>
      </div>

      <aside class="summary">
        <h3>Tu pedido</h3>
        <div class="order-lines">
          <?php foreach ($contents as $line): ?>
            <div class="order-line">
              <span><?php echo (int) $line['quantity']; ?>× <?php echo html_escape($line['name']); ?><?php echo ! empty($line['flavor']) ? ' <small class="muted">(' . html_escape($line['flavor']) . ')</small>' : ''; ?></span>
              <strong><?php echo store_price($line['line_total']); ?></strong>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="summary-line mt-2"><span>Subtotal</span><strong><?php echo store_price($subtotal); ?></strong></div>
        <div class="summary-line"><span>Envío</span><strong><?php echo store_price($shipping); ?></strong></div>
        <div class="summary-total"><span>Total</span><span><?php echo store_price($total); ?></span></div>
        <button type="submit" class="btn-brand btn-block mt-3"<?php echo empty($methods) ? ' disabled' : ''; ?>>Confirmar pedido</button>
        <?php if ( ! empty(store_whatsapp_number())): ?>
          <button type="button" class="btn-wa btn-block mt-2" data-wa-checkout><i class="bi bi-whatsapp" aria-hidden="true"></i> Pedir por WhatsApp</button>
          <p class="muted mt-2 mb-0" style="font-size:.8rem;">Envia los productos y tus datos escritos por WhatsApp.</p>
        <?php endif; ?>
        <p class="muted mt-2 mb-0" style="font-size:.8rem;">El pedido quedara con pago <strong>pendiente</strong> hasta ser verificado.</p>
      </aside>
    </form>
  </div>
</section>

<?php if ( ! empty(store_whatsapp_number())): ?>
  <?php
  $wa_lines = array();
  foreach ($contents as $line)
  {
      $wa_lines[] = array(
          'qty'    => (int) $line['quantity'],
          'name'   => $line['name'],
          'lab'    => $line['laboratory'],
          'flavor' => isset($line['flavor']) ? $line['flavor'] : '',
          'total'  => store_price($line['line_total']),
      );
  }
  $wa_data = array(
      'number'   => store_whatsapp_number(),
      'country'  => $current_country->name,
      'lines'    => $wa_lines,
      'subtotal' => store_price($subtotal),
      'shipping' => store_price($shipping),
      'total'    => store_price($total),
  );
  ?>
  <script type="application/json" id="waCheckoutData"><?php echo json_encode($wa_data); ?></script>
<?php endif; ?>
