<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$wa_customer = array();
if ($store_logged_in && ! empty($store_user))
{
	$wa_customer = array(
		'nombre'    => $store_user->first_name,
		'telefono'  => $store_user->phone,
		'zona'      => $store_user->delivery_zone,
		'direccion' => $store_user->delivery_address,
	);
}
$wa_order = empty($contents) ? '' : store_cart_whatsapp_url($contents, $subtotal, $shipping, $total, $current_country, $wa_customer);
?>

<section class="section">
  <div class="container-x">
    <span class="section-eyebrow">Carrito</span>
    <h1 class="section-title">Tu carrito</h1>
    <p class="section-subtitle">Revisa los productos antes de continuar.</p>

    <?php if (empty($contents)): ?>
      <div class="empty-state mt-4">
        <i class="bi bi-cart-x" aria-hidden="true"></i>
        <h3>Tu carrito esta vacio.</h3>
        <p>Agrega productos para comenzar tu pedido.</p>
        <a class="btn-brand mt-3" href="<?php echo base_url('productos'); ?>">Ver productos</a>
      </div>
    <?php else: ?>
      <div class="cart-layout mt-4">
        <div>
          <div class="cart-list">
            <?php foreach ($contents as $line): ?>
              <div class="cart-row" data-cart-row="<?php echo (int) $line['product_id']; ?>">
                <div class="cart-thumb">
                  <img src="<?php echo store_product_image($line['product']); ?>" alt="<?php echo html_escape($line['name']); ?>" loading="lazy">
                </div>
                <div>
                  <h3 class="cart-name"><a href="<?php echo store_product_url($line['product']); ?>"><?php echo html_escape($line['name']); ?></a></h3>
                  <div class="muted" style="font-size:.82rem;">
                    <?php if ( ! empty($line['laboratory'])): ?><?php echo html_escape($line['laboratory']); ?> · <?php endif; ?>
                    <?php if ( ! empty($line['flavor'])): ?>Sabor: <?php echo html_escape($line['flavor']); ?> · <?php endif; ?>
                    <?php echo store_price($line['unit_price']); ?> c/u
                  </div>
                </div>
                <div class="qty-control" data-cart-qty="<?php echo (int) $line['product_id']; ?>">
                  <button type="button" data-delta="-1" aria-label="Disminuir">−</button>
                  <input type="number" value="<?php echo (int) $line['quantity']; ?>" min="1" aria-label="Cantidad de <?php echo html_escape($line['name']); ?>">
                  <button type="button" data-delta="1" aria-label="Aumentar">+</button>
                </div>
                <div style="text-align:right;">
                  <div class="cart-line-price" data-line-total="<?php echo (int) $line['product_id']; ?>"><?php echo store_price($line['line_total']); ?></div>
                  <button type="button" class="cart-remove" data-cart-remove="<?php echo (int) $line['product_id']; ?>" aria-label="Eliminar <?php echo html_escape($line['name']); ?>">
                    <i class="bi bi-trash" aria-hidden="true"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-3" style="display:flex; gap:.6rem;">
            <a class="btn-ghost btn-sm" href="<?php echo base_url('productos'); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Seguir comprando</a>
            <button type="button" class="btn-ghost btn-sm" data-cart-clear>Vaciar carrito</button>
          </div>
        </div>

        <aside class="summary">
          <h3>Resumen</h3>
          <div class="summary-line"><span>Subtotal</span><strong data-summary-subtotal><?php echo store_price($subtotal); ?></strong></div>
          <div class="summary-line"><span>Envío</span><strong data-summary-shipping><?php echo store_price($shipping); ?></strong></div>
          <div class="summary-total"><span>Total</span><span data-summary-total><?php echo store_price($total); ?></span></div>
          <a class="btn-brand btn-block mt-3" href="<?php echo base_url('checkout'); ?>">Finalizar compra <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
          <?php if ( ! empty($wa_order)): ?>
            <a class="btn-wa btn-block mt-2" href="<?php echo html_escape($wa_order); ?>" target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i> Finalizar por WhatsApp</a>
            <p class="muted mt-2 mb-0" style="font-size:.78rem;">Se abrira WhatsApp con el detalle de tu pedido escrito.</p>
          <?php endif; ?>
          <?php if ( ! $store_logged_in): ?>
            <p class="muted mt-2 mb-0" style="font-size:.82rem;">Necesitaras iniciar sesión o crear una cuenta para completar la compra.</p>
          <?php endif; ?>
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>
