<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$available = store_product_available($product);
$search_blob = implode(' ', array($product->name, $product->laboratory, $product->product_type, $product->flavor, $product->sku));
$wa = store_product_whatsapp_url($product);
?>
<article class="product-card reveal" data-search-item="<?php echo html_escape($search_blob); ?>">
  <div class="product-media">
    <?php if ( ! $available): ?>
      <span class="product-badge badge-out">Agotado</span>
    <?php elseif ((int) $product->featured === 1): ?>
      <span class="product-badge badge-featured">Destacado</span>
    <?php elseif ((int) $product->stock_enabled === 1 && (int) $product->stock_qty <= 5): ?>
      <span class="product-badge badge-low">Pocas unidades</span>
    <?php endif; ?>
    <a href="<?php echo store_product_url($product); ?>" aria-label="<?php echo html_escape($product->name); ?>">
      <img src="<?php echo store_product_image($product); ?>" alt="<?php echo html_escape($product->name); ?>" loading="lazy" width="240" height="180">
    </a>
  </div>
  <div class="product-body">
    <?php if ( ! empty($product->laboratory)): ?>
      <span class="product-lab"><?php echo html_escape($product->laboratory); ?></span>
    <?php endif; ?>
    <h3 class="product-name"><a href="<?php echo store_product_url($product); ?>"><?php echo html_escape($product->name); ?></a></h3>
    <div class="product-meta">
      <span><?php echo html_escape(store_category_label(store_category($product->product_type))); ?></span>
      <?php if ( ! empty($product->weight)): ?><span><?php echo html_escape($product->weight); ?></span><?php endif; ?>
      <?php if ( ! empty($product->flavor)): ?><span><?php echo html_escape($product->flavor); ?></span><?php endif; ?>
    </div>
    <p class="product-desc"><?php echo html_escape(store_product_short_description($product, 80)); ?></p>
    <div class="product-foot">
      <span class="product-price"><?php echo store_price($product->unit_price); ?></span>
      <div class="product-actions">
        <?php if ($available): ?>
          <button type="button" class="btn-brand btn-sm" data-add-to-cart data-product-id="<?php echo (int) $product->id; ?>" aria-label="Agregar <?php echo html_escape($product->name); ?> al carrito">
            <i class="bi bi-cart-plus" aria-hidden="true"></i>
          </button>
        <?php else: ?>
          <button type="button" class="btn-ghost btn-sm" disabled>Agotado</button>
        <?php endif; ?>
        <?php if ( ! empty($wa)): ?>
          <a class="btn-ghost btn-sm" href="<?php echo html_escape($wa); ?>" target="_blank" rel="noopener" aria-label="Pedir por WhatsApp">
            <i class="bi bi-whatsapp" aria-hidden="true"></i>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</article>
